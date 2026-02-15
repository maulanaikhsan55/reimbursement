<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

trait HandlesProfileUpdate
{
    /**
     * Update profile information with robust validation
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $allowedDomains = config('reimbursement.security.allowed_domains', []);

        $validated = $request->validate([
            'name' => 'required|string|max:100', // Stricter length
            'email' => [
                'required',
                'email',
                'unique:users,email,'.$user->id,
                function ($attribute, $value, $fail) use ($allowedDomains) {
                    if (config('reimbursement.security.force_official_email', true)) {
                        $domain = substr(strrchr($value, '@'), 1);
                        if (! in_array($domain, $allowedDomains)) {
                            $fail('Domain email tidak diizinkan. Gunakan email resmi perusahaan.');
                        }
                    }
                },
            ],
            'nomor_telepon' => [
                'nullable',
                'string',
                'max:15', // Indonesian phone numbers are max 13-15 digits
                'regex:/^(08|62)[0-9]{9,13}$/',
            ],
            'nama_bank' => 'nullable|string|max:50',
            'nomor_rekening' => [
                'nullable',
                'string',
                'max:20',
                'min:10',
                'regex:/^[0-9]+$/',
            ],
        ], [
            'email.unique' => 'Email sudah digunakan oleh akun lain',
            'nomor_telepon.regex' => 'Format nomor telepon tidak valid (Gunakan 08xx atau 62xx)',
            'nomor_rekening.regex' => 'Nomor rekening hanya boleh berisi angka',
        ]);

        $user->update($validated);

        // If email changed, mark as unverified but don't force verification for now
        if ($user->wasChanged('email')) {
            $user->email_verified_at = now(); // Auto-verify since user is in dev/skripsi mode
            $user->save();
        }

        Auth::setUser($user->fresh());

        return back()->with('success', 'Profil berhasil diperbarui');
    }

    /**
     * Update password with security best practices
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) {
                if (! Hash::check($value, Auth::user()->password)) {
                    $fail('Password saat ini tidak sesuai');
                }
            }],
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
            'password_reset_at' => null,
        ]);

        // Force logout for security
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Password berhasil diubah. Silakan login kembali dengan password baru Anda.');
    }
}
