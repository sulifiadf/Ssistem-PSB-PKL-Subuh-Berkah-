<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kehadiran;
use App\Models\Rombong;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminKehadiranController extends Controller
{
    public function getStatusKehadiran()
    {
        $today = Carbon::today();
        
        $rombongs = Rombong::with(['user', 'kehadiranHariIni', 'lapak'])
            ->whereHas('user')
            ->get();

        $data = $rombongs->map(function($rombong) use ($today) {
            $kehadiran = $rombong->kehadiranHariIni;
            
            // Default values
            $warnaButton = 'bg-[#CFB47D] hover:bg-[#b89e65]';
            $statusText = 'STANDBY';
            
            if ($kehadiran) {
                if ($kehadiran->pesan_wa_terkirim) {
                    if ($kehadiran->status == 'masuk') {
                        $warnaButton = 'bg-green-500 hover:bg-green-600';
                        $statusText = 'MASUK';
                    } elseif ($kehadiran->status == 'libur') {
                        $warnaButton = 'bg-red-500 hover:bg-red-600';
                        $statusText = 'LIBUR';
                    } else {
                        // Status null atau lainnya - menunggu konfirmasi
                        $warnaButton = 'bg-yellow-500 hover:bg-yellow-600 animate-pulse';
                        $statusText = 'MENUNGGU KONFIRMASI';
                    }
                } else {
                    // WA belum terkirim tapi ada record
                    if ($kehadiran->status == 'masuk') {
                        $warnaButton = 'bg-green-300 hover:bg-green-400';
                        $statusText = 'MASUK (WA Belum)';
                    } elseif ($kehadiran->status == 'libur') {
                        $warnaButton = 'bg-red-300 hover:bg-red-400';
                        $statusText = 'LIBUR (WA Belum)';
                    }
                }
            }
            
            // Jika tidak ada kehadiran, tetap menggunakan default (STANDBY)

            return [
                'rombong_id' => $rombong->rombong_id,
                'user_id' => $rombong->user_id,
                'nama_jualan' => $rombong->nama_jualan ?? 'Tidak ada nama',
                'user_name' => $rombong->user->name ?? 'User tidak ditemukan',
                'warnaButton' => $warnaButton,
                'status' => $kehadiran ? $kehadiran->status : null,
                'statusText' => $statusText,
                'pesan_wa_terkirim' => $kehadiran ? $kehadiran->pesan_wa_terkirim : false,
                'waktu_konfirmasi' => $kehadiran ? $kehadiran->waktu_konfirmasi : null,
            ];
        });

        return response()->json($data);
    }

    public function getDetailKehadiran($userId)
    {
        $today = Carbon::today();
        
        $kehadiran = Kehadiran::where('user_id', $userId)
            ->whereDate('tanggal', $today)
            ->first();

        $user = User::find($userId);
        
        // Tentukan status text yang sesuai
        $statusText = 'STANDBY';
        if ($kehadiran) {
            if ($kehadiran->pesan_wa_terkirim) {
                if ($kehadiran->status == 'masuk') {
                    $statusText = 'MASUK';
                } elseif ($kehadiran->status == 'libur') {
                    $statusText = 'LIBUR';
                } else {
                    $statusText = 'MENUNGGU KONFIRMASI';
                }
            } else {
                if ($kehadiran->status == 'masuk') {
                    $statusText = 'MASUK (WA Belum)';
                } elseif ($kehadiran->status == 'libur') {
                    $statusText = 'LIBUR (WA Belum)';
                }
            }
        }
        
        return response()->json([
            'user_id' => $userId,
            'user_name' => $user->name ?? 'User tidak ditemukan',
            'status' => $kehadiran ? $kehadiran->status : null,
            'statusText' => $statusText,
            'pesan_wa_terkirim' => $kehadiran ? $kehadiran->pesan_wa_terkirim : false,
            'waktu_konfirmasi' => $kehadiran && $kehadiran->waktu_konfirmasi 
                ? $kehadiran->waktu_konfirmasi->format('H:i:s') 
                : null,
            'tanggal' => $today->format('d/m/Y'),
            'keterangan' => $kehadiran ? $kehadiran->keterangan : null
        ]);
    }
}