<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\kehadiran;
use App\Models\rombong;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminKehadiranController extends Controller
{
    public function getStatusKehadiran()
    {
        $today = Carbon::today();
        
        // Ambil semua lapak dengan rombong yang diurutkan
        $lapaks = \App\Models\Lapak::with(['rombongs' => function($query) {
            $query->with('user')->orderBy('urutan', 'asc');
        }])->get();

        $data = [];
        
        foreach ($lapaks as $lapak) {
            // Cek apakah sudah ada yang masuk di lapak ini
            $adaYangMasuk = false;
            foreach ($lapak->rombongs as $rombong) {
                if ($rombong->user && in_array($rombong->user->status ?? '', ['approve', 'disetujui'])) {
                    $kehadiranCheck = \App\Models\kehadiran::where('user_id', $rombong->user_id)
                        ->whereDate('tanggal', $today)
                        ->where('status', 'masuk')
                        ->exists();
                    if ($kehadiranCheck) {
                        $adaYangMasuk = true;
                        break;
                    }
                }
            }
            
            foreach ($lapak->rombongs as $index => $rombong) {
                if (!$rombong->user || !in_array($rombong->user->status ?? '', ['approve', 'disetujui'])) {
                    continue; // Skip rombong tanpa user atau user tidak aktif
                }
                
                $kehadiran = \App\Models\kehadiran::where('user_id', $rombong->user_id)
                    ->whereDate('tanggal', $today)
                    ->first();
                
                // Default values
                $warnaButton = 'bg-gray-300 cursor-not-allowed';
                $statusText = 'MENUNGGU GILIRAN';
                
                if ($kehadiran) {
                    // Sudah ada data kehadiran
                    if ($kehadiran->status == 'masuk') {
                        $warnaButton = 'bg-green-500 hover:bg-green-600';
                        $statusText = 'MASUK';
                    } elseif ($kehadiran->status == 'libur') {
                        $warnaButton = 'bg-red-400 cursor-not-allowed';
                        $statusText = 'LIBUR';
                    }
                } else {
                    // Belum ada data kehadiran - cek kondisi
                    if ($adaYangMasuk) {
                        // Sudah ada yang masuk di lapak ini
                        $warnaButton = 'bg-gray-300 cursor-not-allowed';
                        $statusText = 'SUDAH ADA YANG MASUK';
                    } elseif ($index === 0) {
                        // Rombong urutan pertama yang belum konfirmasi dan belum ada yang masuk
                        $warnaButton = 'bg-yellow-500 hover:bg-yellow-600';
                        $statusText = 'MENUNGGU KONFIRMASI';
                    } else {
                        // Rombong urutan lain menunggu giliran
                        $warnaButton = 'bg-gray-300 cursor-not-allowed';
                        $statusText = 'MENUNGGU GILIRAN';
                    }
                }

                $data[] = [
                    'rombong_id' => $rombong->rombong_id,
                    'user_id' => $rombong->user_id,
                    'lapak_id' => $lapak->lapak_id,
                    'urutan' => $index,
                    'nama_jualan' => $rombong->nama_jualan ?? 'Tidak ada nama',
                    'user_name' => $rombong->user->name ?? 'User tidak ditemukan',
                    'warnaButton' => $warnaButton,
                    'status' => $kehadiran ? $kehadiran->status : null,
                    'statusText' => $statusText,
                    'adaYangMasuk' => $adaYangMasuk,
                    'pesan_wa_terkirim' => $kehadiran ? $kehadiran->pesan_wa_terkirim : false,
                    'waktu_konfirmasi' => $kehadiran ? $kehadiran->waktu_konfirmasi : null,
                ];
            }
        }

        return response()->json($data);
    }

    public function getDetailKehadiran($userId)
    {
        $today = Carbon::today();
        
        $kehadiran = kehadiran::where('user_id', $userId)
            ->whereDate('tanggal', $today)
            ->first();

        $user = User::find($userId);
        
        // Cari urutan rombong untuk menentukan status yang tepat
        $rombong = \App\Models\rombong::where('user_id', $userId)->first();
        $urutan = 0;
        if ($rombong && $rombong->lapak) {
            $rombongsDiLapak = $rombong->lapak->rombongs()->orderBy('urutan', 'asc')->get();
            foreach ($rombongsDiLapak as $index => $r) {
                if ($r->rombong_id === $rombong->rombong_id) {
                    $urutan = $index;
                    break;
                }
            }
        }
        
        // Tentukan status text yang sesuai dengan logic baru
        $statusText = 'MENUNGGU GILIRAN';
        if ($kehadiran) {
            if ($kehadiran->status == 'masuk') {
                $statusText = 'MASUK';
            } elseif ($kehadiran->status == 'libur') {
                $statusText = 'LIBUR';
            }
        } else {
            // Belum ada kehadiran - cek urutan
            if ($urutan === 0) {
                $statusText = 'MENUNGGU KONFIRMASI';
            } else {
                $statusText = 'MENUNGGU GILIRAN';
            }
        }
        
        return response()->json([
            'user_id' => $userId,
            'user_name' => $user->name ?? 'User tidak ditemukan',
            'status' => $kehadiran ? $kehadiran->status : null,
            'statusText' => $statusText,
            'urutan' => $urutan + 1, // Display urutan mulai dari 1
            'pesan_wa_terkirim' => $kehadiran ? $kehadiran->pesan_wa_terkirim : false,
            'waktu_konfirmasi' => $kehadiran && $kehadiran->waktu_konfirmasi 
                ? $kehadiran->waktu_konfirmasi->format('H:i:s') 
                : null,
            'tanggal' => $today->format('d/m/Y'),
            'keterangan' => $kehadiran ? $kehadiran->keterangan : null
        ]);
    }
}
