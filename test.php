<?php
header('Content-Type: text/html; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h2>🔧 SGX Vendor Server Diagnostics</h2>";
echo "<b>PHP Version:</b> " . PHP_VERSION . "<br>";

if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    echo "<p style='color:red;'><b>❌ PERINGATAN:</b> Laravel 11 membutuhkan PHP minimal versi 8.2. Versi aktif Anda saat ini: " . PHP_VERSION . ". Silakan ubah versi PHP di cPanel MultiPHP Manager ke PHP 8.2 atau 8.3.</p>";
} else {
    echo "<p style='color:green;'><b>✅ Versi PHP Sesuai:</b> " . PHP_VERSION . "</p>";
}

// Check Extensions
$requiredExts = ['openssl', 'pdo', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'pdo_mysql', 'fileinfo'];
echo "<h3>📦 PHP Extensions Check:</h3><ul>";
foreach ($requiredExts as $ext) {
    if (extension_loaded($ext)) {
        echo "<li style='color:green;'>✅ Extension <b>{$ext}</b>: Loaded</li>";
    } else {
        echo "<li style='color:red;'>❌ Extension <b>{$ext}</b>: NOT LOADED</li>";
    }
}
echo "</ul>";

// Check .env
echo "<h3>📄 Environment (.env) Check:</h3>";
$envPath = __DIR__ . '/laravel-backend/.env';
if (file_exists($envPath)) {
    echo "<p style='color:green;'>✅ File <code>laravel-backend/.env</code> ditemukan (" . filesize($envPath) . " bytes).</p>";
    $envContent = file_get_contents($envPath);
    preg_match('/DB_DATABASE=(.*)/', $envContent, $dbMatch);
    preg_match('/DB_USERNAME=(.*)/', $envContent, $userMatch);
    preg_match('/DB_HOST=(.*)/', $envContent, $hostMatch);
    preg_match('/DB_PORT=(.*)/', $envContent, $portMatch);
    preg_match('/DB_PASSWORD=(.*)/', $envContent, $passMatch);

    $db = isset($dbMatch[1]) ? trim($dbMatch[1], "\"' \r\n") : '';
    $user = isset($userMatch[1]) ? trim($userMatch[1], "\"' \r\n") : '';
    $host = isset($hostMatch[1]) ? trim($hostMatch[1], "\"' \r\n") : '127.0.0.1';
    $port = isset($portMatch[1]) ? trim($portMatch[1], "\"' \r\n") : '3306';
    $pass = isset($passMatch[1]) ? trim($passMatch[1], "\"' \r\n") : '';

    echo "<b>Database Target:</b> {$db}<br>";
    echo "<b>User Database:</b> {$user}<br>";
    echo "<b>Host:</b> {$host}:{$port}<br>";

    // Test MySQL Connection
    echo "<h3>🗄️ MySQL Connection Test:</h3>";
    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        echo "<p style='color:green;'><b>✅ KONEKSI DATABASE BERHASIL!</b> Terhubung ke MySQL server.</p>";

        // Check tables count
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Jumlah Tabel Terdaftar: <b>" . count($tables) . " tabel</b></p>";
        if (count($tables) > 0) {
            echo "<ul>";
            foreach (array_slice($tables, 0, 10) as $t) {
                echo "<li>" . htmlspecialchars($t) . "</li>";
            }
            if (count($tables) > 10) echo "<li>... dan " . (count($tables) - 10) . " tabel lainnya.</li>";
            echo "</ul>";
        } else {
            echo "<p style='color:orange;'>⚠️ Database masih kosong. Perlu menjalankan <code>php artisan migrate --seed</code>.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red;'><b>❌ GAGAL KONEK DATABASE:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color:red;'>❌ File <code>laravel-backend/.env</code> TIDAK DITEMUKAN.</p>";
}

// Check storage permissions
echo "<h3>🔒 Storage Permissions:</h3>";
$storagePath = __DIR__ . '/laravel-backend/storage';
if (is_writable($storagePath)) {
    echo "<p style='color:green;'>✅ Folder storage dapat ditulisi (Writable).</p>";
} else {
    echo "<p style='color:red;'>❌ Folder storage TIDAK DAPAT DITULISI. Jalankan <code>chmod -R 777 laravel-backend/storage</code>.</p>";
}

echo "<hr><p><i>SGX Vendor Diagnostics Tool</i></p>";
