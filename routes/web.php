<?php

use Illuminate\Http\Request;
use App\Services\NotifikasiKehadiran;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

use App\Http\Controllers\HelpersController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\auth\LoginController;
use App\Http\Controllers\Admin\LapakController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\RombongController;
use App\Http\Controllers\auth\registerController;
use App\Http\Controllers\Admin\KeuanganController;
use App\Http\Controllers\User\KehadiranController;
use App\Http\Controllers\Admin\PerpindahanController;
use App\Http\Controllers\Admin\WaitingListController;
use App\Http\Controllers\Auth\LupaPasswordController;
use App\Http\Controllers\Admin\AdminKehadiranController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

// Route untuk testing WA massal
Route::get('/test-wa-massal', function() {
    $kehadiranController = new App\Http\Controllers\User\KehadiranController();
    $results = $kehadiranController->processKonfirmasiMassal();
    return response()->json([
        'success' => true,
        'message' => 'Proses kirim WA massal selesai',
        'results' => $results
    ]);
})->name('test.wa.massal');

// Route untuk test WA individual
Route::get('/test-wa-individual/{userId}', function($userId) {
    $controller = new App\Http\Controllers\User\KehadiranController();
    $result = $controller->kirimKonfirmasiWALink($userId);
    return response()->json($result);
})->name('test.wa.individual');

// ============== TESTING AUTO LIBUR ==============
Route::get('/test-auto-libur', function() {
    $kehadiranController = new App\Http\Controllers\User\KehadiranController();
    $kehadiranController->prosesAutoLibur();
    return response()->json([
        'success' => true,
        'message' => 'Auto libur processing completed'
    ]);
})->name('test.auto-libur');

// ============== TESTING STATUS ==============
Route::get('/test-status/{userId}', function($userId) {
    $kehadiranController = new App\Http\Controllers\User\KehadiranController();
    $status = $kehadiranController->getStatusKonfirmasi($userId);
    return response()->json($status);
})->name('test.status');

// ============== TESTING LAPAK ==============
Route::get('/test-lapak/{lapakId}', function($lapakId) {
    $kehadiranController = new App\Http\Controllers\User\KehadiranController();
    $status = $kehadiranController->cekKehadiranLapak($lapakId);
    return response()->json($status);
})->name('test.lapak');




Route::get('/', function () {
    return redirect()->route('user.login');
});

// =================== TESTING HELPERS ===================
Route::get('/helpers/testWa', [HelpersController::class, 'testWa'] );

// =================== ADMIN ===================
Route::prefix('admin')->name('admin.')->group(function () {
    // Dashboard → hanya bisa diakses oleh admin yang login
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])
        ->middleware('auth.custom:admin')
        ->name('dashboard');

    Route::post('/perpindahan', [PerpindahanController::class, 'store'])->name('perpindahan.store');

    // Register
    Route::get('/register', [registerController::class, 'adminIndex'])->name('register.create');
    Route::post('/register', [registerController::class, 'adminStore'])->name('register.store');

    // Login
    Route::get('/login', function () {
        return view('admin.login');
    })->name('login');
    Route::post('/login', [LoginController::class, 'authenticateAdmin'])->name('login.submit');

    // Waiting List → hanya admin
    Route::middleware('auth.custom:admin')->group(function () {
        //approve user
        Route::get('/waitinglist', [WaitingListController::class, 'waitinglist'])->name('waitinglist');
        Route::post('/approve/{user}', [WaitingListController::class, 'approveUser'])->name('approve');
        Route::post('/reject/{user}', [WaitingListController::class, 'rejectUser'])->name('reject');

        //approve anggota
        Route::get('/anggota/waitinglist', [WaitingListController::class, 'anggota'])->name('anggota.waitinglist');
        Route::post('/anggota/{id}/approve', [WaitingListController::class, 'approveAnggota'])->name('anggota.approve');
        Route::post('/anggota/{id}/reject', [WaitingListController::class, 'rejectAnggota'])->name('anggota.reject');

        // CRUD User → khusus user
        Route::resource('/user', UserController::class)->except(['show']);

        //lapak
        Route::resource('/lapak', LapakController::class)->except(['show']);
        Route::post('/lapak/add-anggota', [LapakController::class, 'addAnggota'])->name('lapak.add-anggota');
        Route::post('/lapak/remove-anggota', [LapakController::class, 'removeAnggota'])->name('lapak.remove-anggota');

        //keuangan
        Route::resource('/keuangan', KeuanganController::class)->except(['show', 'create']);

        Route::get('/kehadiran/status', [AdminKehadiranController::class, 'getStatusKehadiran'])->name('kehadiran.status');
        Route::get('/kehadiran/detail/{userId}', [AdminKehadiranController::class, 'getDetailKehadiran'])->name('kehadiran.detail');
    });
});

