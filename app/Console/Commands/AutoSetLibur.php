<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Kehadiran;
use App\Models\Lapak;
use App\Models\Rombong;
use Carbon\Carbon;

class AutoSetLibur extends Command
{
    protected $signature = 'kehadiran:auto-libur';
    protected $description = 'Set otomatis libur dan kirim WA ke anggota berikutnya jika belum konfirmasi';

    public function handle()
    {
        $today = Carbon::today();

        // Semua yg belum konfirmasi diset otomatis libur
        Kehadiran::where('tanggal', $today)
            ->whereNull('status')
            ->update([
                'status' => 'libur',
                'waktu_konfirmasi' => Carbon::now(),
                'keterangan' => 'Otomatis diset libur karena tidak ada konfirmasi'
            ]);

        $this->info('Auto set libur selesai.');

        // Proses antrean lapak
        $this->prosesAntreanLapak($today);
    }

    private function prosesAntreanLapak($today)
    {
        $lapaks = Lapak::with(['rombongs' => function($query) {
            $query->whereHas('user', function($userQuery) {
                $userQuery->where('status', 'approve');
            })->orderBy('urutan', 'asc');
        }])->get();

        foreach ($lapaks as $lapak) {
            if ($lapak->rombongs->count() > 1) {
                $this->cekDanKirimKeAnggotaBerikutnya($lapak->rombongs, $lapak->nama_lapak, $today);
            }
        }
    }

    private function cekDanKirimKeAnggotaBerikutnya($rombongs, $namaLapak, $today)
    {
        $anggotaPertama = $rombongs->first();

        $kehadiranPertama = Kehadiran::where('user_id', $anggotaPertama->user->id)
            ->whereDate('tanggal', $today)
            ->first();

        // Jika anggota pertama dinyatakan LIBUR
        if ($kehadiranPertama && $kehadiranPertama->status === 'libur') {

            // Cek anggota berikutnya
            foreach ($rombongs->skip(1) as $rombong) {
                $kehadiranBerikutnya = Kehadiran::where('user_id', $rombong->user->id)
                    ->whereDate('tanggal', $today)
                    ->first();

                // Kalau belum ada record → buat & kirim WA
                if (!$kehadiranBerikutnya) {
                    $kehadiranBaru = Kehadiran::create([
                        'user_id' => $rombong->user->id,
                        'tanggal' => $today,
                        'status' => null,
                        'pesan_wa_terkirim' => false,
                        'jam_reminder' => Carbon::now()
                    ]);

                    $this->kirimWA($rombong->user, $namaLapak);
                    $kehadiranBaru->update(['pesan_wa_terkirim' => true]);

                    $this->info("WA terkirim ke {$rombong->user->name} untuk lapak {$namaLapak}");
                    break; // stop setelah kirim ke 1 orang
                }

                // Kalau sudah ada yg MASUK → hentikan antrean
                if ($kehadiranBerikutnya && $kehadiranBerikutnya->status === 'masuk') {
                    break;
                }
            }
        }
    }

    private function kirimWA($user, $namaLapak)
    {
        $pesan = "Halo {$user->name}!\n\n";
        $pesan .= "Anggota sebelumnya di {$namaLapak} tidak berjualan hari ini.\n";
        $pesan .= "Sekarang giliran Anda untuk konfirmasi kehadiran jualan.\n\n";
        $pesan .= "Silakan balas:\n";
        $pesan .= "- MASUK jika Anda akan berjualan\n";
        $pesan .= "- LIBUR jika Anda tidak berjualan\n\n";
        $pesan .= "⚠️ Segera konfirmasi agar lapak tidak kosong!";

        try {
            $response = Http::post('https://app.wablas.com/api/send-message', [
                'phone' => $user->no_telp,   // pastikan kolomnya sesuai
                'message' => $pesan,
                'token' => env('WHATSAPP_API_KEY')
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            $this->error("Gagal kirim WA antrean ke {$user->name}: " . $e->getMessage());
            return false;
        }
    }
}
