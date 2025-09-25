<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kehadiran;
use App\Models\User;
use App\Models\Rombong;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KehadiranController extends Controller
{
    public function konfirmasi(Request $request)
    {
        $userId = auth()->id();
        $status = strtolower(trim($request->input('status')));
        $today = Carbon::today();

        if (!in_array($status, ['masuk', 'libur'])) {
            return back()->with('error', 'Status tidak valid.');
        }

        $rombong = Rombong::where('user_id', $userId)->first();
        if (!$rombong) {
            return back()->with('error', 'Anda tidak memiliki rombong.');
        }

        // Cek apakah sudah konfirmasi hari ini
        if (Kehadiran::where('user_id', $userId)->whereDate('tanggal', $today)->exists()) {
            return back()->with('error', 'Anda sudah konfirmasi hari ini.');
        }

        $urutan = $rombong->urutan;
        $lapakId = $rombong->lapak_id;

        // 🔹 Logika khusus urutan 1
        if ($urutan == 1) {
            if (now()->hour >= 20) {
                $status = 'libur'; // auto libur
            }
        } else {
            // 🔹 Logika urutan > 1
            $prevRombong = Rombong::where('lapak_id', $lapakId)->where('urutan', $urutan - 1)->first();
            if ($prevRombong) {
                $prevKehadiran = Kehadiran::where('user_id', $prevRombong->user_id)
                    ->whereDate('tanggal', $today)
                    ->first();

                if (!$prevKehadiran) {
                    return back()->with('error', 'Menunggu konfirmasi rombong sebelumnya.');
                }

                if ($prevKehadiran->status == 'masuk') {
                    return back()->with('error', 'Rombong sebelumnya masuk, Anda tidak bisa konfirmasi.');
                }

                if ($prevKehadiran->status == 'libur') {
                    $batas = Carbon::parse($prevKehadiran->waktu_konfirmasi)->addMinutes(30);
                    if (now()->gt($batas)) {
                        $status = 'libur'; // auto libur
                    }
                }
            }
        }

        // Simpan data kehadiran
        Kehadiran::create([
            'user_id' => $userId,
            'rombong_id' => $rombong->id,
            'tanggal' => $today,
            'status' => $status,
            'waktu_konfirmasi' => now()
        ]);

        $this->clearKehadiranCache($userId, $today);

        return back()->with('success', "Kehadiran berhasil dikonfirmasi sebagai {$status}");
    }

    private function clearKehadiranCache($userId, $date)
    {
        Cache::forget('kehadiran_status_' . $date->format('Y-m-d'));
        Cache::forget('dashboard_data_' . $userId);
    }

    private function kirimKonfirmasiWA($userId, $status)
    {
        try {
            $user = User::find($userId, ['user_id', 'phone', 'name']);
            if (!$user || !$user->phone) return false;

            $pesan = "Halo {$user->name}!\n\nKonfirmasi kehadiran berhasil!\nStatus: {$status}\nWaktu: " . now()->format('d/m/Y H:i:s');

            Http::timeout(10)->post(env('WHATSAPP_API_URL'), [
                'phone' => $user->phone,
                'message' => $pesan,
                'token' => env('WHATSAPP_API_KEY')
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("WA Error: " . $e->getMessage());
            return false;
        }
    }
}
