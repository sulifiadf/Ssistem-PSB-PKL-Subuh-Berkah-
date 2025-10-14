<?php

namespace App\Http\Controllers\auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\registrasi_pertanyaan;
use App\Models\WaitingList;

class registerController extends Controller
{
    public function index()
    {
        return view('user.register');
    }

    public function createUser(Request $request)
    {
        return $this->store($request);
    }

    public function store(Request $request)
    {
        // Debug: Log semua data request yang diterima PERTAMA KALI
        \Log::info('=== REGISTRATION START ===', [
            'method' => $request->method(),
            'url' => $request->url(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip()
        ]);
        
        \Log::info('Raw request data:', [
            'pertanyaan2' => $request->input('pertanyaan2'),
            'pertanyaan2_custom' => $request->input('pertanyaan2_custom'),
            'has_pertanyaan2' => $request->has('pertanyaan2'),
            'all_except_passwords' => $request->except(['password', 'password_confirmation']),
            'all_keys' => array_keys($request->all())
        ]);

        try {
            // Debug: Log semua data request yang diterima
            \Log::info('Registration attempt with raw data:', [
                'pertanyaan2' => $request->input('pertanyaan2'),
                'pertanyaan2_custom' => $request->input('pertanyaan2_custom'),
                'has_pertanyaan2' => $request->has('pertanyaan2'),
                'all_request' => $request->except(['password', 'password_confirmation'])
            ]);

            $validated = $request->validate([
                'name' => 'required|string|max:255|min:2',
                'email' => 'required|string|email|max:50|unique:users,email',
                'password' => [
                    'required',
                    'confirmed',
                    'min:6',
                ],
                'alamat' => 'required|string|max:255|min:5',
                'no_telp' => [
                    'required',
                    'string',
                    'regex:/^62[0-9]{9,13}$/',
                    'unique:users,no_telp'
                ],
                'pertanyaan1' => 'nullable|array',
                'pertanyaan1.*' => 'string|max:50',
                'pertanyaan1_custom' => 'nullable|string|max:50',
                'pertanyaan2' => 'nullable|string|max:50',
                'pertanyaan2_custom' => 'nullable|numeric|min:0|max:999999',
                'mulai_jual' => 'required|date|before_or_equal:today',
                'penjaga_stand' => 'required|string|in:Saya Sendiri,Karyawan',
            ], [
                // Custom error messages
                'name.required' => 'Nama wajib diisi.',
                'name.min' => 'Nama minimal 2 karakter.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.unique' => 'Email sudah terdaftar.',
                'password.required' => 'Password wajib diisi.',
                'password.confirmed' => 'Konfirmasi password tidak cocok.',
                'password.min' => 'Password minimal 6 karakter.',
                'alamat.required' => 'Alamat wajib diisi.',
                'alamat.min' => 'Alamat minimal 5 karakter.',
                'no_telp.required' => 'Nomor WhatsApp wajib diisi.',
                'no_telp.regex' => 'Format nomor WhatsApp harus dimulai dengan 62 dan diikuti 9-13 digit angka. Contoh: 6281234567890',
                'no_telp.unique' => 'Nomor WhatsApp sudah terdaftar.',
                'pertanyaan2_custom.numeric' => 'Jumlah iuran harus berupa angka.',
                'pertanyaan2_custom.min' => 'Jumlah iuran tidak boleh negatif.',
                'pertanyaan2_custom.max' => 'Jumlah iuran terlalu besar.',
                'mulai_jual.required' => 'Tanggal mulai berjualan wajib diisi.',
                'mulai_jual.before_or_equal' => 'Tanggal mulai berjualan tidak boleh di masa depan.',
                'penjaga_stand.required' => 'Penjaga lapak wajib dipilih.',
                'penjaga_stand.in' => 'Pilihan penjaga lapak tidak valid.',
            ]);

            // Validasi tambahan untuk pertanyaan
            if (empty($validated['pertanyaan1']) && empty($validated['pertanyaan1_custom'])) {
                throw ValidationException::withMessages([
                    'pertanyaan1' => 'Pilih minimal satu opsi atau isi kolom lainnya untuk pertanyaan tentang ilmu yang ingin dipelajari.'
                ]);
            }

            // Validasi pertanyaan2 - PERBAIKAN: safe access untuk pertanyaan2
            $pertanyaan2Value = $request->input('pertanyaan2'); // Langsung dari request
            $pertanyaan2Custom = $request->input('pertanyaan2_custom');
            
            \Log::info('Pertanyaan2 validation check:', [
                'pertanyaan2Value' => $pertanyaan2Value,
                'pertanyaan2Custom' => $pertanyaan2Custom,
                'pertanyaan2_empty' => empty($pertanyaan2Value),
                'pertanyaan2_custom_empty' => empty($pertanyaan2Custom)
            ]);
            
            if (empty($pertanyaan2Value) && empty($pertanyaan2Custom)) {
                throw ValidationException::withMessages([
                    'pertanyaan2' => 'Pilih salah satu opsi atau isi kolom lainnya untuk pertanyaan tentang iuran harian.'
                ]);
            }

            // Normalisasi jawaban
            $p1 = $validated['pertanyaan1'] ?? [];
            if (!empty($validated['pertanyaan1_custom'])) {
                $p1[] = $validated['pertanyaan1_custom'];
            }

            // Gunakan variabel yang sudah didefinisikan di atas
            $p2_enum = null;      // Untuk kolom pertanyaan2 (enum)
            $p2_custom = null;    // Untuk kolom pertanyaan2_custom
            
            if (!empty($pertanyaan2Custom)) {
                // User mengisi custom input
                $p2_enum = 'lainnya';    // Set enum ke 'lainnya'
                $p2_custom = $pertanyaan2Custom; // Simpan nilai custom
            } elseif ($pertanyaan2Value !== null && $pertanyaan2Value !== '') {
                // User memilih radio button
                $p2_enum = $pertanyaan2Value;    // Simpan nilai radio button ke enum
                $p2_custom = null;               // Custom field kosong
            }

            // Validasi nomor telepon WhatsApp lebih detail
            if (!preg_match('/^628[0-9]{8,12}$/', $validated['no_telp'])) {
                throw ValidationException::withMessages([
                    'no_telp' => 'Format nomor WhatsApp tidak valid. Harus dimulai dengan 628 dan diikuti 8-12 digit angka.'
                ]);
            }

            // Debug logging - akan dihapus setelah issue fixed
            \Log::info('Debug pertanyaan2 values:', [
                'pertanyaan2Value' => $pertanyaan2Value,
                'pertanyaan2Custom' => $pertanyaan2Custom,
                'final_p2_enum' => $p2_enum,
                'final_p2_custom' => $p2_custom
            ]);

            // Simpan data ke database
            DB::beginTransaction();
            
            try {
                // Buat user dulu
                $user = User::create([
                    'name'     => trim($validated['name']),
                    'email'    => strtolower(trim($validated['email'])),
                    'password' => Hash::make($validated['password']),
                    'alamat'   => trim($validated['alamat']),
                    'no_telp'  => $validated['no_telp'],
                    'role'     => 'user',
                    'status'   => 'pending',
                ]);

                // Buat data pertanyaan
                registrasi_pertanyaan::create([
                    'user_id'            => $user->user_id,
                    'pertanyaan1'        => json_encode($p1),
                    'pertanyaan1_custom' => $validated['pertanyaan1_custom'] ?? null,
                    'pertanyaan2'        => $p2_enum,
                    'pertanyaan2_custom' => $p2_custom,
                    'mulai_jual'         => $validated['mulai_jual'],
                    'penjaga_stand'      => $validated['penjaga_stand'],
                ]);

                // Catatan: Waiting list akan dibuat hanya ketika user mengajukan ke lapak tertentu
                // Tidak membuat waiting list otomatis saat registrasi

                // TODO: Implement WhatsApp notification later
                // Kirim notifikasi WA ke admin (disabled for now)
                \Log::info('New user registered successfully', [
                    'user_id' => $user->user_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->no_telp
                ]);

                DB::commit();

                return redirect()->route('user.login')
                    ->with('success', 'Registrasi berhasil! Akun Anda sedang menunggu persetujuan admin. Silakan cek email atau WhatsApp untuk update status.');

            } catch (\Exception $dbError) {
                DB::rollback();
                \Log::error('Database error during user registration: ' . $dbError->getMessage(), [
                    'user_data' => $validated,
                    'trace' => $dbError->getTraceAsString()
                ]);
                throw $dbError;
            }

        } catch (ValidationException $e) {
            \Log::info('Validation failed during registration', [
                'errors' => $e->errors(),
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('General error during user registration: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.');
        }
    }

    // Method untuk register admin (tanpa pertanyaan)
    public function adminIndex()
    {
        return view('admin.register');
    }

    public function adminStore(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|min:2',
                'email' => 'required|string|email|max:50|unique:users,email',
                'password' => 'required|min:6',
                'alamat' => 'required|string|max:255|min:5',
                'no_telp' => [
                    'required',
                    'string',
                    'regex:/^62[0-9]{9,13}$/',
                    'unique:users,no_telp'
                ],
            ], [
                'name.required' => 'Nama wajib diisi.',
                'name.min' => 'Nama minimal 2 karakter.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.unique' => 'Email sudah terdaftar.',
                'password.required' => 'Password wajib diisi.',
                'password.min' => 'Password minimal 6 karakter.',
                'alamat.required' => 'Alamat wajib diisi.',
                'alamat.min' => 'Alamat minimal 5 karakter.',
                'no_telp.required' => 'Nomor WhatsApp wajib diisi.',
                'no_telp.regex' => 'Format nomor WhatsApp harus dimulai dengan 62 dan diikuti 9-13 digit angka. Contoh: 6281234567890',
                'no_telp.unique' => 'Nomor WhatsApp sudah terdaftar.',
            ]);

            // Validasi nomor telepon WhatsApp lebih detail
            if (!preg_match('/^628[0-9]{8,12}$/', $validated['no_telp'])) {
                throw ValidationException::withMessages([
                    'no_telp' => 'Format nomor WhatsApp tidak valid. Harus dimulai dengan 628 dan diikuti 8-12 digit angka.'
                ]);
            }

            // Simpan admin ke database (tanpa pertanyaan dan waiting list)
            DB::beginTransaction();
            
            try {
                $admin = User::create([
                    'name'     => trim($validated['name']),
                    'email'    => strtolower(trim($validated['email'])),
                    'password' => Hash::make($validated['password']),
                    'alamat'   => trim($validated['alamat']),
                    'no_telp'  => $validated['no_telp'],
                    'role'     => 'admin',
                    'status'   => 'approve',
                ]);

                \Log::info('New admin registered successfully', [
                    'user_id' => $admin->user_id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'phone' => $admin->no_telp
                ]);

                DB::commit();

                return redirect()->route('admin.login')
                    ->with('success', 'Registrasi admin berhasil! Silakan login.');

            } catch (\Exception $dbError) {
                DB::rollback();
                \Log::error('Database error during admin registration: ' . $dbError->getMessage());
                throw $dbError;
            }

        } catch (ValidationException $e) {
            \Log::info('Validation failed during admin registration', [
                'errors' => $e->errors()
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('General error during admin registration: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.');
        }
    }
}