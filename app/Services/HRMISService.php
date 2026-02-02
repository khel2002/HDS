<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HRMISService
{
    /**
     * Sync employee data from HRMIS to local database
     * Only syncs: first_name, middle_name, last_name, status
     *
     * @param array $hrmisData Employee data from HRMIS API
     * @param int $userId The user ID to update
     * @return bool
     */
    public function syncEmployee(array $hrmisData, int $userId): bool
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($userId);

            // Update user with HRMIS data (only name fields and status)
            $user->update([
                'first_name' => $hrmisData['first_name'] ?? $user->first_name,
                'middle_name' => $hrmisData['middle_name'] ?? $user->middle_name,
                'last_name' => $hrmisData['last_name'] ?? $user->last_name,
                'STATUS' => strtolower($hrmisData['status'] ?? '') === 'active' ? 'active' : 'inactive',
            ]);

            // Sync guest details if user is a guest
            if ($this->shouldCreateGuestDetails($user)) {
                $this->syncGuestDetails($hrmisData, $userId);
            }

            DB::commit();

            Log::info("Successfully synced HRMIS data for user {$userId}");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to sync HRMIS data for user {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if guest details should be created
     * Only create guest details for users with guest role (role_id = 4)
     *
     * @param User $user
     * @return bool
     */
    private function shouldCreateGuestDetails(User $user): bool
    {
        // Only create guest details for users with guest role (role_id = 4)
        return $user->role_id == 4;
    }

    /**
     * Sync guest details from HRMIS data
     *
     * @param array $hrmisData
     * @param int $userId
     * @return void
     */
    private function syncGuestDetails(array $hrmisData, int $userId): void
    {
        $guestDetails = [
            'user_id' => $userId,
            'first_name' => $hrmisData['first_name'] ?? '',
            'middle_name' => $hrmisData['middle_name'] ?? null,
            'last_name' => $hrmisData['last_name'] ?? '',
            'contact_number' => $hrmisData['contact_number'] ?? $hrmisData['phone'] ?? null,
            'dob' => $hrmisData['date_of_birth'] ?? $hrmisData['dob'] ?? null,
        ];

        // Check if guest details already exist
        $existing = DB::table('guest_details')
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            // Update existing guest details
            DB::table('guest_details')
                ->where('user_id', $userId)
                ->update($guestDetails);
        } else {
            // Create new guest details
            DB::table('guest_details')->insert($guestDetails);
        }
    }

    /**
     * Fetch all employees from HRMIS and sync them
     *
     * @return array Results of sync operation
     */
    public function syncAllEmployees(): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withToken(config('services.hrmis_api.token'))
                ->get(config('services.hrmis_api.url_all'));

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch employees from HRMIS");
            }

            $apiData = $response->json();
            $employees = $apiData['data'] ?? [];

            $results = [
                'success' => 0,
                'failed' => 0,
                'total' => count($employees),
            ];

            foreach ($employees as $employeeData) {
                $email = $employeeData['email'] ?? null;

                if (!$email) {
                    $results['failed']++;
                    continue;
                }

                // Find user by email
                $user = User::where('email', $email)->first();

                if ($user) {
                    $synced = $this->syncEmployee($employeeData, $user->user_id);
                    if ($synced) {
                        $results['success']++;
                    } else {
                        $results['failed']++;
                    }
                } else {
                    // User doesn't exist in local database
                    $results['failed']++;
                }
            }

            return $results;

        } catch (\Exception $e) {
            Log::error("Failed to sync all employees: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify if an employee is active in HRMIS
     *
     * @param string $email
     * @return array ['exists' => bool, 'active' => bool, 'data' => array|null]
     */
    public function verifyEmployeeStatus(string $email): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withToken(config('services.hrmis_api.token'))
                ->post(config('services.hrmis_api.url'), [
                    'email' => $email,
                ]);

            if (!$response->successful()) {
                return [
                    'exists' => false,
                    'active' => false,
                    'data' => null,
                ];
            }

            $apiData = $response->json();
            $hrmisData = $apiData['data'] ?? null;

            if (empty($hrmisData)) {
                return [
                    'exists' => false,
                    'active' => false,
                    'data' => null,
                ];
            }

            $isActive = strtolower($hrmisData['status'] ?? '') === 'active';

            return [
                'exists' => true,
                'active' => $isActive,
                'data' => $hrmisData,
            ];

        } catch (\Exception $e) {
            Log::error("Failed to verify employee status for {$email}: " . $e->getMessage());
            return [
                'exists' => false,
                'active' => false,
                'data' => null,
            ];
        }
    }
}
