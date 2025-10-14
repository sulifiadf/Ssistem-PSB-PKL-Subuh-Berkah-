<?php

namespace App\Http\Controllers\auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;


class LupaPasswordController extends Controller
{
    // Direct password reset (bypass email verification)
    public function directResetForm(){
        return view('auth.reset-password');
    }

    public function directResetPassword(Request $request){
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed'
            ],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email tidak ditemukan dalam sistem.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        try {
            // Find user by email
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                return back()->withErrors(['email' => 'Email tidak ditemukan dalam sistem.']);
            }

            // Update password
            $user->forceFill([
                'password' => Hash::make($request->password),
            ])->setRememberToken(Str::random(60));

            $user->save();

            // Fire the password reset event
            event(new PasswordReset($user));

            // Redirect to login with success message
            return redirect()->route('user.login')->with([
                'status' => 'Password berhasil direset! Silakan login dengan password baru.',
                'success' => true
            ]);

        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Terjadi kesalahan saat mereset password. Silakan coba lagi.']);
        }
    }
}
