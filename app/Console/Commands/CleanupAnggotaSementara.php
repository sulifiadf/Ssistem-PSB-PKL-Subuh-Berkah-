<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\rombong;
use App\Models\kehadiran;
use Carbon\Carbon;

class CleanupAnggotaSementara extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cleanup:anggota-sementara {--dry-run : Preview apa yang akan dihapus tanpa eksekusi}';

    /**
     * The console command description.
     */
    protected $description = 'Hapus anggota sementara yang sudah melewati masa berlaku (1 hari)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $today = Carbon::today();
        
        $this->info("🔍 Mencari anggota sementara yang sudah kadaluarsa...");
        $this->info("📅 Tanggal hari ini: " . $today->format('Y-m-d'));
        
        // Cari anggota sementara yang sudah melewati masa berlaku
        $rombongSementaraKadaluarsa = rombong::with(['user', 'lapak'])
            ->where('jenis', 'sementara')
            ->where(function ($query) use ($today) {
                $query->whereDate('berlaku_hingga', '<', $today)
                      ->orWhere(function ($subQuery) use ($today) {
                          // Fallback untuk data lama tanpa berlaku_hingga
                          $subQuery->whereNull('berlaku_hingga')
                                   ->whereDate('created_at', '<', $today);
                      });
            })
            ->get();

        if ($rombongSementaraKadaluarsa->isEmpty()) {
            $this->info("✅ Tidak ada anggota sementara yang perlu dihapus.");
            return 0;
        }

        $this->info("📋 Ditemukan " . $rombongSementaraKadaluarsa->count() . " anggota sementara kadaluarsa:");
        
        foreach ($rombongSementaraKadaluarsa as $rombong) {
            $userName = $rombong->user->name ?? 'Unknown';
            $lapakName = $rombong->lapak->nama_lapak ?? 'Unknown';
            $namaUsaha = $rombong->nama_jualan ?? 'Unknown';
            $berlakuHingga = $rombong->berlaku_hingga ? 
                Carbon::parse($rombong->berlaku_hingga)->format('Y-m-d') : 
                'Tidak ada batas (data lama)';
            $createdAt = Carbon::parse($rombong->created_at)->format('Y-m-d H:i');
            
            $this->line("  🏪 {$userName} ({$namaUsaha}) di {$lapakName}");
            $this->line("     Berlaku hingga: {$berlakuHingga} | Created: {$createdAt}");
            
            if (!$dryRun) {
                // Hapus kehadiran terkait untuk rombong sementara ini
                $kehadiranTerkait = kehadiran::where('user_id', $rombong->user_id)
                    ->whereDate('tanggal', '>=', Carbon::parse($rombong->created_at)->toDateString())
                    ->get();
                    
                $kehadiranDihapus = 0;
                foreach ($kehadiranTerkait as $kehadiran) {
                    // Cek apakah user punya lapak tetap
                    $userLapakTetap = rombong::where('user_id', $rombong->user_id)
                        ->where('jenis', 'tetap')
                        ->first();
                        
                    if ($userLapakTetap) {
                        // Jika ada kehadiran masuk pada tanggal yang sama di lapak tetap, 
                        // berarti kehadiran ini untuk lapak sementara
                        $kehadiranLapakTetap = kehadiran::where('user_id', $rombong->user_id)
                            ->whereDate('tanggal', $kehadiran->tanggal)
                            ->where('status', 'masuk')
                            ->count();
                            
                        // Hanya hapus jika ada duplikasi kehadiran (berarti ada konflik)
                        if ($kehadiranLapakTetap > 1 || 
                            ($kehadiran->status === 'masuk' && $kehadiranLapakTetap === 1)) {
                            $kehadiran->delete();
                            $kehadiranDihapus++;
                        }
                    }
                }
                
                if ($kehadiranDihapus > 0) {
                    $this->line("     🗑️  Hapus {$kehadiranDihapus} record kehadiran");
                }
                
                // Hapus rombong sementara
                $rombong->delete();
                $this->line("     ✅ Rombong sementara dihapus!");
            } else {
                $this->line("     👁️  [DRY RUN] Akan dihapus");
            }
        }

        if ($dryRun) {
            $this->warn("⚠️  Ini adalah DRY RUN - tidak ada data yang benar-benar dihapus.");
            $this->info("💡 Jalankan tanpa --dry-run untuk eksekusi sebenarnya.");
        } else {
            $this->info("🎉 Cleanup selesai! " . $rombongSementaraKadaluarsa->count() . " anggota sementara dihapus.");
        }
        
        return 0;
    }
}
