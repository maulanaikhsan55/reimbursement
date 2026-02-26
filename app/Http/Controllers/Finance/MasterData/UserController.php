<?php

namespace App\Http\Controllers\Finance\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('departemen', 'atasan', 'bawahan');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('departemen_id')) {
            $query->where('departemen_id', $request->departemen_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->where('is_active', true);
            } elseif ($request->status === 'nonaktif') {
                $query->where('is_active', false);
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(config('app.pagination.master_data'))->withQueryString();

        $departemens = \Cache::remember('departemens_list', 3600, function () {
            return Departemen::orderBy('nama_departemen')->get();
        });

        // Calculate stats once
        $stats = [
            'total' => User::count(),
            'aktif' => User::where('is_active', true)->count(),
            'nonaktif' => User::where('is_active', false)->count(),
        ];

        // Optimize organization tree: only load essential columns
        $organizationByDept = [];
        if (! $request->filled('search')) {
            $allUsers = User::select('id', 'name', 'role', 'departemen_id', 'jabatan')
                ->with('departemen:departemen_id,nama_departemen')
                ->get();

            foreach ($allUsers as $user) {
                $dept = $user->departemen->nama_departemen ?? 'Tanpa Departemen';
                if (! isset($organizationByDept[$dept])) {
                    $organizationByDept[$dept] = [
                        'atasan' => [],
                        'pegawai' => [],
                        'finance' => [],
                    ];
                }

                if ($user->role === 'atasan') {
                    $organizationByDept[$dept]['atasan'][] = $user;
                } elseif ($user->role === 'pegawai') {
                    $organizationByDept[$dept]['pegawai'][] = $user;
                } elseif ($user->role === 'finance') {
                    $organizationByDept[$dept]['finance'][] = $user;
                }
            }
        }

        return view('dashboard.finance.masterdata.users.index', compact('users', 'organizationByDept', 'departemens', 'stats'));
    }

    public function create()
    {
        $departemen = Departemen::orderBy('nama_departemen')->get();
        $supervisors = User::where('role', 'atasan')
            ->with('departemen')
            ->orderBy('name')
            ->get();

        return view('dashboard.finance.masterdata.users.create', compact('departemen', 'supervisors'));
    }

    public function checkEmailAvailability(Request $request)
    {
        $email = strtolower(trim((string) $request->query('email', '')));

        if ($email === '') {
            return response()->json([
                'available' => false,
                'message' => 'Email wajib diisi.',
            ], 422);
        }

        $validator = Validator::make(
            ['email' => $email],
            ['email' => 'required|email']
        );

        if ($validator->fails()) {
            return response()->json([
                'available' => false,
                'message' => $validator->errors()->first('email'),
            ], 422);
        }

        $allowedDomains = config('reimbursement.security.allowed_domains', []);
        if (config('reimbursement.security.force_official_email', true)) {
            $domain = (string) substr(strrchr($email, '@') ?: '', 1);
            if (! in_array($domain, $allowedDomains, true)) {
                return response()->json([
                    'available' => false,
                    'message' => 'Domain email tidak diizinkan. Gunakan email resmi perusahaan.',
                ]);
            }
        }

        $exists = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->exists();

        return response()->json([
            'available' => ! $exists,
            'message' => $exists ? 'Email sudah terdaftar.' : 'Email tersedia.',
        ]);
    }

    public function store(Request $request)
    {
        $allowedDomains = config('reimbursement.security.allowed_domains', []);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:users,email',
                function ($attribute, $value, $fail) use ($allowedDomains) {
                    if (config('reimbursement.security.force_official_email', true)) {
                        $domain = strtolower((string) substr(strrchr((string) $value, '@') ?: '', 1));
                        if (! in_array($domain, $allowedDomains, true)) {
                            $fail('Domain email tidak diizinkan. Gunakan email resmi perusahaan.');
                        }
                    }
                },
            ],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:pegawai,atasan,finance',
            'jabatan' => 'required|string|max:100',
            'departemen_id' => 'required|exists:departemen,departemen_id',
            'nomor_telepon' => [
                'nullable',
                'string',
                'max:15',
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
            'is_active' => 'required|boolean',
        ];

        if ($request->role === 'pegawai') {
            $hasAtasan = User::where('role', 'atasan')
                ->where('departemen_id', $request->departemen_id)
                ->exists();

            if ($hasAtasan) {
                $rules['atasan_id'] = 'required|exists:users,id';
            } else {
                $rules['atasan_id'] = 'nullable|exists:users,id';
            }
        } else {
            $rules['atasan_id'] = 'nullable';
            $request->merge(['atasan_id' => null]);
        }

        $validated = $request->validate($rules);

