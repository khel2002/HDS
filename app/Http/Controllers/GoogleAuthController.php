<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{

    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }


    public function handleGoogleCallback()
    {
        try {

            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $email = $googleUser->getEmail();


            if (!$this->isValidEmailDomain($email)) {
                return redirect('/auth/login')->with('error', 'Only @southernleytestateu.edu.ph email addresses are allowed.');
            }




            $response = Http::withoutVerifying()
                ->withToken(config('services.hrmis_api.token'))
                ->post(config('services.hrmis_api.url'), [
                    'email' => $email,
                ]);

            if (!$response->successful()) {
                Log::error('HRMIS API request failed for email: ' . $email);
                return redirect('/auth/login')->with('error', 'Failed to verify account with HRMIS. Please try again.');
            }

            $apiData = $response->json();
            $hrmisData = $apiData['data'] ?? null;


            Log::info('=== HRMIS API RESPONSE DEBUG ===');
            Log::info('Full HRMIS Data: ' . json_encode($hrmisData, JSON_PRETTY_PRINT));
            Log::info('=== END DEBUG ===');




            if (empty($hrmisData)) {
                return redirect('/auth/login')->with('error', 'Your email is not registered in HRMIS. Please contact HR department.');
            }



            $employeeStatus = $this->getEmployeeStatus($hrmisData);

            Log::info('Determined employee status: "' . $employeeStatus . '"');

            if (!$this->isActiveStatus($employeeStatus)) {
                Log::warning('User status check failed. Status: "' . $employeeStatus . '" for email: ' . $email);
                return redirect('/auth/login')->with('error', 'Your account is not active in HRMIS. Current status: "' . $employeeStatus . '". Please contact HR department.');
            }


            $user = $this->findOrCreateUser($email, $hrmisData);


            $user->update([
                'first_name' => $this->extractStringValue($hrmisData, 'FirstName')
                             ?? $this->extractStringValue($hrmisData, 'first_name')
                             ?? $this->extractStringValue($hrmisData, 'firstname')
                             ?? $user->first_name,
                'middle_name' => $this->extractStringValue($hrmisData, 'MiddleName')
                              ?? $this->extractStringValue($hrmisData, 'middle_name')
                              ?? $this->extractStringValue($hrmisData, 'middlename')
                              ?? $user->middle_name,
                'last_name' => $this->extractStringValue($hrmisData, 'LastName')
                            ?? $this->extractStringValue($hrmisData, 'last_name')
                            ?? $this->extractStringValue($hrmisData, 'lastname')
                            ?? $user->last_name,
                'STATUS' => 'active',
                'last_login_at' => now(),
            ]);


            Auth::login($user);

            Log::info('User logged in successfully via Google SSO: ' . $email);

            return redirect('/')->with('success', 'Login successful!');

        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::error('InvalidStateException: ' . $e->getMessage());
            return redirect('/auth/login')->with('error', 'Session expired. Please try logging in again.');

        } catch (\Exception $e) {
            Log::error('Google SSO Login Error: ' . $e->getMessage());
            Log::error('Error Trace: ' . $e->getTraceAsString());
            return redirect('/auth/login')->with('error', 'An error occurred during login. Please try again.');
        }
    }


    private function extractStringValue(array $data, string $field): ?string
    {
        if (!isset($data[$field])) {
            return null;
        }

        $value = $data[$field];


        if (is_string($value)) {
            return trim($value);
        }


        if (is_array($value)) {

            if (isset($value['name'])) {
                return trim((string)$value['name']);
            }
            if (isset($value['value'])) {
                return trim((string)$value['value']);
            }
            if (isset($value['text'])) {
                return trim((string)$value['text']);
            }
            if (isset($value[0])) {
                return trim((string)$value[0]);
            }

            return null;
        }


        if (is_object($value)) {
            if (isset($value->name)) {
                return trim((string)$value->name);
            }
            if (isset($value->value)) {
                return trim((string)$value->value);
            }
            return null;
        }


        return trim((string)$value);
    }


    private function getEmployeeStatus(array $hrmisData): string
    {

        $possibleStatusFields = [
            'status',
            'Status',
            'employee_status',
            'employeeStatus',
            'employment_status',
            'employmentStatus',
            'active',
            'is_active',
            'isActive',
            'emp_status',
        ];

        foreach ($possibleStatusFields as $field) {
            if (isset($hrmisData[$field])) {
                $statusValue = $hrmisData[$field];


                if (is_array($statusValue)) {

                    if (isset($statusValue['name'])) {
                        return strtolower(trim((string)$statusValue['name']));
                    }
                    if (isset($statusValue['value'])) {
                        return strtolower(trim((string)$statusValue['value']));
                    }
                    if (isset($statusValue['status'])) {
                        return strtolower(trim((string)$statusValue['status']));
                    }
                    if (isset($statusValue[0])) {
                        return strtolower(trim((string)$statusValue[0]));
                    }

                    Log::warning('Status is array but no recognizable keys: ' . json_encode($statusValue));
                    return '';
                }


                if (is_object($statusValue)) {
                    if (isset($statusValue->name)) {
                        return strtolower(trim((string)$statusValue->name));
                    }
                    if (isset($statusValue->value)) {
                        return strtolower(trim((string)$statusValue->value));
                    }
                    return '';
                }


                return strtolower(trim((string)$statusValue));
            }
        }


        Log::warning('No status field found in HRMIS data. Available fields: ' . implode(', ', array_keys($hrmisData)));
        return '';
    }


    private function isActiveStatus(string $status): bool
    {
        if (empty($status)) {
            return false;
        }


        $activeValues = [
            'active',
            '1',
            'true',
            'yes',
            'employed',
            'permanent',
            'regular',
            'working',
            'current',
            'hired',
        ];


        if (str_contains(strtolower($status), 'active')) {
            return true;
        }

        return in_array(strtolower($status), $activeValues);
    }


    private function isValidEmailDomain(string $email): bool
    {
        $allowedDomain = 'southernleytestateu.edu.ph';
        $emailDomain = substr(strrchr($email, "@"), 1);

        return strtolower($emailDomain) === strtolower($allowedDomain);
    }


    private function findOrCreateUser(string $email, array $hrmisData): User
    {
        $defaultPassword = Hash::make(explode('@', $email)[0]); // e.g. "rbesande"

        $user = User::where('email', $email)->first();

        if ($user) {
            // Update to a known password so manual login works too
            $user->update(['password' => $defaultPassword]);
            return $user;
        }

        Log::info('Creating new user account for: ' . $email);

        $roleId = $this->determineUserRole($hrmisData);

        $user = User::create([
            'email'         => $email,
            'password'      => $defaultPassword,
            'role_id'       => $roleId,
            'first_name'    => $this->extractStringValue($hrmisData, 'FirstName')
                            ?? $this->extractStringValue($hrmisData, 'first_name')
                            ?? $this->extractStringValue($hrmisData, 'firstname'),
            'middle_name'   => $this->extractStringValue($hrmisData, 'MiddleName')
                            ?? $this->extractStringValue($hrmisData, 'middle_name')
                            ?? $this->extractStringValue($hrmisData, 'middlename'),
            'last_name'     => $this->extractStringValue($hrmisData, 'LastName')
                            ?? $this->extractStringValue($hrmisData, 'last_name')
                            ?? $this->extractStringValue($hrmisData, 'lastname'),
            'STATUS'        => 'active',
            'temporary_act' => 0,
        ]);

        Log::info('New user created: ' . $email . ' with role_id: ' . $roleId);

        return $user;
    }

    private function determineUserRole(array $hrmisData): int
    {

        $position = strtolower($this->extractStringValue($hrmisData, 'position') ?? '');
        $department = strtolower($this->extractStringValue($hrmisData, 'department') ?? '');

        Log::info('Determining role - Position: "' . $position . '", Department: "' . $department . '"');

        if (str_contains($position, 'director') ||
            str_contains($position, 'dean') ||
            str_contains($position, 'head')) {
            return 1;
        }

        if (str_contains($position, 'manager') ||
            str_contains($position, 'supervisor') ||
            str_contains($position, 'coordinator')) {
            return 3;
        }

        if (str_contains($position, 'staff') ||
            str_contains($position, 'assistant') ||
            str_contains($position, 'clerk')) {
            return 2;
        }


        return 4;
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/auth/login');
    }
}
