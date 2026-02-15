<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    /**
     * Rate limit key for failed login attempts
     */
    protected string $throttleKey = 'login.attempts';

    /**
     * Maximum login attempts before lockout
     */
    protected int $maxAttempts = 5;

    /**
     * Lockout time in seconds (1 hour)
     */
    protected int $decaySeconds = 3600;

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validate input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Build throttle key based on email and IP
        $throttleKey = $this->throttleKey.':'.strtolower($request->email).'|'.$request->ip();

        // Check if too many attempts
        if (RateLimiter::tooManyAttempts($throttleKey, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            // Log blocked attempt
            $this->logFailedAttempt($request, 'rate_limited', 'Too many login attempts');

            return back()->withErrors([
                'email' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam '.
                          ceil($seconds / 60).' menit.',
            ])->onlyInput('email');
        }

        // Attempt authentication
        if (Auth::attempt($credentials)) {
            // Regenerate session to prevent session fixation
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if account is active
            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $this->logFailedAttempt($request, 'inactive_account', 'Account inactive');

                return back()->withErrors([
                    'email' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
                ])->onlyInput('email');
            }

            // Clear rate limiter on successful login
            RateLimiter::clear($throttleKey);

            // Log successful login
            Log::channel('auth')->info('Login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $this->redirectByRole($user);
        }

        // Record failed attempt
        RateLimiter::hit($throttleKey, $this->decaySeconds);

        // Log failed attempt
        $this->logFailedAttempt($request, 'invalid_credentials', 'Invalid email or password');

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    /**
     * Log failed login attempts
     */
    protected function logFailedAttempt(Request $request, string $reason, string $details): void
    {
        Log::channel('auth')->warning('Login failed', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'reason' => $reason,
            'details' => $details,
            'attempts_remaining' => max(0, $this->maxAttempts - RateLimiter::attempts($this->throttleKey.':'.strtolower($request->email).'|'.$request->ip())),
        ]);
    }

    public function logout(Request $request)
    {
        // Get user before logout for logging
        $user = Auth::user();
        $userId = $user?->id;
        $email = $user?->email;

        // Log logout
        if ($userId) {
            Log::channel('auth')->info('User logged out', [
                'user_id' => $userId,
                'email' => $email,
                'ip' => $request->ip(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }

    protected function redirectByRole($user)
    {
        if ($user->role === 'pegawai') {
            return redirect()->route('pegawai.dashboard');
        } elseif ($user->role === 'atasan') {
            return redirect()->route('atasan.dashboard');
        } elseif ($user->role === 'finance') {
            return redirect()->route('finance.dashboard');
        }

        return redirect('/');
    }
}