// =================== USER ===================
Route::prefix('user')->name('user.')->group(function () {
    // Register
    Route::get('/register', [registerController::class, 'index'])->name('register.create');
    Route::post('/register', [registerController::class, 'store'])->name('register.store');

    // Login
    Route::get('/login', function () {
        return view('user.login');
    })->name('login');
    Route::post('/login', [LoginController::class, 'authenticateUser'])->name('login.submit');

    // Routes yang membutuhkan autentikasi user
    Route::middleware('auth.custom:user')->group(function () {
        // Dashboard
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

        // Rekap Kehadiran
        Route::get('/history', [App\Http\Controllers\User\RekapKehadiranController::class, 'index'])->name('history');

        // Rekap Keuangan
        Route::get('/keuangan', [App\Http\Controllers\User\RekapKeuanganController::class, 'index'])->name('keuangan');

        // Routes untuk kehadiran 
        Route::post('/kehadiran/konfirmasi', [KehadiranController::class, 'konfirmasi'])
            ->name('kehadiran.konfirmasi');

        Route::get('/api/kehadiran/status', [KehadiranController::class, 'getStatusKehadiran'])
            ->name('api.kehadiran.status');

        // Routes untuk pengajuan anggota - API baru
        Route::get('/api/lapak/available-for-pengajuan', [KehadiranController::class, 'getAvailableLapaksForPengajuan'])
            ->name('api.lapak.available');
        
        Route::post('/api/pengajuan/validate', [KehadiranController::class, 'validatePengajuanAnggotaAPI'])
            ->name('api.pengajuan.validate');

        // Routes untuk rombong 
        Route::post('/rombong/store', [RombongController::class, 'store'])
            ->name('rombong.store');

        // =================== PROFILE ROUTES ===================
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // Image Management (AJAX Routes)
        Route::delete('/profile/delete-image', [ProfileController::class, 'deleteProfileImage'])->name('profile.delete-image');
        Route::delete('/profile/delete-neighbor-image', [ProfileController::class, 'deleteNeighborImage'])->name('profile.delete-neighbor-image');

        // Location Management (AJAX Routes)
        Route::get('/profile/location', [ProfileController::class, 'getLocation'])->name('profile.location');
        Route::post('/profile/update-location', [ProfileController::class, 'updateLocation'])->name('profile.update-location');

        // Additional Profile Routes (Change Password Only)
        Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');

        // =================== REMOVED BACKUP ROUTES - Using new WA system only ===================
    });


});

// =================== LOGOUT ROUTE ===================
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Direct password reset (bypass email verification)
Route::get('/auth/reset-password-direct', [LupaPasswordController::class, 'directResetForm'])
    ->name('password.direct.form');

Route::post('/auth/reset-password-direct', [LupaPasswordController::class, 'directResetPassword'])
    ->name('password.direct.update');


// =================== KEHADIRAN WA KONFIRMASI ===================
// Route untuk konfirmasi kehadiran via WhatsApp (menggunakan unified form)
Route::get('/kehadiran/wa-konfirmasi/{token}', [App\Http\Controllers\User\KehadiranController::class, 'showKonfirmasiWAForm'])
    ->name('kehadiran.wa-konfirmasi');

Route::post('/kehadiran/wa-konfirmasi/{token}', [App\Http\Controllers\User\KehadiranController::class, 'konfirmasiViaWA'])
    ->name('kehadiran.wa-konfirmasi.submit');

// Route untuk kirim WA konfirmasi (admin/testing)
Route::post('/kehadiran/kirim-wa/{userId}', [App\Http\Controllers\User\KehadiranController::class, 'kirimKonfirmasiWA'])
    ->name('kehadiran.kirim-wa');

// Route untuk test sistem komprehensif
Route::get('/test-sistem-konfirmasi', function() {
    $kehadiranController = new App\Http\Controllers\User\KehadiranController();
    $testResults = [];
    
    // Test dengan beberapa user
    $testUserIds = [20, 24, 25]; // User yang pernah kita debug
    
    foreach ($testUserIds as $userId) {
        $user = App\Models\User::find($userId);
        if ($user) {
            $statusKonfirmasi = $kehadiranController->getStatusKonfirmasi($userId);
            $dashboardData = $kehadiranController->getDashboardData($userId);
            
            $testResults[] = [
                'user_id' => $userId,
                'user_name' => $user->name,
                'whatsapp' => $user->whatsapp,
                'status_konfirmasi' => $statusKonfirmasi,
                'dashboard_summary' => [
                    'sudah_konfirmasi' => $dashboardData['sudahKonfirmasiHariIni'],
                    'show_batas_waktu' => $dashboardData['showBatasWaktu'],
                    'button_aktif' => $dashboardData['buttonKonfirmasiAktif'],
                    'batas_jam_urutan1' => $dashboardData['batasJamUrutan1']
                ]
            ];
        }
    }
    
    return response()->json([
        'timestamp' => now()->format('Y-m-d H:i:s'),
        'test_results' => $testResults,
        'summary' => [
            'total_users_tested' => count($testResults),
            'dapat_konfirmasi' => count(array_filter($testResults, function($r) {
                return $r['status_konfirmasi']['status'] === 'dapat_konfirmasi';
            })),
            'auto_libur' => count(array_filter($testResults, function($r) {
                return $r['status_konfirmasi']['status'] === 'auto_libur';
            })),
            'already_confirmed' => count(array_filter($testResults, function($r) {
                return $r['status_konfirmasi']['status'] === 'already_confirmed';
            }))
        ]
    ]);
})->name('test.sistem');

