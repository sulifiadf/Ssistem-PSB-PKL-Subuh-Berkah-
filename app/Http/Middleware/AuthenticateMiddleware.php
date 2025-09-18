<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        if (!Auth::check()) {
            // Belum login → arahkan ke login sesuai role
            return $role === 'admin'
                ? redirect()->route('admin.login')
                : redirect()->route('user.login');
        }

        $user = Auth::user();

        // Validasi role
        if ($role && $user->role !== $role) {
            Auth::logout();
            return redirect()->route($role . '.login')
                ->withErrors(['email' => 'Anda tidak punya akses ke halaman ini.']);
        }

        // Validasi status approval khusus user
        if ($role === 'user' && $user->status !== 'approve') {
            Auth::logout();
            return redirect()->route('user.login')
                ->withErrors(['email' => 'Akun Anda belum disetujui admin.']);
        }

        return $next($request);
    }
}
