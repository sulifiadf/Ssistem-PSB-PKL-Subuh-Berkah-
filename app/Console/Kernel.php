<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\User\KehadiranController;
use App\Models\KehadiranToken;

class Kernel extends ConsoleKernel
{
    public function schedule(Schedule $schedule)
    {
        // SIMPLE TEST SCHEDULER - untuk memastikan scheduler terdeteksi
        $schedule->call(function () {
            file_put_contents(__DIR__ . '/../../simple_scheduler_test.log', 
                '[' . date('Y-m-d H:i:s') . '] Simple scheduler is working!' . PHP_EOL, 
                FILE_APPEND
            );
        })->everyMinute()->name('simple-test');
        
        // DEBUG SCHEDULER - Test setiap menit untuk memastikan scheduler jalan
        $schedule->call(function () {
            Log::info('DEBUG: Scheduler is running - ' . now());
        })->everyMinute()->name('debug-scheduler');
        
        // KIRIM WA REMINDER pagi
        $schedule->call(function () {
            try {
                $controller = new KehadiranController();
                $result = $controller->processKonfirmasiMassal();
                Log::info('WA Reminder sent (morning)', $result);
            } catch (\Exception $e) {
                Log::error('Error in morning WA reminder: ' . $e->getMessage());
            }
        })->dailyAt('09:00')->name('kirim-wa-pagi');
        
        // KIRIM WA REMINDER siang (backup)
        $schedule->call(function () {
            try {
                $controller = new KehadiranController();
                $result = $controller->processKonfirmasiMassal();
                Log::info('WA Reminder sent (afternoon)', $result);
            } catch (\Exception $e) {
                Log::error('Error in afternoon WA reminder: ' . $e->getMessage());
            }
        })->dailyAt('10:00')->name('kirim-wa-siang');
        
        // KIRIM WA REMINDER jam 11 (reminder terakhir)
        $schedule->call(function () {
            try {
                $controller = new KehadiranController();
                $result = $controller->processKonfirmasiMassal();
                Log::info('WA Reminder sent (11am final)', $result);
            } catch (\Exception $e) {
                Log::error('Error in 11am WA reminder: ' . $e->getMessage());
            }
        })->dailyAt('11:00')->name('kirim-wa-jam11');
        
        // PROSES AUTO-LIBUR setiap 5 menit
        $schedule->call(function () {
            try {
                $controller = new KehadiranController();
                $controller->prosesAutoLibur();
                Log::info('Auto-libur processed successfully');
            } catch (\Exception $e) {
                Log::error('Error in auto-libur process: ' . $e->getMessage());
            }
        })->everyFiveMinutes()->name('auto-libur-monitor');
        
        // CLEANUP ANGGOTA SEMENTARA setiap hari jam 6 pagi
        $schedule->command('cleanup:anggota-sementara')
            ->dailyAt('06:00')
            ->name('cleanup-anggota-sementara');
        
        // CLEANUP token expired setiap hari
        $schedule->call(function () {
            try {
                $deletedCount = KehadiranToken::where('expires_at', '<', now())->delete();
                Log::info('Expired tokens cleaned: ' . $deletedCount . ' tokens deleted');
            } catch (\Exception $e) {
                Log::error('Error cleaning expired tokens: ' . $e->getMessage());
            }
        })->daily()->name('cleanup-tokens');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}