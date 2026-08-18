<?php
header('Content-Type: text/html; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<div style='font-family: sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);'>";
echo "<h2 style='color: #1e3a8a;'>🔧 SGX Vendor System & Database Diagnostics</h2>";
echo "<b>PHP Version:</b> " . PHP_VERSION . "<br>";

if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    echo "<p style='color:red;'><b>❌ PERINGATAN:</b> Laravel 11 membutuhkan PHP minimal versi 8.2. Versi aktif Anda saat ini: " . PHP_VERSION . ". Silakan ubah versi PHP di cPanel MultiPHP Manager ke PHP 8.2 atau 8.3.</p>";
} else {
    echo "<p style='color:green;'><b>✅ Versi PHP Sesuai:</b> " . PHP_VERSION . "</p>";
}

// Check .env
echo "<h3>📄 Environment (.env) Check:</h3>";
$envPath = file_exists(__DIR__ . '/laravel-backend/.env') ? __DIR__ . '/laravel-backend/.env' : __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "<p style='color:green;'>✅ File <code>{$envPath}</code> ditemukan (" . filesize($envPath) . " bytes).</p>";
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
            echo "<p style='color:green;'><b>✅ Database sudah terisi tabel:</b></p><ul>";
            foreach (array_slice($tables, 0, 8) as $t) {
                echo "<li>" . htmlspecialchars($t) . "</li>";
            }
            if (count($tables) > 8) echo "<li>... dan " . (count($tables) - 8) . " tabel lainnya.</li>";
            echo "</ul>";

            // Check users
            $userStmt = $pdo->query("SELECT id, name, email FROM users");
            $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<p>Jumlah Akun Pengguna: <b>" . count($users) . " akun</b></p><ul>";
            foreach ($users as $u) {
                echo "<li><b>" . htmlspecialchars($u['name']) . "</b> (" . htmlspecialchars($u['email']) . ")</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color:orange;'>⚠️ Database masih kosong. Klik tombol di bawah untuk menjalankan migrasi otomatis via browser:</p>";
        }

        // Action: Auto Migration Button
        if (isset($_GET['action']) && $_GET['action'] === 'migrate') {
            echo "<hr><h3>🚀 Menjalankan Migrasi & Seeding...</h3>";
            require __DIR__ . '/laravel-backend/vendor/autoload.php';
            $app = require_once __DIR__ . '/laravel-backend/bootstrap/app.php';
            $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
            $kernel->bootstrap();

            \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
            $output = \Illuminate\Support\Facades\Artisan::output();
            echo "<pre style='background:#1e293b; color:#10b981; padding:15px; border-radius:8px; overflow-x:auto;'>" . htmlspecialchars($output) . "</pre>";
            echo "<p style='color:green; font-weight:bold;'>🎉 MIGRASI DAN PENGISIAN AKUN SELESAI! Silakan coba login sekarang.</p>";
        } elseif (isset($_GET['action']) && $_GET['action'] === 'clearcache') {
            echo "<hr><h3>🧹 Membersihkan Cache Rute & Aplikasi...</h3>";
            require __DIR__ . '/laravel-backend/vendor/autoload.php';
            $app = require_once __DIR__ . '/laravel-backend/bootstrap/app.php';
            $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
            $kernel->bootstrap();

            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            $output = \Illuminate\Support\Facades\Artisan::output();

            foreach (glob(__DIR__ . '/laravel-backend/bootstrap/cache/*.php') as $f) {
                @unlink($f);
            }

            echo "<pre style='background:#1e293b; color:#10b981; padding:15px; border-radius:8px; overflow-x:auto;'>" . htmlspecialchars($output) . "</pre>";
            echo "<p style='color:green; font-weight:bold;'>✅ SELURUH CACHE RUTE & CONFIG TELAH DIBERSIHKAN TOTAL!</p>";
        } elseif (isset($_GET['action']) && $_GET['action'] === 'repair_storage') {
            echo "<hr><h3>🛠️ Memperbaiki Folder Penyimpanan & Izin Foto...</h3>";

            // 1. Ensure Directories exist with 0777 permissions
            $dirs = [
                __DIR__ . '/laravel-backend/storage/app/public/uploads',
                __DIR__ . '/laravel-backend/storage/app/public',
                __DIR__ . '/laravel-backend/storage/app',
                __DIR__ . '/laravel-backend/storage/framework/cache',
                __DIR__ . '/laravel-backend/storage/framework/sessions',
                __DIR__ . '/laravel-backend/storage/framework/views',
                __DIR__ . '/laravel-backend/public/storage',
                __DIR__ . '/storage/uploads',
                __DIR__ . '/storage',
            ];

            foreach ($dirs as $d) {
                if (!is_dir($d)) {
                    @mkdir($d, 0777, true);
                }
                @chmod($d, 0777);
            }

            // 2. Try creating symlinks
            @symlink(__DIR__ . '/laravel-backend/storage/app/public', __DIR__ . '/laravel-backend/public/storage');
            @symlink(__DIR__ . '/laravel-backend/storage/app/public', __DIR__ . '/storage');

            // 3. Inspect Uploaded Photos
            $photosStmt = $pdo->query("SELECT id, spk_number, file_name, file_path, stage FROM evidence_photos LEFT JOIN work_orders ON evidence_photos.work_order_id = work_orders.id ORDER BY evidence_photos.id DESC");
            $uploadedPhotos = $photosStmt->fetchAll(PDO::FETCH_ASSOC);

            echo "<p style='color:green; font-weight:bold;'>✅ FOLDER PENYIMPANAN BERHASIL DISIAPKAN DENGAN IZIN AKSES PENUH (0777)!</p>";
            echo "<p>Daftar Foto Bukti di Database (" . count($uploadedPhotos) . " foto):</p>";
            echo "<table border='1' cellpadding='8' style='border-collapse:collapse; width:100%; font-size:12px;'>";
            echo "<tr style='background:#f1f5f9;'><th>ID</th><th>SPK</th><th>Tahap</th><th>File Name</th><th>Path Tersimpan</th><th>File Fisik Ditemukan?</th><th>Aksi Uji</th></tr>";

            foreach ($uploadedPhotos as $p) {
                $clean = ltrim(str_replace('/storage/', '', $p['file_path']), '/');
                $candidate = __DIR__ . '/laravel-backend/storage/app/public/' . $clean;
                $exists = file_exists($candidate);

                echo "<tr>";
                echo "<td>" . $p['id'] . "</td>";
                echo "<td>" . htmlspecialchars($p['spk_number'] ?? '-') . "</td>";
                echo "<td><b>" . htmlspecialchars($p['stage']) . "</b></td>";
                echo "<td>" . htmlspecialchars($p['file_name']) . "</td>";
                echo "<td><code>" . htmlspecialchars($p['file_path']) . "</code></td>";
                echo "<td>" . ($exists ? "<span style='color:green; font-weight:bold;'>✅ ADA (" . filesize($candidate) . " bytes)</span>" : "<span style='color:red;'>❌ TIDAK ADA DI DISK</span>") . "</td>";
                echo "<td><a href='/api/storage-stream/{$clean}' target='_blank' style='color:#4f46e5; font-weight:bold;'>🔗 Buka Stream Gambar</a></td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div style='margin-top: 20px; display:flex; gap:10px; flex-wrap:wrap;'>";
            echo "<a href='?action=repair_storage' style='display:inline-block; padding: 12px 20px; background: #7c3aed; color: #fff; text-decoration: none; border-radius: 8px; font-weight: bold;'>🛠️ 1-Klik Perbaiki Folder & Izin Foto Storage</a>";
            echo "<a href='?action=clearcache' style='display:inline-block; padding: 12px 20px; background: #059669; color: #fff; text-decoration: none; border-radius: 8px; font-weight: bold;'>🧹 1-Klik Bersihkan Cache Rute & Server</a>";
            echo "<a href='?action=migrate' style='display:inline-block; padding: 12px 20px; background: #4f46e5; color: #fff; text-decoration: none; border-radius: 8px; font-weight: bold;'>⚡ Migrasi Ulang Database</a>";
            echo "</div>";
        }

    } catch (Exception $e) {
        echo "<p style='color:red;'><b>❌ GAGAL KONEK DATABASE:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color:red;'>❌ File <code>laravel-backend/.env</code> TIDAK DITEMUKAN.</p>";
}

echo "<hr><p><a href='/' style='color:#4f46e5; font-weight:bold;'>← Kembali ke Halaman Login SGX Vendor</a></p>";
echo "</div>";
