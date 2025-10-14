<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\rombong; 

class ProfileController extends Controller
{
    // Tampilkan form edit (sekalian show)
    public function edit()
    {
        $user = Auth::user();
        // jika belum punya rombong, kembalikan object kosong supaya blade aman
        $rombong = $user->rombong ?? new Rombong();
        return view('user.profile', compact('user', 'rombong'));
    }

    // Simpan/perbarui profile + rombong
    public function update(Request $request)
    {
        try {
            $user = Auth::user();

            // Validate data dengan pesan error kustom dan rules yang lebih ketat
            $validated = $request->validate([
                'name' => 'required|string|max:255|min:2',
                'alamat' => 'required|string|min:5|max:500',
                'no_telp' => [
                    'required',
                    'string',
                    'regex:/^62[0-9]{9,13}$/',
                    'unique:users,no_telp,' . $user->user_id . ',user_id'
                ],
                'email' => 'required|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
                'nama_jualan' => 'nullable|string|max:255|min:2',
                'foto_rombong' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'foto_tetangga_kanan' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'foto_tetangga_kiri' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                // Password fields
                'current_password' => 'nullable|string',
                'new_password' => [
                    'nullable',
                    'string',
                    'min:6',
                    'confirmed',
                ],
            ], [
                // Custom error messages
                'name.required' => 'Nama lengkap wajib diisi.',
                'name.min' => 'Nama minimal 2 karakter.',
                'alamat.required' => 'Alamat wajib diisi.',
                'alamat.min' => 'Alamat minimal 5 karakter.',
                'alamat.max' => 'Alamat maksimal 500 karakter.',
                'no_telp.required' => 'Nomor WhatsApp wajib diisi.',
                'no_telp.regex' => 'Format nomor WhatsApp harus dimulai dengan 62 dan diikuti 9-13 digit angka. Contoh: 6281234567890',
                'no_telp.unique' => 'Nomor WhatsApp sudah digunakan oleh user lain.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.unique' => 'Email sudah digunakan oleh user lain.',
                'nama_jualan.min' => 'Nama jualan minimal 2 karakter.',
                'foto_rombong.image' => 'File foto rombong harus berupa gambar.',
                'foto_rombong.mimes' => 'Foto rombong harus berformat jpeg, png, jpg, atau gif.',
                'foto_rombong.max' => 'Ukuran foto rombong maksimal 2MB.',
                'foto_tetangga_kanan.image' => 'File foto tetangga kanan harus berupa gambar.',
                'foto_tetangga_kanan.mimes' => 'Foto tetangga kanan harus berformat jpeg, png, jpg, atau gif.',
                'foto_tetangga_kanan.max' => 'Ukuran foto tetangga kanan maksimal 2MB.',
                'foto_tetangga_kiri.image' => 'File foto tetangga kiri harus berupa gambar.',
                'foto_tetangga_kiri.mimes' => 'Foto tetangga kiri harus berformat jpeg, png, jpg, atau gif.',
                'foto_tetangga_kiri.max' => 'Ukuran foto tetangga kiri maksimal 2MB.',
                'new_password.min' => 'Password baru minimal 6 karakter.',
                'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
            ]);

            // Validasi tambahan untuk format nomor WhatsApp
            if (!preg_match('/^628[0-9]{8,12}$/', $validated['no_telp'])) {
                return back()->withErrors([
                    'no_telp' => 'Format nomor WhatsApp tidak valid. Harus dimulai dengan 628 dan diikuti 8-12 digit angka.'
                ])->withInput();
            }

            // Handle password change dengan validasi yang lebih baik
            if ($request->filled('current_password') || $request->filled('new_password')) {
                if (!$request->filled('current_password')) {
                    return back()->withErrors(['current_password' => 'Password lama harus diisi untuk mengubah password.'])
                                ->withInput();
                }
                
                if (!Hash::check($request->current_password, $user->password)) {
                    return back()->withErrors(['current_password' => 'Password lama tidak benar.'])
                                ->withInput();
                }
                
                if (!$request->filled('new_password')) {
                    return back()->withErrors(['new_password' => 'Password baru harus diisi.'])
                                ->withInput();
                }
                
                if ($request->current_password === $request->new_password) {
                    return back()->withErrors(['new_password' => 'Password baru harus berbeda dengan password lama.'])
                                ->withInput();
                }
                
                $user->password = Hash::make($request->new_password);
            }

            // Mulai transaksi database
            \DB::beginTransaction();

            try {
                // Update USER data dengan data yang sudah dinormalisasi
                $user->update([
                    'name' => trim($validated['name']),
                    'alamat' => trim($validated['alamat']),
                    'no_telp' => $validated['no_telp'],
                    'email' => strtolower(trim($validated['email'])),
                ]);

                // Ambil atau buat rombong (relasi hasOne)
                $rombong = $user->rombong ?: new Rombong();
                $rombong->user_id = $user->user_id; 
                if (isset($validated['nama_jualan']) && !empty(trim($validated['nama_jualan']))) {
                    $rombong->nama_jualan = trim($validated['nama_jualan']);
                }

                // Upload/replace foto (hapus file lama jika ada)
                if ($request->hasFile('foto_rombong')) {
                    try {
                        // Validasi tambahan untuk file
                        $file = $request->file('foto_rombong');
                        if (!$file->isValid()) {
                            throw new \Exception('File foto rombong tidak valid atau rusak.');
                        }

                        // hapus yang lama
                        if ($rombong->foto_rombong && Storage::disk('public')->exists($rombong->foto_rombong)) {
                            Storage::disk('public')->delete($rombong->foto_rombong);
                        }
                        $rombong->foto_rombong = $file->store('rombong', 'public');
                    } catch (\Exception $e) {
                        \DB::rollback();
                        \Log::error('Error uploading foto rombong: ' . $e->getMessage());
                        return back()->withErrors(['foto_rombong' => 'Gagal mengupload foto rombong: ' . $e->getMessage()])
                                    ->withInput();
                    }
                }

                if ($request->hasFile('foto_tetangga_kanan')) {
                    try {
                        $file = $request->file('foto_tetangga_kanan');
                        if (!$file->isValid()) {
                            throw new \Exception('File foto tetangga kanan tidak valid atau rusak.');
                        }

                        if ($rombong->foto_tetangga_kanan && Storage::disk('public')->exists($rombong->foto_tetangga_kanan)) {
                            Storage::disk('public')->delete($rombong->foto_tetangga_kanan);
                        }
                        $rombong->foto_tetangga_kanan = $file->store('rombong', 'public');
                    } catch (\Exception $e) {
                        \DB::rollback();
                        \Log::error('Error uploading foto tetangga kanan: ' . $e->getMessage());
                        return back()->withErrors(['foto_tetangga_kanan' => 'Gagal mengupload foto tetangga kanan: ' . $e->getMessage()])
                                    ->withInput();
                    }
                }

                if ($request->hasFile('foto_tetangga_kiri')) {
                    try {
                        $file = $request->file('foto_tetangga_kiri');
                        if (!$file->isValid()) {
                            throw new \Exception('File foto tetangga kiri tidak valid atau rusak.');
                        }

                        if ($rombong->foto_tetangga_kiri && Storage::disk('public')->exists($rombong->foto_tetangga_kiri)) {
                            Storage::disk('public')->delete($rombong->foto_tetangga_kiri);
                        }
                        $rombong->foto_tetangga_kiri = $file->store('rombong', 'public');
                    } catch (\Exception $e) {
                        \DB::rollback();
                        \Log::error('Error uploading foto tetangga kiri: ' . $e->getMessage());
                        return back()->withErrors(['foto_tetangga_kiri' => 'Gagal mengupload foto tetangga kiri: ' . $e->getMessage()])
                                    ->withInput();
                    }
                }

                $rombong->save();
                \DB::commit();

                $successMessage = 'Profil berhasil diperbarui.';
                if ($request->filled('new_password')) {
                    $successMessage .= ' Password juga berhasil diubah.';
                }

                return redirect()->route('user.profile.edit')->with('success', $successMessage);

            } catch (\Exception $e) {
                \DB::rollback();
                \Log::error('Database error in profile update: ' . $e->getMessage());
                return back()->withErrors(['general' => 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.'])
                            ->withInput();
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors akan di-handle otomatis oleh Laravel
            throw $e;
        } catch (\Exception $e) {
            // Handle unexpected errors
            \Log::error('General error in profile update: ' . $e->getMessage());
            return back()->withErrors(['general' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.'])
                        ->withInput();
        }
    }

    public function deleteProfileImage()
    {
        $user = Auth::user();
        if ($user->rombong && $user->rombong->foto_rombong) {
            if (Storage::disk('public')->exists($user->rombong->foto_rombong)) {
                Storage::disk('public')->delete($user->rombong->foto_rombong);
            }
            $user->rombong->foto_rombong = null;
            $user->rombong->save();
        }
        return response()->json(['success' => true]);
    }

    public function deleteNeighborImage()
    {
        $user = Auth::user();
        if ($user->rombong) {
            if ($user->rombong->foto_tetangga_kanan && Storage::disk('public')->exists($user->rombong->foto_tetangga_kanan)) {
                Storage::disk('public')->delete($user->rombong->foto_tetangga_kanan);
            }
            if ($user->rombong->foto_tetangga_kiri && Storage::disk('public')->exists($user->rombong->foto_tetangga_kiri)) {
                Storage::disk('public')->delete($user->rombong->foto_tetangga_kiri);
            }

            $user->rombong->foto_tetangga_kanan = null;
            $user->rombong->foto_tetangga_kiri = null;
            $user->rombong->save();
        }
        return response()->json(['success' => true]);
    }

    public function getLocation()
    {
        $user = Auth::user();
        return response()->json([
            'latitude' => $user->rombong->latitude ?? null,
            'longitude' => $user->rombong->longitude ?? null
        ]);
    }

    public function updateLocation(Request $request)
    {
        try {
            // Validasi input dengan pesan error kustom
            $validated = $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ], [
                'latitude.required' => 'Latitude wajib diisi.',
                'latitude.numeric' => 'Latitude harus berupa angka.',
                'latitude.between' => 'Latitude harus berada antara -90 sampai 90.',
                'longitude.required' => 'Longitude wajib diisi.',
                'longitude.numeric' => 'Longitude harus berupa angka.',
                'longitude.between' => 'Longitude harus berada antara -180 sampai 180.',
            ]);

            $user = Auth::user();

            // Mulai transaksi database
            \DB::beginTransaction();

            try {
                $rombong = $user->rombong ?: new Rombong();
                $rombong->user_id = $user->user_id;
                $rombong->latitude = $validated['latitude'];
                $rombong->longitude = $validated['longitude'];
                $rombong->save();

                \DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Lokasi berhasil diperbarui.'
                ]);

            } catch (\Exception $e) {
                \DB::rollback();
                \Log::error('Database error in location update: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan lokasi. Silakan coba lagi.'
                ], 500);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('General error in location update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ], 500);
        }
    }

    public function settings()
    {
        return view('user.profile');
    }

    public function changePassword(Request $request)
    {
        try {
            // Validasi input dengan pesan error kustom yang lebih detail
            $validated = $request->validate([
                'current_password' => 'required|string',
                'new_password' => [
                    'required',
                    'string',
                    'min:6',
                    'confirmed',
                ],
                'new_password_confirmation' => 'required|string',
            ], [
                'current_password.required' => 'Password lama wajib diisi.',
                'new_password.required' => 'Password baru wajib diisi.',
                'new_password.min' => 'Password baru minimal 6 karakter.',
                'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
                'new_password_confirmation.required' => 'Konfirmasi password baru wajib diisi.',
            ]);

            $user = Auth::user();

            // Validasi password lama
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Password lama tidak benar.'])
                            ->withInput($request->except(['current_password', 'new_password', 'new_password_confirmation']));
            }

            // Validasi password baru tidak sama dengan password lama
            if ($validated['current_password'] === $validated['new_password']) {
                return back()->withErrors(['new_password' => 'Password baru harus berbeda dengan password lama.'])
                            ->withInput($request->except(['current_password', 'new_password', 'new_password_confirmation']));
            }

            // Mulai transaksi database
            \DB::beginTransaction();

            try {
                $user->password = Hash::make($validated['new_password']);
                $user->save();

                \DB::commit();

                // Log aktivitas perubahan password
                \Log::info('Password changed for user: ' . $user->email);

                return back()->with('success', 'Password berhasil diubah.');

            } catch (\Exception $e) {
                \DB::rollback();
                \Log::error('Database error in password change: ' . $e->getMessage());
                return back()->withErrors(['general' => 'Gagal mengubah password. Silakan coba lagi.'])
                            ->withInput($request->except(['current_password', 'new_password', 'new_password_confirmation']));
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors akan di-handle otomatis oleh Laravel
            throw $e;
        } catch (\Exception $e) {
            \Log::error('General error in password change: ' . $e->getMessage());
            return back()->withErrors(['general' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.'])
                        ->withInput($request->except(['current_password', 'new_password', 'new_password_confirmation']));
        }
    }

    public function uploadBulkImages(Request $request)
    {
        try {
            // Validasi input dengan pesan error kustom
            $request->validate([
                'images' => 'required|array|min:1',
                'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'images.required' => 'Gambar wajib dipilih.',
                'images.array' => 'Format gambar tidak valid.',
                'images.min' => 'Minimal pilih 1 gambar.',
                'images.*.required' => 'Setiap file harus berupa gambar.',
                'images.*.image' => 'File harus berupa gambar.',
                'images.*.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif.',
                'images.*.max' => 'Ukuran gambar maksimal 2MB per file.',
            ]);

            $uploadedPaths = [];
            $failedUploads = [];

            // Mulai transaksi database
            \DB::beginTransaction();

            try {
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $index => $image) {
                        try {
                            // Validasi tambahan untuk setiap file
                            if (!$image->isValid()) {
                                $failedUploads[] = "File ke-" . ($index + 1) . ": File tidak valid atau rusak.";
                                continue;
                            }

                            $path = $image->store('uploads', 'public');
                            $uploadedPaths[] = $path;

                        } catch (\Exception $e) {
                            \Log::error('Error uploading image ' . ($index + 1) . ': ' . $e->getMessage());
                            $failedUploads[] = "File ke-" . ($index + 1) . ": Gagal mengupload.";
                        }
                    }
                }

                \DB::commit();

                $response = [
                    'success' => true,
                    'uploaded_count' => count($uploadedPaths),
                    'uploaded_paths' => $uploadedPaths,
                ];

                if (!empty($failedUploads)) {
                    $response['partial_success'] = true;
                    $response['failed_uploads'] = $failedUploads;
                    $response['message'] = count($uploadedPaths) . ' gambar berhasil diupload, ' . count($failedUploads) . ' gagal.';
                } else {
                    $response['message'] = 'Semua gambar berhasil diupload.';
                }

                return response()->json($response);

            } catch (\Exception $e) {
                \DB::rollback();
                \Log::error('Database error in bulk image upload: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan gambar. Silakan coba lagi.'
                ], 500);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('General error in bulk image upload: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ], 500);
        }
    }
}
