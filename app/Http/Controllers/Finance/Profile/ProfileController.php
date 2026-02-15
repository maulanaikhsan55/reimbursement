<?php

namespace App\Http\Controllers\Finance\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    use \App\Traits\HandlesProfileUpdate;

    public function index()
    {
        $user = Auth::user()->load('departemen');

        return view('dashboard.finance.profile', compact('user'));
    }
}
