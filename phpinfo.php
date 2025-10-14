<?php
// Simple PHP Info untuk mengetahui path
echo "<h1>🐘 PHP Information</h1>";
echo "<strong>PHP Version:</strong> " . PHP_VERSION . "<br>";
echo "<strong>PHP Binary Path:</strong> " . PHP_BINARY . "<br>";
echo "<strong>PHP Executable:</strong> " . php_sapi_name() . "<br>";
echo "<strong>Current Working Directory:</strong> " . getcwd() . "<br>";
echo "<strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// Coba deteksi common PHP paths
$commonPaths = [
    '/usr/local/bin/php',
    '/usr/bin/php',
    '/usr/local/bin/php82',
    '/usr/local/bin/php81',
    '/usr/local/bin/php80',
    '/opt/cpanel/ea-php82/root/usr/bin/php',
    '/opt/cpanel/ea-php81/root/usr/bin/php',
    '/opt/alt/php82/usr/bin/php'
];

echo "<br><h2>🔍 Available PHP Paths:</h2>";
foreach ($commonPaths as $path) {
    if (file_exists($path)) {
        echo "<span style='color: green;'>✅ $path</span><br>";
    } else {
        echo "<span style='color: red;'>❌ $path</span><br>";
    }
}

// Show current path info
echo "<br><h2>📋 Path Information:</h2>";
echo "<strong>PATH Environment:</strong> " . getenv('PATH') . "<br>";

// Recommended cron command
echo "<br><h2>⏰ Recommended Cron Command:</h2>";
echo "<code>" . PHP_BINARY . " " . __DIR__ . "/artisan schedule:run</code>";
?>