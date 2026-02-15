<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFinanceRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! auth()->user()->is_active) {
            auth()->logout();

            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan. Silakan hubungi admin.');
        }

        if (auth()->user()->role !== 'finance') {
            abort(403, 'Unauthorized: Finance only');
        }

        return $next($request);
    }
}