// =================== API ROUTES UNTUK FRONTEND ===================
// Routes API yang bisa diakses tanpa prefix user (untuk AJAX calls)
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/kehadiran/status', [KehadiranController::class, 'getStatusKehadiran'])
        ->name('kehadiran.status');

    // Route untuk dashboard data (jika diperlukan)
    Route::get('/dashboard/data', [UserDashboardController::class, 'getDashboardData'])
        ->name('dashboard.data')
        ->middleware('auth.custom:user');
        
    // Route untuk testing WhatsApp
    Route::get('/test-wa', [HelpersController::class, 'testWa'])
        ->name('test-wa');
    
    // Route untuk testing environment
    Route::get('/test-env', [HelpersController::class, 'testEnv'])
        ->name('test-env');
    
    // Route untuk cek PHP path di server
    Route::get('/check-server-info', function() {
        $info = [
            'php_path' => PHP_BINARY,
            'php_version' => PHP_VERSION,
            'current_directory' => getcwd(),
            'base_path' => base_path(),
            'public_path' => public_path(),
            'storage_path' => storage_path(),
            'artisan_exists' => file_exists(base_path('artisan')),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
            'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown',
        ];
        
        return response()->json($info, 200, [], JSON_PRETTY_PRINT);
    })->name('check-server-info');
    
    // Route untuk cek IP server
    Route::get('/check-server-ip', function() {
        $ip_info = [];
        
        // Server variables
        $ip_info['server_addr'] = $_SERVER['SERVER_ADDR'] ?? 'Unknown';
        $ip_info['local_addr'] = $_SERVER['LOCAL_ADDR'] ?? 'Unknown';
        $ip_info['http_host'] = $_SERVER['HTTP_HOST'] ?? 'Unknown';
        
        // External IP check
        $external_services = [
            'ipinfo.io' => 'https://ipinfo.io/ip',
            'ifconfig.me' => 'https://ifconfig.me/ip', 
            'ipify.org' => 'https://api.ipify.org'
        ];
        
        foreach ($external_services as $name => $url) {
            try {
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 10,
                        'method' => 'GET'
                    ]
                ]);
                $ip = @file_get_contents($url, false, $context);
                $ip_info['external_ip_' . $name] = $ip ? trim($ip) : 'Failed';
            } catch (Exception $e) {
                $ip_info['external_ip_' . $name] = 'Error: ' . $e->getMessage();
            }
        }
        
        // Hostname
        $hostname = gethostname();
        $ip_info['hostname'] = $hostname;
        if ($hostname) {
            $ip_info['hostname_ip'] = gethostbyname($hostname);
        }
        
        return response()->json($ip_info, 200, [], JSON_PRETTY_PRINT);
    })->name('check-server-ip');
    
    // Route untuk test WhatsApp API configuration
    Route::get('/test-wa-config', function() {
        $config = [
            'whatsapp_api_url' => env('WHATSAPP_API_URL'),
            'whatsapp_api_key_set' => env('WHATSAPP_API_KEY') ? 'YES (Hidden)' : 'NO',
            'webhook_secret_set' => env('WHATSAPP_WEBHOOK_SECRET') ? 'YES (Hidden)' : 'NO',
            'curl_available' => function_exists('curl_init') ? 'YES' : 'NO',
            'scheduler_tasks' => [
                'WA_reminder_09:00' => 'kirim-wa-pagi',
                'WA_reminder_10:00' => 'kirim-wa-siang', 
                'auto_libur_every_5min' => 'auto-libur-monitor',
                'cleanup_06:00' => 'cleanup-anggota-sementara',
                'cleanup_tokens_daily' => 'cleanup-tokens'
            ]
        ];
        
        return response()->json($config, 200, [], JSON_PRETTY_PRINT);
    })->name('test-wa-config');
    
    // Route untuk test versi PHP dan cron
    Route::get('/test-php-version', function() {
        $info = [
            'web_php_version' => PHP_VERSION,
            'web_php_binary' => PHP_BINARY,
            'web_php_sapi' => php_sapi_name(),
            'laravel_version' => app()->version(),
            'current_time' => now()->format('Y-m-d H:i:s'),
            'timezone' => config('app.timezone'),
            'curl_available' => function_exists('curl_init'),
            'file_get_contents_available' => function_exists('file_get_contents'),
            'env_whatsapp_url' => env('WHATSAPP_API_URL') ? 'SET' : 'NOT SET',
            'env_whatsapp_key' => env('WHATSAPP_API_KEY') ? 'SET' : 'NOT SET'
        ];
        
        return response()->json($info, 200, [], JSON_PRETTY_PRINT);
    })->name('test-php-version');
});

// Testing routes
Route::get('/test-notifikasi/{userId}', function($userId) {
    $result = NotifikasiKehadiran::kirimNotifikasiWA($userId);
    return response()->json([
        'success' => $result,
        'message' => $result ? 'Notifikasi berhasil dikirim' : 'Gagal mengirim notifikasi'
    ]);
});
