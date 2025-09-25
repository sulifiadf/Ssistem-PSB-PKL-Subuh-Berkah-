<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\kehadiran;
use App\Models\User;
use App\Models\Lapak;
use App\Models\rombong;
use Carbon\Carbon;

class KirimReminderKehadiran extends Command
{
    protected $signature = 'kehadiran:reminder';
    protected $description = 'Kirim WA ke user untuk validasi kehadiran sesuai urutan rombong';

    public function handle()
    {
        $today = Carbon::today();

        // Ambil semua lapak beserta rombong yg sudah di-approve, urutkan berdasarkan urutan
        $lapaks = Lapak::with(['rombongs' => function($query) {
            $query->whereHas('user', function($userQuery) {
                $userQuery->where('status', 'approve');
            })->orderBy('urutan', 'asc');
        }])->get();

        foreach ($lapaks as $lapak) {
            if ($lapak->rombongs->count() > 0) {
                // Cari rombong urutan pertama yg belum konfirmasi
                $rombongAktif = $this->getAnggotaBelumKonfirmasi($lapak->rombongs, $today);

                if ($rombongAktif) {
                    $kehadiran = kehadiran::firstOrCreate(
                        [
                            'user_id' => $rombongAktif->user_id,
                            'tanggal' => $today
                        ],
                        [
                            'status' => null,
                            'pesan_wa_terkirim' => false,
                            'jam_reminder' => Carbon::now()
                        ]
                    );

                    // Kalau WA belum terkirim
                    if (!$kehadiran->pesan_wa_terkirim) {
                        $this->kirimPesanWA($rombongAktif->user, $lapak->nama_lapak);
                        $kehadiran->update([
                            'pesan_wa_terkirim' => true,
                            'jam_reminder' => Carbon::now()
                        ]);
                        $this->info("WA terkirim ke {$rombongAktif->user->name} untuk lapak {$lapak->nama_lapak}");
                    } else {
                        // Jika sudah terkirim, cek apakah sudah lebih dari 30 menit & belum konfirmasi
                        if ($kehadiran->status === null && Carbon::parse($kehadiran->jam_reminder)->addMinutes(30)->isPast()) {
                            $this->info("{$rombongAktif->user->name} tidak konfirmasi, oper ke urutan berikutnya.");
                            
                            // Tandai libur
                            $kehadiran->update(['status' => 'libur']);

                            // Cari rombong selanjutnya
                            $rombongBerikutnya = $this->getNextRombong($lapak->rombongs, $rombongAktif->urutan);
                            if ($rombongBerikutnya) {
                                $this->kirimPesanWA($rombongBerikutnya->user, $lapak->nama_lapak);
                                kehadiran::updateOrCreate(
                                    ['user_id' => $rombongBerikutnya->user_id, 'tanggal' => $today],
                                    ['status' => null, 'pesan_wa_terkirim' => true, 'jam_reminder' => Carbon::now()]
                                );
                                $this->info("WA dialihkan ke {$rombongBerikutnya->user->name} (urutan {$rombongBerikutnya->urutan})");
                            }
                        }
                    }
                }
            }
        }

        $this->info('Proses pengiriman reminder selesai.');
    }

    private function getAnggotaBelumKonfirmasi($rombongs, $today)
    {
        foreach ($rombongs as $rombong) {
            $kehadiran = kehadiran::where('user_id', $rombong->user_id)
                ->whereDate('tanggal', $today)
                ->first();

            // Kalau belum ada kehadiran atau status masih null → ini kandidat
            if (!$kehadiran || $kehadiran->status === null) {
                return $rombong;
            }

            // Kalau sudah ada yg MASUK → berhenti
            if ($kehadiran && $kehadiran->status === 'masuk') {
                break;
            }
        }

        return null;
    }

    private function getNextRombong($rombongs, $urutanSekarang)
    {
        return $rombongs->where('urutan', '>', $urutanSekarang)->first();
    }

    private function kirimPesanWA($user, $namaLapak)
    {
        $pesan = "Halo {$user->name}!\n\n";
        $pesan .= "Konfirmasi kehadiran jualan Anda hari ini di {$namaLapak}.\n";
        $pesan .= "Silakan balas:\n";
        $pesan .= "- MASUK jika Anda akan berjualan\n";
        $pesan .= "- LIBUR jika Anda tidak berjualan\n\n";
        $pesan .= "⚠️ Batas waktu konfirmasi: 30 menit setelah pesan ini.\n";
        $pesan .= "Jika tidak ada konfirmasi, otomatis dialihkan ke anggota berikutnya.";

        try {
            $response = Http::post(env('WHATSAPP_API_URL'), [
                'phone' => $user->no_telp,
                'message' => $pesan,
                'token' => env('WHATSAPP_API_KEY')
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            $this->error("Gagal mengirim WA ke {$user->name} ({$user->no_telp}): " . $e->getMessage());
            return false;
        }
    }
}
