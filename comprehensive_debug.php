<?php
// COMPREHENSIVE DEBUG FOR PROCESSKONFIRAMASISMASSAL
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Comprehensive Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .warning { background-color: #fff3cd; color: #856404; }
        .info { background-color: #d1ecf1; color: #0c5460; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>🔍 Comprehensive Debug: Why WA Not Sent</h1>
    <p><strong>Debug Time:</strong> <?= date('Y-m-d H:i:s') ?></p>
    
    <?php
    use App\Http\Controllers\User\KehadiranController;
    use App\Models\User;
    use App\Models\Lapak;
    use Carbon\Carbon;
    
    try {
        $controller = new KehadiranController();
        
        echo '<div class="section">';
        echo '<h2>🔄 1. Detailed processKonfirmasiMassal() Debug</h2>';
        
        $tanggal = Carbon::today();
        $results = [];
        
        // Exactly same logic as processKonfirmasiMassal but with detailed output
        $lapaks = Lapak::with(['rombongs'])->get();
        
        echo '<div class="info status">Found ' . count($lapaks) . ' lapaks</div>';
        
        foreach ($lapaks as $lapak) {
            echo '<h3>🏢 Lapak: ' . $lapak->nama_lapak . ' (ID: ' . $lapak->lapak_id . ')</h3>';
            
            $rombongs = \App\Models\rombong::where('lapak_id', $lapak->lapak_id)
                ->orderBy('rombong_id', 'asc')
                ->get();
            
            echo '<div class="info status">Found ' . count($rombongs) . ' rombongs in this lapak</div>';
            
            if (count($rombongs) == 0) {
                echo '<div class="warning status">❌ No rombongs found for lapak_id: ' . $lapak->lapak_id . '</div>';
                continue;
            }
            
            echo '<table>';
            echo '<tr><th>Rombong ID</th><th>User</th><th>Status</th><th>Bisa Konfirmasi</th><th>Action</th></tr>';
            
            foreach ($rombongs as $index => $rombong) {
                echo '<tr>';
                echo '<td>' . $rombong->rombong_id . '</td>';
                
                if ($rombong->user_id) {
                    $user = User::find($rombong->user_id);
                    $statusInfo = $controller->getStatusKonfirmasi($rombong->user_id);
                    
                    echo '<td>' . ($user ? $user->name : 'User not found') . ' (ID: ' . $rombong->user_id . ')</td>';
                    echo '<td>' . $statusInfo['status'] . '</td>';
                    echo '<td>' . ($statusInfo['bisa_konfirmasi'] ? 'YES ✅' : 'NO ❌') . '</td>';
                    
                    // Check if WA should be sent
                    if ($statusInfo['bisa_konfirmasi'] && $statusInfo['status'] === 'konfirmasi_sekarang') {
                        echo '<td style="background-color: #d4edda;">SHOULD SEND WA ✅</td>';
                        
                        // Actually try to send WA
                        $result = $controller->kirimKonfirmasiWALink($rombong->user_id, $tanggal);
                        
                        $results[] = [
                            'user_id' => $rombong->user_id,
                            'user_name' => $user->name ?? 'Unknown',
                            'lapak' => $lapak->nama_lapak,
                            'urutan' => $index + 1,
                            'status_konfirmasi' => $statusInfo['status'],
                            'result' => $result
                        ];
                        
                        echo '</tr>';
                        echo '<tr><td colspan="5" style="background-color: #f8f9fa;"><strong>WA Result:</strong> ' . json_encode($result, JSON_UNESCAPED_UNICODE) . '</td></tr>';
                    } else {
                        echo '<td style="background-color: #fff3cd;">Skip: ' . $statusInfo['pesan'] . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<td>No user assigned</td>';
                    echo '<td>-</td>';
                    echo '<td>-</td>';
                    echo '<td>Skip: No user</td>';
                    echo '</tr>';
                }
            }
            echo '</table><br>';
        }
        
        echo '<div class="' . (count($results) > 0 ? 'success' : 'error') . ' status">';
        echo '<h3>📊 Final Results:</h3>';
        echo '<strong>Total WA Sent:</strong> ' . count($results) . '<br>';
        echo '</div>';
        
        if (count($results) > 0) {
            echo '<h4>📱 WA Send Details:</h4>';
            echo '<table>';
            echo '<tr><th>User</th><th>Lapak</th><th>WA Result</th></tr>';
            foreach ($results as $result) {
                echo '<tr>';
                echo '<td>' . $result['user_name'] . '</td>';
                echo '<td>' . $result['lapak'] . '</td>';
                echo '<td>' . ($result['result']['success'] ?? false ? '✅ Success' : '❌ Failed: ' . ($result['result']['message'] ?? 'Unknown error')) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<div class="error status">';
            echo '<h4>❌ No WA sent! Investigating...</h4>';
            echo '</div>';
            
            // Cross-check with direct user query
            echo '<h4>🔄 Cross-check: Direct User Query</h4>';
            $eligible_users_direct = User::whereHas('rombong')->get()->filter(function($user) use ($controller) {
                $status = $controller->getStatusKonfirmasi($user->user_id);
                return $status['bisa_konfirmasi'] && $status['status'] === 'konfirmasi_sekarang';
            });
            
            echo '<div class="info status">Direct query found ' . count($eligible_users_direct) . ' eligible users:</div>';
            
            if (count($eligible_users_direct) > 0) {
                echo '<table>';
                echo '<tr><th>User</th><th>User ID</th><th>Rombong ID</th><th>Lapak ID</th><th>Test WA</th></tr>';
                foreach ($eligible_users_direct as $user) {
                    $rombong = $user->rombong;
                    echo '<tr>';
                    echo '<td>' . $user->name . '</td>';
                    echo '<td>' . $user->user_id . '</td>';
                    echo '<td>' . ($rombong ? $rombong->rombong_id : 'No rombong') . '</td>';
                    echo '<td>' . ($rombong ? $rombong->lapak_id : 'No lapak') . '</td>';
                    
                    // Test WA to this user
                    if ($rombong) {
                        $wa_result = $controller->kirimKonfirmasiWALink($user->user_id, $tanggal);
                        echo '<td>' . ($wa_result['success'] ?? false ? '✅ Success' : '❌ Failed: ' . ($wa_result['message'] ?? 'Unknown')) . '</td>';
                    } else {
                        echo '<td>❌ No rombong</td>';
                    }
                    echo '</tr>';
                }
                echo '</table>';
            }
        }
        
    } catch (Exception $e) {
        echo '<div class="error status">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
    ?>

</body>
</html>