<?php

namespace App\Http\Controllers\Atasan;

use App\Http\Controllers\Controller;
use App\Traits\HandlesProfileUpdate;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    use HandlesProfileUpdate;

    public function index()
    {
        $user = Auth::user()->load('departemen');

        return view('dashboard.atasan.profile', compact('user'));
    }
}
