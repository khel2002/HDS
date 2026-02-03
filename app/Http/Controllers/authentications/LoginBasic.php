<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');
        $remember = $request->has('remember');

        try {
            // Find user by email
            $user = User::where('email', $email)->first();

            if (!$user) {
                Log::warning("Login attempt - User not found: {$email}");
                return back()->withInput($request->only('email'))
                    ->with('error', 'These credentials do not match our records.');
            }

            Log::info("User found: {$email}");

            // Verify password
            if (!Hash::check($password, $user->password)) {
                Log::warning("Login attempt - Invalid password for: {$email}");
                return back()->withInput($request->only('email'))
                    ->with('error', 'These credentials do not match our records.');
            }

            Log::info("Password verified for: {$email}");

            // Check if user account is active
            if (!empty($user->STATUS) && strtolower($user->STATUS) !== 'active') {
                Log::warning("Login attempt - Inactive account: {$email}, Status: {$user->STATUS}");
                return back()->withInput($request->only('email'))
                    ->with('error', 'Your account is not active. Please contact the administrator.');
            }

            Log::info("Status check passed for: {$email}");

            // Update last login timestamp
            $user->last_login_at = now();
            $user->save();

            Log::info("Last login updated for: {$email}");

            // Log the user in
            Auth::login($user, $remember);

            Log::info("Auth::login called for: {$email}");

            // Regenerate session to prevent session fixation
            $request->session()->regenerate();

            Log::info("User logged in successfully: {$email}");

            return redirect()->intended('/')->with('success', 'Login successful!');

        } catch (\Exception $e) {
            Log::error('Login Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withInput($request->only('email'))
                ->with('error', 'An error occurred during login: ' . $e->getMessage());
        }
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/auth/login')->with('success', 'You have been logged out successfully.');
    }
}
