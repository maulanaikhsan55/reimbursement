<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Traits\HandlesProfileUpdate;
use Illuminate\Support\Facades\Auth;

/**
 * Pegawai Profile Controller
 *
 * Manages employee profile operations:
 * - View profile information
 * - Update basic information (name, phone, bank account)
 * - Change password with security features
 */
class ProfileController extends Controller
{
    use HandlesProfileUpdate;

    /**
     * Display employee profile page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user()->load(['departemen', 'atasan']);

        return view('dashboard.pegawai.profile', compact('user'));
    }
}