        if ($request->role === 'pegawai' && ! empty($validated['atasan_id'])) {
            $atasan = User::findOrFail($validated['atasan_id']);
            // Loose comparison (== or !=) because request input is string vs DB int
            if ($atasan->departemen_id != $validated['departemen_id']) {
                return back()->withErrors([
                    'atasan_id' => 'Pegawai dan Atasan harus dari departemen yang sama!',
                ])->withInput();
            }
        } else {
            $validated['atasan_id'] = null;
        }

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('finance.masterdata.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan');
    }

    public function edit(User $user)
    {
        $departemen = Departemen::orderBy('nama_departemen')->get();
        $supervisors = User::where('role', 'atasan')
            ->with('departemen')
            ->orderBy('name')
            ->get();

        return view('dashboard.finance.masterdata.users.edit', compact('user', 'departemen', 'supervisors'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'role' => 'required|in:pegawai,atasan,finance',
            'jabatan' => 'required|string|max:100',
            'departemen_id' => 'required|exists:departemen,departemen_id',
            'nomor_telepon' => [
                'nullable',
                'string',
                'max:15',
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
            'is_active' => 'required|boolean',
        ];

        if ($request->role === 'pegawai') {
            $hasAtasan = User::where('role', 'atasan')
                ->where('departemen_id', $request->departemen_id)
                ->exists();

            if ($hasAtasan) {
                $rules['atasan_id'] = 'required|exists:users,id';
            } else {
                $rules['atasan_id'] = 'nullable|exists:users,id';
            }
        } else {
            $rules['atasan_id'] = 'nullable';
            $request->merge(['atasan_id' => null]);
        }

        $validated = $request->validate($rules);

        // Explicitly prevent email change
        unset($validated['email']);

        if ($request->role === 'pegawai' && ! empty($validated['atasan_id'])) {
            $atasan = User::findOrFail($validated['atasan_id']);
            // Loose comparison (== or !=) because request input is string vs DB int
            if ($atasan->departemen_id != $validated['departemen_id']) {
                return back()->withErrors([
                    'atasan_id' => 'Pegawai dan Atasan harus dari departemen yang sama!',
                ])->withInput();
            }
        } else {
            $validated['atasan_id'] = null;
        }

        // Check if critical info changed to force logout
        $criticalChanged = $user->role !== $validated['role'] ||
                          $user->departemen_id != $validated['departemen_id'] ||
                          $user->is_active != $validated['is_active'];

        $user->update($validated);

        // If critical info changed, force logout from all sessions
        if ($criticalChanged) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        // Handle current user specifically
        if (auth()->id() === $user->id) {
            if ($criticalChanged) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('success', 'Profil Anda telah diperbarui. Silakan login kembali.');
            }
            auth()->setUser($user->fresh());
        }

        return redirect()->route('finance.masterdata.users.index')
            ->with('success', 'Pengguna berhasil diperbarui'.($criticalChanged ? '. User akan diminta menyesuaikan perubahan saat akses berikutnya.' : ''));
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->user()->id) {
            return redirect()->route('finance.masterdata.users.index')
                ->with('error', 'Tidak bisa menghapus akun Anda sendiri');
        }

        if ($user->bawahan()->exists()) {
            return redirect()->route('finance.masterdata.users.index')
                ->with('error', "User {$user->name} tidak dapat dihapus karena masih memiliki bawahan.");
        }

        if ($user->pengajuan()->exists() || $user->pengajuanDisetujui()->exists() || $user->pengajuanDisetujuiFinance()->exists()) {
            return redirect()->route('finance.masterdata.users.index')
                ->with('error', "User {$user->name} tidak dapat dihapus karena memiliki keterkaitan data transaksi (pengajuan/approval).");
        }

        $user->delete();

        return redirect()->route('finance.masterdata.users.index')
            ->with('success', 'Pengguna berhasil dihapus');
    }

    public function resetPassword(User $user)
    {
        $key = 'reset-password:'.auth()->id().':'.$user->id;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan. Silakan coba lagi dalam '.$seconds.' detik.',
            ], 429);
        }

        RateLimiter::hit($key, 3600);

        if ($user->id === auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa mereset password akun Anda sendiri',
            ], 403);
        }

        try {
            return DB::transaction(function () use ($user) {
                $tempPassword = $this->generateTemporaryPassword();

                $user->update([
                    'password' => Hash::make($tempPassword),
                    'password_reset_at' => now(),
                ]);

                // Force logout after password reset
                DB::table('sessions')->where('user_id', $user->id)->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Password direset berhasil',
                    'password' => $tempPassword,
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mereset password: '.$e->getMessage(),
            ], 500);
        }
    }

    private function generateTemporaryPassword()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < 12; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }
}
