<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupOverdueGuests extends Command
{
    protected $signature   = 'guests:cleanup-overdue
                                {--dry-run : Preview without deleting}
                                {--grace=1 : Grace period in days after overdue (default 1)}';

    protected $description = 'Delete temporary guest accounts that are overdue for check-out or have missed their check-in date.';

    public function handle(): int
    {
        $dryRun      = $this->option('dry-run');
        $graceDays   = (int) $this->option('grace');
        $tz          = config('app.timezone', 'Asia/Manila');
        $today       = Carbon::today($tz);
        $cutoff      = $today->copy()->subDays($graceDays)->toDateString();

        $this->info('=== Overdue Guest Cleanup ===');
        $this->info("Reference date : {$today->toDateString()} | Grace: {$graceDays} day(s) | Cutoff: {$cutoff}");
        $dryRun && $this->warn('[DRY-RUN] No changes will be written.');

        $deleted = 0;

        // ── 1. Overdue check-OUT ───────────────────────────────────────────────
        // Guest has a registration (checked in) but never checked out,
        // and their reservation's check_out_date is before the cutoff.
        $overdueCheckouts = DB::table('users as u')
            ->join('reservations as r',   'r.user_id',          '=', 'u.user_id')
            ->join('registrations as reg','reg.reservation_id', '=', 'r.reservation_id')
            ->where('u.temporary_act', 1)
            ->whereNotNull('reg.check_in_at')          // did check in
            ->whereNull('reg.check_out_date')           // never checked out
            ->where('r.check_out_date', '<', $cutoff)  // past cutoff
            ->whereNotIn('r.reservation_status', ['cancelled', 'rejected'])
            ->select('u.user_id', 'u.email', 'u.first_name', 'u.last_name',
                     'r.reservation_id', 'r.check_out_date',
                     DB::raw('"overdue_checkout" as reason'))
            ->get();

        // ── 2. Missed check-IN ────────────────────────────────────────────────
        // Reservation is pending/approved but the check_in_date has passed cutoff
        // and the guest never checked in at all.
        $missedCheckins = DB::table('users as u')
            ->join('reservations as r', 'r.user_id', '=', 'u.user_id')
            ->where('u.temporary_act', 1)
            ->whereIn('r.reservation_status', ['pending', 'approved'])
            ->where('r.check_in_date', '<', $cutoff)   // past cutoff
            ->whereNotExists(function ($q) {            // no registration = never checked in
                $q->select(DB::raw(1))
                  ->from('registrations as reg')
                  ->whereColumn('reg.reservation_id', 'r.reservation_id')
                  ->whereNotNull('reg.check_in_at');
            })
            ->select('u.user_id', 'u.email', 'u.first_name', 'u.last_name',
                     'r.reservation_id', 'r.check_in_date as check_out_date',
                     DB::raw('"missed_checkin" as reason'))
            ->get();

        $allTargets = $overdueCheckouts->merge($missedCheckins)->unique('user_id');

        if ($allTargets->isEmpty()) {
            $this->info('Nothing to clean up. All good!');
            return Command::SUCCESS;
        }

        $this->table(
            ['User ID', 'Name', 'Email', 'Reservation', 'Date', 'Reason'],
            $allTargets->map(fn($u) => [
                $u->user_id,
                trim("{$u->first_name} {$u->last_name}"),
                $u->email,
                $u->reservation_id,
                $u->check_out_date,
                $u->reason,
            ])
        );

        if ($dryRun) {
            $this->warn("[DRY-RUN] Would delete {$allTargets->count()} user(s). Run without --dry-run to apply.");
            return Command::SUCCESS;
        }

       foreach ($allTargets as $target) {
    DB::beginTransaction();
    try {
        $userId = $target->user_id;

        // 1. Get all reservation IDs for this user
        $reservationIds = DB::table('reservations')
            ->where('user_id', $userId)
            ->pluck('reservation_id')
            ->toArray();

        // 2. Get all registration IDs tied to those reservations
        $registrationIds = DB::table('registrations')
            ->whereIn('reservation_id', $reservationIds)
            ->pluck('registration_id')
            ->toArray();

        // 3. Cancel active reservations (audit trail)
        DB::table('reservations')
            ->where('user_id', $userId)
            ->whereIn('reservation_status', ['pending', 'approved'])
            ->update([
                'reservation_status' => 'cancelled',
                'purpose'            => DB::raw("CONCAT(IFNULL(purpose,''), ' [Auto-cancelled: overdue]')"),
            ]);

        // 4. Delete service_requests first (FK → registrations)
        if (!empty($registrationIds)) {
            DB::table('service_requests')
                ->whereIn('registration_id', $registrationIds)
                ->delete();
        }

        // 5. Delete registrations (guest_details_id is NOT NULL — cannot be nulled)
        //    Must happen BEFORE guest_details is deleted
        if (!empty($registrationIds)) {
            DB::table('registrations')
                ->whereIn('registration_id', $registrationIds)
                ->delete();
        }

        // 6. Null out reservations.guest_details_id (FK → guest_details)
        //    Must happen BEFORE guest_details is deleted
        if (!empty($reservationIds)) {
            DB::table('reservations')
                ->whereIn('reservation_id', $reservationIds)
                ->update(['guest_details_id' => null]);
        }

        // 7. Now safe to delete guest_details
        DB::table('guest_details')->where('user_id', $userId)->delete();

        // 8. Delete reservations (FK → users)
        if (!empty($reservationIds)) {
            DB::table('reservations')
                ->whereIn('reservation_id', $reservationIds)
                ->delete();
        }

        // 9. Finally delete the user
        DB::table('users')->where('user_id', $userId)->delete();

        DB::commit();

        $deleted++;
        $this->line("  ✓ Deleted user #{$userId} ({$target->email}) — {$target->reason}");

        Log::info('CleanupOverdueGuests: deleted user', [
            'user_id'        => $userId,
            'email'          => $target->email,
            'reservation_id' => $target->reservation_id,
            'reason'         => $target->reason,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        $this->error("  ✗ Failed user #{$target->user_id}: {$e->getMessage()}");
        Log::error('CleanupOverdueGuests: failed', [
            'user_id' => $target->user_id,
            'error'   => $e->getMessage(),
        ]);
    }
}

        $this->info("Done. {$deleted}/{$allTargets->count()} account(s) removed.");
        return Command::SUCCESS;
    }
}