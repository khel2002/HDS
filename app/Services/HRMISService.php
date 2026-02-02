<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HRMISService
{

    public function syncEmployee(array $hrmisData, int $userId): bool
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($userId);



            $user->update([
                'first_name' => $hrmisData['FirstName'] ?? $hrmisData['first_name'] ?? $hrmisData['firstname'] ?? $user->first_name,
                'middle_name' => $hrmisData['MiddleName'] ?? $hrmisData['middle_name'] ?? $hrmisData['middlename'] ?? $user->middle_name,
                'last_name' => $hrmisData['LastName'] ?? $hrmisData['last_name'] ?? $hrmisData['lastname'] ?? $user->last_name,
                'STATUS' => strtolower($hrmisData['status'] ?? $hrmisData['Status'] ?? '') === 'active' ? 'active' : 'inactive',
            ]);


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


    private function shouldCreateGuestDetails(User $user): bool
    {


        return $user->role_id == 4;
    }


    private function syncGuestDetails(array $hrmisData, int $userId): void
    {
        $guestDetails = [
            'user_id' => $userId,
            'first_name' => $hrmisData['FirstName'] ?? $hrmisData['first_name'] ?? $hrmisData['firstname'] ?? '',
            'middle_name' => $hrmisData['MiddleName'] ?? $hrmisData['middle_name'] ?? $hrmisData['middlename'] ?? null,
            'last_name' => $hrmisData['LastName'] ?? $hrmisData['last_name'] ?? $hrmisData['lastname'] ?? '',
            'contact_number' => $hrmisData['contact_number'] ?? $hrmisData['ContactNumber'] ?? $hrmisData['phone'] ?? $hrmisData['Phone'] ?? null,
            'dob' => $hrmisData['date_of_birth'] ?? $hrmisData['DateOfBirth'] ?? $hrmisData['dob'] ?? $hrmisData['DOB'] ?? null,
        ];

        $existing = DB::table('guest_details')
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            DB::table('guest_details')
                ->where('user_id', $userId)
                ->update($guestDetails);
        } else {
            DB::table('guest_details')->insert($guestDetails);
        }
    }


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
                $email = $employeeData['email'] ?? $employeeData['Email'] ?? null;

                if (!$email) {
                    $results['failed']++;
                    continue;
                }


                $user = User::where('email', $email)->first();

                if ($user) {
                    $synced = $this->syncEmployee($employeeData, $user->user_id);
                    if ($synced) {
                        $results['success']++;
                    } else {
                        $results['failed']++;
                    }
                } else {

                    $results['failed']++;
                }
            }

            return $results;

        } catch (\Exception $e) {
            Log::error("Failed to sync all employees: " . $e->getMessage());
            throw $e;
        }
    }


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

            $isActive = strtolower($hrmisData['status'] ?? $hrmisData['Status'] ?? '') === 'active';

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
