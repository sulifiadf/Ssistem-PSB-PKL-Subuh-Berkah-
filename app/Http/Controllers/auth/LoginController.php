<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function authenticateUser(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->status !== 'approve') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun Anda belum disetujui admin.',
                ]);
            }

            $request->session()->regenerate();
            return redirect()->intended('/user/dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }

    public function authenticateAdmin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // WAJIB: Cek kredensial dengan database
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // WAJIB: Cek role admin
            if ($user->role !== 'admin') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun ini bukan akun admin.',
                ]);
            }

            // WAJIB: Generate session
            $request->session()->regenerate();
            
            return redirect()->intended('/admin/dashboard');
        }

        // Jika kredensial salah
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }

    public function logout(Request $request){
        $redirect = 'login';

        if (auth()->check()){
            $user = auth()->user();

            if($user->role== 'admin'){
                $redirect = 'admin.login';
            }else{
                $redirect = 'user.login';
            }
        }

        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();


        return redirect()->route($redirect);
    }
}
