<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\kehadiran;
use App\Models\User;
use App\Models\Lapak;
use App\Models\Rombong;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class AdminKehadiranController extends Controller
{
    public function kirimReminderManual()
    {
        try {
            // Jalankan command reminder
            Artisan::call('kehadiran:reminder');
            
            return response()->json([
                'message' => 'Reminder kehadiran berhasil dikirim ke semua anggota urutan pertama.',
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengirim reminder: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function setLiburManual()
    {
        try {
            // Jalankan command auto libur
            Artisan::call('kehadiran:auto-libur');
            
            return response()->json([
                'message' => 'Berhasil set libur untuk yang belum konfirmasi dan memproses antrean.',
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal set libur manual: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function kirimWAManual(Request $request)
    {
        $userId = $request->user_id;
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        try {
            // Ambil nama lapak user
            $rombong = Rombong::where('user_id', $userId)->first();
            $namaLapak = $rombong && $rombong->lapak ? $rombong->lapak->nama_lapak : 'Lapak';
            
            // Kirim WA
            $berhasil = $this->kirimWA($user, $namaLapak);
            
            if ($berhasil) {
                // Update atau buat record kehadiran
                kehadiran::updateOrCreate(
                    ['user_id' => $userId, 'tanggal' => Carbon::today()],
                    ['pesan_wa_terkirim' => true]
                );
                
                return response()->json([
                    'message' => "WA berhasil dikirim ke {$user->name}",
                    'status' => 'success'
                ]);
            } else {
                return response()->json([
                    'message' => "Gagal mengirim WA ke {$user->name}",
                    'status' => 'error'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function exportKehadiran()
    {
        $today = Carbon::today();
        
        $kehadirans = kehadiran::with(['user', 'user.rombongs.lapak'])
            ->whereDate('tanggal', $today)
            ->get();

        $filename = 'kehadiran_' . $today->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($kehadirans) {
            $file = fopen('php://output', 'w');
            
            // Header CSV
            fputcsv($file, [
                'Nama',
                'Lapak', 
                'Nama Jualan',
                'Status',
                'WA Terkirim',
                'Waktu Konfirmasi',
                'Keterangan'
            ]);
            
            foreach ($kehadirans as $kehadiran) {
                $rombong = $kehadiran->user->rombongs->first();
                
                fputcsv($file, [
                    $kehadiran->user->name,
                    $rombong && $rombong->lapak ? $rombong->lapak->nama_lapak : '-',
                    $rombong ? $rombong->nama_jualan : '-',
                    $kehadiran->status ?: 'Belum Konfirmasi',
                    $kehadiran->pesan_wa_terkirim ? 'Ya' : 'Tidak',
                    $kehadiran->waktu_konfirmasi ? $kehadiran->waktu_konfirmasi->format('H:i:s') : '-',
                    $kehadiran->keterangan ?: '-'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getDashboardStats()
    {
        $today = Carbon::today();
        
        $stats = [
            'total_lapak' => Lapak::count(),
            'total_anggota' => User::where('status', 'approve')->count(),
            'hari_ini' => [
                'masuk' => kehadiran::whereDate('tanggal', $today)->where('status', 'masuk')->count(),
                'libur' => kehadiran::whereDate('tanggal', $today)->where('status', 'libur')->count(),
                'menunggu' => kehadiran::whereDate('tanggal', $today)
                    ->whereNull('status')
                    ->where('pesan_wa_terkirim', true)
                    ->count(),
                'belum_wa' => User::where('status', 'approve')->whereDoesntHave('kehadirans', function($query) use ($today) {
                    $query->whereDate('tanggal', $today);
                })->count()
            ]
        ];
        
        return response()->json($stats);
    }

    public function getKehadiranByLapak()
    {
        $today = Carbon::today();
        
        $lapaks = Lapak::with(['rombongs' => function($query) use ($today) {
            $query->whereHas('user', function($userQuery) {
                $userQuery->where('status', 'approve');
            })
            ->with(['user.kehadirans' => function($kehadiranQuery) use ($today) {
                $kehadiranQuery->whereDate('tanggal', $today);
            }])
            ->orderBy('urutan', 'asc');
        }])->get();

        $data = [];
        foreach ($lapaks as $lapak) {
            $anggota = [];
            foreach ($lapak->rombongs as $index => $rombong) {
                $kehadiran = $rombong->user->kehadirans->first();
                
                $anggota[] = [
                    'urutan' => $index + 1,
                    'nama' => $rombong->user->name,
                    'nama_jualan' => $rombong->nama_jualan,
                    'status' => $kehadiran ? $kehadiran->status : null,
                    'wa_terkirim' => $kehadiran ? $kehadiran->pesan_wa_terkirim : false,
                    'waktu_konfirmasi' => $kehadiran && $kehadiran->waktu_konfirmasi ? 
                        $kehadiran->waktu_konfirmasi->format('H:i') : null
                ];
            }
            
            $data[] = [
                'nama_lapak' => $lapak->nama_lapak,
                'anggota' => $anggota
            ];
        }
        
        return response()->json($data);
    }

    private function kirimWA($user, $namaLapak)
    {
        $pesan = "🔔 REMINDER KEHADIRAN\n\n";
        $pesan .= "Halo {$user->name}!\n\n";
        $pesan .= "Konfirmasi kehadiran jualan Anda hari ini di {$namaLapak}.\n";
        $pesan .= "Silakan balas:\n";
        $pesan .= "- MASUK (jika akan berjualan)\n";
        $pesan .= "- LIBUR (jika tidak berjualan)\n\n";
        $pesan .= "⚠️ Batas waktu: 18:00 WIB\n";
        $pesan .= "Tanpa konfirmasi = otomatis LIBUR";

        try {
            // Implementasi sesuai API WhatsApp yang digunakan
            $response = Http::post(env('WHATSAPP_API_URL'), [
                'phone' => $user->no_telepon,
                'message' => $pesan
            ]);
            
            return $response->successful();
        } catch (\Exception $e) {
            \Log::error("Gagal kirim WA ke {$user->name}: " . $e->getMessage());
            return false;
        }
    }
}