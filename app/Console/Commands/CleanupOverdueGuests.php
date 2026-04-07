<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupOverdueGuests extends Command
{
    protected $signature = 'guests:cleanup-overdue
                            {--dry-run : Preview without deleting}
                            {--grace=1 : Grace period in days}
                            {--email= : Manually delete specific email}';

    protected $description = 'Delete overdue or specific temporary guest accounts.';

    public function handle(): int
    {
        $dryRun    = $this->option('dry-run');
        $graceDays = (int) $this->option('grace');
        $email     = $this->option('email');

        $tz     = config('app.timezone', 'Asia/Manila');
        $today  = Carbon::today($tz);
        $cutoff = $today->copy()->subDays($graceDays)->toDateString();

        $this->info('=== Guest Cleanup ===');
        $this->info("Date: {$today->toDateString()} | Grace: {$graceDays} | Cutoff: {$cutoff}");

        if ($dryRun) {
            $this->warn('[DRY-RUN MODE]');
        }

        $deleted = 0;

        // ─────────────────────────────────────────────
        // 🔥 MANUAL EMAIL OVERRIDE
        // ─────────────────────────────────────────────
        if ($email) {
            $this->warn("Manual delete for: {$email}");

            $user = DB::table('users')->where('email', $email)->first();

            if (!$user) {
                $this->error('User not found.');
                return Command::FAILURE;
            }

            $targets = collect([(object)[
                'user_id' => $user->user_id,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'reservation_id' => null,
                'check_out_date' => null,
                'reason' => 'manual_delete'
            ]]);

        } else {

            // ── 1. Overdue checkout ──
            $overdueCheckouts = DB::table('users as u')
                ->join('reservations as r', 'r.user_id', '=', 'u.user_id')
                ->join('registrations as reg', 'reg.reservation_id', '=', 'r.reservation_id')
                ->where('u.temporary_act', 1)
                ->whereNotNull('reg.check_in_at')
                ->whereNull('reg.check_out_date')
                ->where('r.check_out_date', '<', $cutoff)
                ->whereNotIn('r.reservation_status', ['cancelled', 'rejected'])
                ->select(
                    'u.user_id',
                    'u.email',
                    'u.first_name',
                    'u.last_name',
                    'r.reservation_id',
                    'r.check_out_date',
                    DB::raw('"overdue_checkout" as reason')
                )
                ->get();

            // ── 2. Missed check-in ──
            $missedCheckins = DB::table('users as u')
                ->join('reservations as r', 'r.user_id', '=', 'u.user_id')
                ->where('u.temporary_act', 1)
                ->whereIn('r.reservation_status', ['pending', 'approved'])
                ->where('r.check_in_date', '<', $cutoff)
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                      ->from('registrations as reg')
                      ->whereColumn('reg.reservation_id', 'r.reservation_id')
                      ->whereNotNull('reg.check_in_at');
                })
                ->select(
                    'u.user_id',
                    'u.email',
                    'u.first_name',
                    'u.last_name',
                    'r.reservation_id',
                    'r.check_in_date as check_out_date',
                    DB::raw('"missed_checkin" as reason')
                )
                ->get();

            $targets = $overdueCheckouts->merge($missedCheckins)->unique('user_id');
        }

        if ($targets->isEmpty()) {
            $this->info('Nothing to delete.');
            return Command::SUCCESS;
        }

        // Display preview
        $this->table(
            ['User ID', 'Name', 'Email', 'Reservation', 'Date', 'Reason'],
            $targets->map(fn($u) => [
                $u->user_id,
                trim("{$u->first_name} {$u->last_name}"),
                $u->email,
                $u->reservation_id,
                $u->check_out_date,
                $u->reason,
            ])
        );

        if ($dryRun) {
            $this->warn("Would delete {$targets->count()} user(s).");
            return Command::SUCCESS;
        }

        // ─────────────────────────────────────────────
        // 🔥 DELETION PROCESS
        // ─────────────────────────────────────────────
        foreach ($targets as $target) {

            DB::beginTransaction();

            try {
                $userId = $target->user_id;

                $reservationIds = DB::table('reservations')
                    ->where('user_id', $userId)
                    ->pluck('reservation_id')
                    ->toArray();

                $registrationIds = DB::table('registrations')
                    ->whereIn('reservation_id', $reservationIds)
                    ->pluck('registration_id')
                    ->toArray();

                // Cancel active reservations
                DB::table('reservations')
                    ->where('user_id', $userId)
                    ->whereIn('reservation_status', ['pending', 'approved'])
                    ->update([
                        'reservation_status' => 'cancelled',
                        'purpose' => DB::raw("CONCAT(IFNULL(purpose,''), ' [Auto-deleted]')")
                    ]);

                // Delete service_requests
                if (!empty($registrationIds)) {
                    DB::table('service_requests')
                        ->whereIn('registration_id', $registrationIds)
                        ->delete();
                }

                // Delete registrations
                if (!empty($registrationIds)) {
                    DB::table('registrations')
                        ->whereIn('registration_id', $registrationIds)
                        ->delete();
                }

                // Null FK
                if (!empty($reservationIds)) {
                    DB::table('reservations')
                        ->whereIn('reservation_id', $reservationIds)
                        ->update(['guest_details_id' => null]);
                }

                // Delete guest details
                DB::table('guest_details')
                    ->where('user_id', $userId)
                    ->delete();

                // Delete reservations
                if (!empty($reservationIds)) {
                    DB::table('reservations')
                        ->whereIn('reservation_id', $reservationIds)
                        ->delete();
                }

                // Delete user
                DB::table('users')
                    ->where('user_id', $userId)
                    ->delete();

                DB::commit();

                $deleted++;

                $this->line("✓ Deleted {$target->email}");

                Log::info('Guest deleted', [
                    'user_id' => $userId,
                    'email' => $target->email,
                    'reason' => $target->reason,
                ]);

            } catch (\Exception $e) {

                DB::rollBack();

                $this->error("✗ Failed {$target->email}: " . $e->getMessage());

                Log::error('Deletion failed', [
                    'user_id' => $target->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Done. {$deleted}/{$targets->count()} deleted.");

        return Command::SUCCESS;
    }
}