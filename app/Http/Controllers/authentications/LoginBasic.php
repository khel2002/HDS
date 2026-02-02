<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LoginBasic extends Controller
{

    public function index()
    {

        if (Auth::check()) {
            return redirect('/')->with('info', 'You are already logged in.');
        }

        return view('content.authentications.auth-login-basic');
    }


    public function login(Request $request)
    {
        dd($request->all());
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');
        $remember = $request->has('remember');

        try {

            $user = User::where('email', $email)->first();

            if (!$user) {
                return back()->with('error', 'These credentials do not match our records.');
            }


            if (!Hash::check($password, $user->password)) {
                return back()->with('error', 'These credentials do not match our records.');
            }


            $response = Http::withToken(config('services.hrmis_api.token'))
                ->post(config('services.hrmis_api.url'), [
                    'email' => $email,
                ]);

            if ($response->successful()) {
                $apiData = $response->json();
                $hrmisData = $apiData['data'] ?? null;

                if (!empty($hrmisData)) {
                    $employeeStatus = strtolower($hrmisData['status'] ?? '');

                    if ($employeeStatus !== 'active') {
                        return back()->with('error', 'Your account is not active in HRMIS. Please contact HR department.');
                    }


                    $user->update([
                        'first_name' => $hrmisData['first_name'] ?? $user->first_name,
                        'middle_name' => $hrmisData['middle_name'] ?? $user->middle_name,
                        'last_name' => $hrmisData['last_name'] ?? $user->last_name,
                        'STATUS' => 'active',
                        'last_login_at' => now(),
                    ]);
                } else {

                    return back()->with('error', 'Your account is not registered in HRMIS. Please contact HR department.');
                }
            } else {

                Log::warning("HRMIS API unavailable during login for: {$email}");


                $user->update(['last_login_at' => now()]);
            }


            Auth::login($user, $remember);


            $request->session()->regenerate();

            Log::info("User logged in successfully: {$email}");

            return redirect()->intended('/')->with('success', 'Login successful!');

        } catch (\Exception $e) {
            Log::error('Login Error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred during login. Please try again.');
        }
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'You have been logged out successfully.');
    }
}
