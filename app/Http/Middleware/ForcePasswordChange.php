<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->password_reset_at) {
            $routeName = $request->route()->getName();

            // Allow access to profile and logout routes to prevent infinite loop
            $allowedRoutes = [
                'pegawai.profile.index',
                'pegawai.profile.password',
                'atasan.profile.index',
                'atasan.profile.password',
                'finance.profile.index',
                'finance.profile.password',
                'logout',
            ];

            if (! in_array($routeName, $allowedRoutes)) {
                $role = Auth::user()->role;

                return redirect()->route($role.'.profile.index')
                    ->with('warning', 'Wajib Ubah Password: Password Anda baru saja direset oleh admin. Silakan ubah password sementara Anda untuk dapat mengakses fitur lainnya.');
            }
        }

        return $next($request);
    }
}
