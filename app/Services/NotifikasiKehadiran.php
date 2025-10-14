<?php

namespace App\Services;

use App\Models\User;
use App\Models\rombong;
use App\Models\Lapak;
use App\Models\KehadiranToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotifikasiKehadiran
{
    // Gunakan konstanta yang sama dengan KehadiranController
    const BATAS_JAM_URUTAN_1 = 13; // Sesuaikan dengan controller
    const WINDOW_MENIT_KONFIRMASI = 30;

    /**
     * Method utama untuk kirim WA konfirmasi dengan link
     * Digunakan untuk kirim link konfirmasi ke user yang gilirannya
     */
    public static function kirimKonfirmasiWA($userId, $statusInfo = null, $tanggal = null, $posisiSebenarnya = null)
    {
        try {
            if (!$tanggal) {
                $tanggal = Carbon::today();
            }

            $user = User::find($userId);
            if (!$user) {
                Log::error('User tidak ditemukan', ['user_id' => $userId]);
                return ['success' => false, 'message' => 'User tidak ditemukan'];
            }

            $phone = self::getUserPhone($user);
            if (!$phone) {
                Log::error('User tidak memiliki nomor telepon', ['user_id' => $userId]);
                return ['success' => false, 'message' => 'User tidak memiliki nomor telepon'];
            }

            // Generate token baru menggunakan KehadiranToken
            $tokenRecord = KehadiranToken::generateToken($userId, $tanggal);

            // GUNAKAN FORMAT PESAN YANG SAMA DENGAN CONTROLLER
            $formUrl = rtrim(config('app.url'), '/') . '/kehadiran/wa-konfirmasi/' . $tokenRecord->token;
            
            // GUNAKAN POSISI YANG SUDAH DIHITUNG CONTROLLER JIKA ADA, ATAU HITUNG SENDIRI
            $posisi = $posisiSebenarnya ?? self::getUserPosition($userId);
            $lapak = self::getUserLapak($userId);
            
            // FORMAT PESAN YANG KONSISTEN
            $pesan = self::formatPesanKonfirmasi($user, $lapak, $posisi, $formUrl, $tanggal);

            // KIRIM WA
            $result = self::kirimWAMessage($phone, $pesan);
            
            if ($result['success']) {
                Log::info("WA konfirmasi link terkirim ke {$user->name} ({$phone})", [
                    'user_id' => $userId,
                    'token' => $tokenRecord->token,
                    'posisi' => $posisi
                ]);
                
                return [
                    'success' => true,
                    'message' => 'WA konfirmasi berhasil dikirim',
                    'token' => $tokenRecord->token,
                    'posisi' => $posisi
                ];
            }
            
            return $result;

        } catch (\Exception $e) {
            Log::error('Error kirim WA: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error sistem: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Method untuk kirim notifikasi SETELAH konfirmasi berhasil
     */
    public static function kirimNotifikasiSetelahKonfirmasi($userId, $status)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return false;
            }
            
            $phone = self::getUserPhone($user);
            if (!$phone) {
                return false;
            }

            $statusText = $status === 'masuk' ? 'masuk' : 'libur';
            $pesan = "Halo {$user->name}!\n\nKonfirmasi kehadiran berhasil!\nStatus: {$statusText}\nWaktu: " . now()->format('d/m/Y H:i:s');

            $result = self::kirimWAMessage($phone, $pesan);
            
            if ($result['success']) {
                Log::info("WA notifikasi setelah konfirmasi terkirim ke {$user->name}");
                
                // Jika status libur, kirim notifikasi ke urutan selanjutnya
                if ($status === 'libur') {
                    self::notifikasiUrutanSelanjutnyaSetelahLibur($userId);
                }
                
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error("WA Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Kirim notifikasi ke urutan selanjutnya setelah ada yang libur
     */
    private static function notifikasiUrutanSelanjutnyaSetelahLibur($userIdYangLibur)
    {
        try {
            // Cari lapak user yang libur
            $rombongYangLibur = rombong::where('user_id', $userIdYangLibur)->first();
            if (!$rombongYangLibur || !$rombongYangLibur->lapak_id) {
                return;
            }
            
            // Ambil semua rombong di lapak yang sama, diurutkan
            $allRombongsInLapak = rombong::where('lapak_id', $rombongYangLibur->lapak_id)
                ->orderBy('rombong_id', 'asc')
                ->get();
            
            // Cari posisi user yang libur
            $posisiYangLibur = null;
            foreach ($allRombongsInLapak as $index => $rombong) {
                if ($rombong->user_id == $userIdYangLibur) {
                    $posisiYangLibur = $index + 1;
                    break;
                }
            }
            
            if (!$posisiYangLibur) {
                return;
            }
            
            // Cari urutan selanjutnya yang belum ada kehadiran hari ini
            $today = Carbon::today();
            for ($i = $posisiYangLibur; $i < count($allRombongsInLapak); $i++) {
                $rombongSelanjutnya = $allRombongsInLapak[$i];
                
                // Skip jika tidak ada user_id
                if (!$rombongSelanjutnya->user_id) {
                    continue;
                }
                
                // Cek apakah sudah ada kehadiran hari ini
                $existingKehadiran = \App\Models\kehadiran::where('user_id', $rombongSelanjutnya->user_id)
                    ->whereDate('tanggal', $today)
                    ->first();
                
                if (!$existingKehadiran) {
                    // Kirim notifikasi ke urutan selanjutnya
                    $result = self::kirimKonfirmasiWA($rombongSelanjutnya->user_id);
                    
                    $userSelanjutnya = User::find($rombongSelanjutnya->user_id);
                    Log::info("WA sent to next user {$userSelanjutnya->name} (urutan " . ($i + 1) . ") after manual libur", [
                        'previous_user' => $userIdYangLibur,
                        'next_user' => $rombongSelanjutnya->user_id,
                        'result' => $result
                    ]);
                    
                    // Hanya kirim ke satu urutan selanjutnya saja
                    break;
                }
            }
        } catch (\Exception $e) {
            Log::error("Error sending notification to next user after manual libur: " . $e->getMessage(), [
                'user_id_libur' => $userIdYangLibur
            ]);
        }
    }

    /**
     * Helper method untuk kirim WA message (single HTTP call)
     * Made public untuk testing purposes
     */
    public static function kirimWAMessage($phone, $pesan)
    {
        try {
            $requestData = [
                'phone' => $phone,
                'message' => $pesan,
            ];

            $response = Http::timeout(10)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => env('WHATSAPP_API_KEY'), // Removed Bearer untuk match dengan HelpersController
                ])
                ->post(env('WHATSAPP_API_URL'), $requestData);

            if ($response->successful()) {
                $responseData = $response->json();
                
                if (isset($responseData['status']) && $responseData['status'] === true) {
                    return ['success' => true, 'message' => 'WA berhasil dikirim'];
                }
            }

            Log::error("Gagal kirim WA ke {$phone}: " . $response->body());
            return [
                'success' => false,
                'message' => 'Gagal mengirim WA: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Error kirim WA: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error kirim WA: ' . $e->getMessage()
            ];
        }
    }

    private static function getUserPhone($user)
    {
        return $user->no_telp ?? $user->whatsapp ?? $user->phone ?? null;
    }

    private static function getUserPosition($userId)
    {
        // Gunakan logic yang sama dengan KehadiranController
        $userRombong = rombong::where('user_id', $userId)->first();
        
        if (!$userRombong || !$userRombong->lapak_id) {
            return 1; // Default urutan 1
        }
        
        // Ambil semua rombong di lapak yang sama, diurutkan berdasarkan ID atau timestamp
        $allRombongsInLapak = rombong::where('lapak_id', $userRombong->lapak_id)
            ->orderBy('rombong_id', 'asc') // Asumsi yang pertama daftar adalah urutan 1
            ->get();
            
        // Cari posisi user dalam lapak
        $userPosition = 1;
        foreach ($allRombongsInLapak as $index => $rombong) {
            if ($rombong->user_id == $userId) {
                $userPosition = $index + 1; // Urutan dimulai dari 1
                break;
            }
        }
        
        return $userPosition;
    }

    private static function getUserLapak($userId)
    {
        $userRombong = rombong::where('user_id', $userId)->first();
        
        if ($userRombong && $userRombong->lapak_id) {
            return Lapak::find($userRombong->lapak_id);
        }
        
        return null;
    }

    private static function formatPesanKonfirmasi($user, $lapak, $posisi, $formUrl, $tanggal)
    {
        $pesan = "🏪 *Konfirmasi Kehadiran Lapak*\n\n";
        $pesan .= "Halo {$user->name},\n\n";
        
        if ($lapak) {
            $pesan .= "📍 *Lapak:* {$lapak->nama_lapak}\n";
            $pesan .= "📋 *Urutan Anda:* #{$posisi}\n";
        }
        
        $pesan .= "📅 *Tanggal:* " . $tanggal->format('d/m/Y') . "\n\n";
        $pesan .= "Silakan konfirmasi kehadiran Anda:\n\n";
        $pesan .= "🔗 *Klik link untuk konfirmasi:*\n";
        $pesan .= "{$formUrl}\n\n";
        
        // GUNAKAN PESAN YANG LEBIH SPESIFIK
        if ($posisi === 1) {
            $pesan .= "⏰ *Batas waktu:* " . self::BATAS_JAM_URUTAN_1 . ":00\n";
        } else {
            // Untuk urutan > 1, berikan info waktu yang lebih jelas
            $now = Carbon::now();
            $batasWaktu = $now->copy()->addMinutes(self::WINDOW_MENIT_KONFIRMASI);
            $pesan .= "⏰ *Waktu konfirmasi:* " . self::WINDOW_MENIT_KONFIRMASI . " menit dari sekarang\n";
            $pesan .= "⏰ *Batas waktu:* " . $batasWaktu->format('H:i') . "\n";
        }
        
        $pesan .= "\n🔒 *Link ini hanya berlaku untuk Anda dan hanya bisa digunakan sekali.*";
        
        return $pesan;
    }

    /**
     * Legacy method untuk backward compatibility
     * @deprecated Use kirimKonfirmasiWA() instead
     */
    public static function kirimNotifikasiWA($userId)
    {
        // Redirect ke method baru
        return self::kirimKonfirmasiWA($userId);
    }
}
