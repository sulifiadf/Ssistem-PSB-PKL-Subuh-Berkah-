<?php
/**
 * Final Test - Scheduler Working Check
 * Akses: http://domain-anda.com/final_test.php
 */

echo "<h1>🎉 Final Test - Scheduler Working Check</h1>";

// 1. Clear caches after fix
echo "<h2>1. Clear Caches After Bootstrap Fix</h2>";
echo "<pre>";
$output = shell_exec("/usr/local/bin/php artisan config:clear 2>&1");
echo htmlspecialchars($output);
echo "</pre>";

// 2. Test schedule list
echo "<h2>2. Schedule List Test</h2>";
echo "<pre>";
$output = shell_exec('/usr/local/bin/php artisan schedule:list 2>&1');
echo htmlspecialchars($output);
echo "</pre>";

// 3. Test schedule run
echo "<h2>3. Schedule Run Test</h2>";
echo "<pre>";
$output = shell_exec('/usr/local/bin/php artisan schedule:run --verbose 2>&1');
echo htmlspecialchars($output);
echo "</pre>";

// 4. Check current logs
echo "<h2>4. Current Log Status</h2>";

// Simple scheduler log
$simpleLog = __DIR__ . '/simple_scheduler_test.log';
if (file_exists($simpleLog)) {
    echo "<h3>✅ Simple Scheduler Log:</h3>";
    echo "<pre>" . htmlspecialchars(file_get_contents($simpleLog)) . "</pre>";
}

// Cron log
$cronLog = __DIR__ . '/cron.log';
if (file_exists($cronLog)) {
    echo "<h3>📋 Cron Log (last 10 lines):</h3>";
    echo "<pre>";
    $file = file($cronLog);
    $lastLines = array_slice($file, -10);
    echo htmlspecialchars(implode('', $lastLines));
    echo "</pre>";
}

// Laravel log
$laravelLog = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($laravelLog)) {
    echo "<h3>📱 Laravel Log (last 10 lines):</h3>";
    echo "<pre>";
    $file = file($laravelLog);
    $lastLines = array_slice($file, -10);
    echo htmlspecialchars(implode('', $lastLines));
    echo "</pre>";
}

// 5. Status summary
echo "<h2>5. 🎯 Status Summary</h2>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";

if (file_exists($simpleLog)) {
    echo "✅ <strong>Scheduler AKTIF</strong> - Simple test log sudah terbuat<br>";
}

if (file_exists($cronLog)) {
    echo "✅ <strong>Cron Job AKTIF</strong> - Cron log sudah terbuat<br>";
}

echo "<br><strong>Next Steps:</strong><br>";
echo "• Scheduler sudah berjalan, tunggu jam 09:00, 10:00, 11:00 besok untuk WhatsApp reminder<br>";
echo "• Monitor file cron.log dan laravel.log untuk aktivitas<br>";
echo "• Setiap 5 menit auto-libur process akan berjalan<br>";

echo "</div>";

echo "<h2>6. 📱 WhatsApp Reminder Schedule</h2>";
echo "<ul>";
echo "<li><strong>09:00</strong> - WA Reminder Pagi</li>";
echo "<li><strong>10:00</strong> - WA Reminder Siang</li>";
echo "<li><strong>11:00</strong> - WA Reminder Terakhir</li>";
echo "<li><strong>Setiap 5 menit</strong> - Auto-libur process</li>";
echo "</ul>";
?>