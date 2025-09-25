<?php

namespace App\Http\Controllers\auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\registrasi_pertanyaan;
use Illuminate\Validation\ValidationException;
use App\Services\NotifikasiPersetujuan;

class RegisterController extends Controller
{
    public function createUser(){
        return view('user.register');
    }

    public function storeUser(Request $request, NotifikasiPersetujuan $wa){
        try {
            // Validasi input data
            $validated = $request->validate([
                'name'=> 'required|string|max:255',
                'email'=> 'required|string|email|max:50|unique:users,email',
                'password'=> 'required|confirmed|min:6',
                'alamat'=> 'required|string|max:255',
                'no_telp'=> 'required|string|max:15',
                'pertanyaan1'=> 'nullable|array',
                'pertanyaan1.*' => 'string|max:50',
                'pertanyaan1_custom' => 'nullable|string|max:50',
                'pertanyaan2' => 'nullable|string|max:50',
                'pertanyaan2_custom' => 'nullable|string|max:50',
                'mulai_jual' => 'required|date',
                'penjaga_stand' => 'required|string|max:50',
            ]);



            // Normalisasi jawaban
            $p1 = $validated['pertanyaan1'] ?? [];
            if (!empty($validated['pertanyaan1_custom'])) {
                $p1[] = $validated['pertanyaan1_custom'];
            }

            $p2 = $validated['pertanyaan2'] ?? null;
            if (empty($p2) && !empty($validated['pertanyaan2_custom'])) {
                $p2 = $validated['pertanyaan2_custom'];
            }

            // Simpan data ke database
            DB::beginTransaction();
            
            try {
                // Buat user dulu
                $user = User::create([
                    'name'     => $validated['name'],
                    'email'    => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'alamat'   => $validated['alamat'],
                    'no_telp'  => $validated['no_telp'],
                    'role'     => 'user',
                    'status'   => 'pending',
                ]);

                // Buat data pertanyaan
                registrasi_pertanyaan::create([
                    'user_id'            => $user->user_id, // Gunakan user_id
                    'pertanyaan1'        => json_encode($p1),
                    'pertanyaan1_custom' => $validated['pertanyaan1_custom'] ?? null,
                    'pertanyaan2'        => $p2,
                    'pertanyaan2_custom' => $validated['pertanyaan2_custom'] ?? null,
                    'mulai_jual'         => $validated['mulai_jual'],
                    'penjaga_stand'      => $validated['penjaga_stand'],
                ]);

                $message = "🔔 Persetujuan User Baru\n\n"
                . "Nama : {$user->name}\n"
                . "Email : {$user->email}\n"
                . "Tanggal Daftar : " . now()->format('d-m-Y H:i') . "\n\n"
                . "✅ Setujui / ❌ Tolak di dashboard admin.";

                $wa->notifyAdmin($message);

                DB::commit();

                return redirect()->route('user.login')
                    ->with('success', 'Registrasi berhasil. Akun Anda menunggu persetujuan admin.');
                    
            } catch (\Exception $e) {
                DB::rollback();
                return back()->withInput()
                    ->with('error', 'Database Error: ' . $e->getMessage());
            }

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'General Error: ' . $e->getMessage());
        }
    }

    public function createAdmin()
    {
        return view ('admin.register');
    }

    public function storeAdmin(Request $request)
    {
        try{
            $validated=$request->validate([
                'name'=> 'required|string|max:255',
                'email'=> 'required|string|email|max:50|unique:users,email',
                'password'=> 'required|confirmed|min:6',
                'alamat'=> 'required|string|max:255',
                'no_telp'=> 'required|string|max:15',
            ]);

            $user = User::create([
                'name'     => $validated['name'],
                    'email'    => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'alamat'   => $validated['alamat'],
                    'no_telp'  => $validated['no_telp'],
                    'role'     => 'admin',
                    'status'   => 'approve',
            ]);

            return redirect()->route('admin.login')
                ->with('success', 'Registrasi admin berhasil. Silakan login.');
        }

        catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'General Error: ' . $e->getMessage());
        }
    }
    
}

