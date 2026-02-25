<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

trait HandlesProfileUpdate
{
    /**
     * Update profile information with robust validation
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $allowedDomains = array_map('strtolower', config('reimbursement.security.allowed_domains', []));

        $validated = $request->validate([
            'name' => 'required|string|max:100', // Stricter length
            'email' => [
                'required',
                'email',
                // Email on profile is read-only; block tampered requests from changing identity.
                Rule::in([$user->email]),
                function ($attribute, $value, $fail) use ($allowedDomains) {
                    if (config('reimbursement.security.force_official_email', true)) {
                        $domain = strtolower((string) substr(strrchr($value, '@'), 1));
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
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:min_width=128,min_height=128',
            'hapus_foto_profil' => 'nullable|boolean',
        ], [
            'email.in' => 'Email tidak dapat diubah. Hubungi administrator jika diperlukan.',
            'nomor_telepon.regex' => 'Format nomor telepon tidak valid (Gunakan 08xx atau 62xx)',
            'nomor_rekening.regex' => 'Nomor rekening hanya boleh berisi angka',
            'foto_profil.max' => 'Ukuran foto profil maksimal 2MB.',
            'foto_profil.mimes' => 'Format foto profil harus JPG, JPEG, PNG, atau WEBP.',
            'foto_profil.dimensions' => 'Ukuran foto terlalu kecil. Minimal 128x128 px.',
        ]);

        $payload = collect($validated)
            ->except(['email', 'foto_profil', 'hapus_foto_profil'])
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->all();

        $oldPhotoPath = $user->foto_profil_path;
        $shouldRemovePhoto = $request->boolean('hapus_foto_profil');

        if ($request->hasFile('foto_profil')) {
            $payload['foto_profil_path'] = $this->storeOptimizedProfilePhoto($request->file('foto_profil'));
        } elseif ($shouldRemovePhoto && $oldPhotoPath) {
            $payload['foto_profil_path'] = null;
        }

        $user->update($payload);

        if (array_key_exists('foto_profil_path', $payload) && $oldPhotoPath && $oldPhotoPath !== $payload['foto_profil_path']) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        Auth::setUser($user->fresh());

        return back()->with('success', 'Profil berhasil diperbarui');
    }

    /**
     * Store profile photo with server-side resize/compression when possible.
     */
    private function storeOptimizedProfilePhoto(UploadedFile $file): string
    {
        try {
            $optimizedBinary = $this->buildOptimizedProfilePhotoBinary($file);
            if ($optimizedBinary !== null) {
                $path = 'profile-photos/'.Str::uuid().'.jpg';
                Storage::disk('public')->put($path, $optimizedBinary);

                return $path;
            }
        } catch (\Throwable $e) {
            // Graceful fallback: keep upload flow working even if optimization fails.
        }

        return $file->store('profile-photos', 'public');
    }

    /**
     * Resize image to max 512px and re-encode JPEG to keep avatars lightweight.
     */
    private function buildOptimizedProfilePhotoBinary(UploadedFile $file): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $realPath = $file->getRealPath();
        if (! $realPath || ! is_file($realPath)) {
            return null;
        }

        $raw = @file_get_contents($realPath);
        if ($raw === false) {
            return null;
        }

        $source = @imagecreatefromstring($raw);
        if (! $source) {
            return null;
        }

        $srcW = imagesx($source);
        $srcH = imagesy($source);
        if ($srcW < 1 || $srcH < 1) {
            imagedestroy($source);

            return null;
        }

        $maxSize = 512;
        $ratio = min($maxSize / $srcW, $maxSize / $srcH, 1);
        $dstW = max(1, (int) round($srcW * $ratio));
        $dstH = max(1, (int) round($srcH * $ratio));

        $canvas = imagecreatetruecolor($dstW, $dstH);
        if (! $canvas) {
            imagedestroy($source);

            return null;
        }

        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

        ob_start();
        imagejpeg($canvas, null, 88);
        $binary = ob_get_clean();

        imagedestroy($canvas);
        imagedestroy($source);

        return is_string($binary) ? $binary : null;
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
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'password_confirmation' => 'required',
        ], [
            'password.min' => 'Password minimal 8 karakter.',
            'password.letters' => 'Password harus mengandung huruf.',
            'password.numbers' => 'Password harus mengandung angka.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
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
