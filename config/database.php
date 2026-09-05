<?php
/**
 * Konfigurasi Database Ayam Penyet Al-Barokah
 * Kompatibel secara otomatis untuk:
 * 1. Lingkungan Docker / VPS Production
 * 2. Lingkungan Local Development (Laragon / XAMPP / Native PHP)
 */

// 1. Baca file .env jika tersedia di root project
$env_file = dirname(__DIR__) . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        list($env_key, $env_val) = explode('=', $line, 2);
        $env_key = trim($env_key);
        $env_val = trim($env_val, " \t\n\r\0\x0B\"'");
        if (!getenv($env_key) && !isset($_ENV[$env_key])) {
            putenv("{$env_key}={$env_val}");
            $_ENV[$env_key] = $env_val;
        }
    }
}

// 2. Deteksi Lingkungan (Docker VPS vs Local Development / Laragon)
$is_docker = file_exists('/.dockerenv') || getenv('DB_HOST') === 'mysql_shared';

if ($is_docker) {
    // Pengaturan Docker VPS
    $host        = getenv('DB_HOST') ?: "mysql_shared";
    $db_username = getenv('DB_USERNAME') ?: "albarokah_user";
    $db_password = getenv('DB_PASSWORD') ?: "AlBarokahSecret2026!";
    $database    = getenv('DB_DATABASE') ?: "db_pemesanan";
    $port        = (int)(getenv('DB_PORT') ?: 3306);
} else {
    // Pengaturan Local Development (Default Laragon: 127.0.0.1, user: root, password: "")
    $host        = getenv('DB_HOST') ?: "127.0.0.1";
    $db_username = getenv('DB_USERNAME') ?: "root";
    $db_password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : "";
    $database    = getenv('DB_DATABASE') ?: "db_pemesanan";
    $port        = (int)(getenv('DB_PORT') ?: 3306);
}

// 3. Eksekusi Koneksi ke Database
$conn = @mysqli_connect($host, $db_username, $db_password, $database, $port);

// Fallback untuk Laragon jika diakses via "localhost" vs "127.0.0.1"
if (!$conn && !$is_docker) {
    $alt_host = ($host === '127.0.0.1') ? 'localhost' : '127.0.0.1';
    $conn = @mysqli_connect($alt_host, $db_username, $db_password, $database, $port);
    if ($conn) {
        $host = $alt_host;
    }
}

// Jika koneksi gagal, periksa apakah database belum dibuat di Laragon
if (!$conn) {
    $err_code = mysqli_connect_errno();
    $err_msg  = mysqli_connect_error();

    // Jika error 1049 (Unknown database), coba auto-create jika memiliki hak akses root
    if ($err_code === 1049 && !$is_docker) {
        $root_conn = @mysqli_connect($host, $db_username, $db_password, "", $port);
        if ($root_conn) {
            @mysqli_query($root_conn, "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            @mysqli_close($root_conn);
            // Coba koneksi ulang
            $conn = @mysqli_connect($host, $db_username, $db_password, $database, $port);
        }
    }
}

// Tampilkan instruksi ramah jika koneksi tetap gagal
if (!$conn) {
    $err_code = mysqli_connect_errno();
    $err_msg  = mysqli_connect_error();

    $guidance = $is_docker
        ? "Pastikan container <code>mysql_shared</code> berjalan di Docker."
        : "Pastikan MySQL di <strong>Laragon</strong> sudah di-start (klik <em>Start All</em>), dan database <code>{$database}</code> sudah diimport dari <code>database/db_pemesanan.sql</code>.";

    die("
    <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; max-width: 650px; margin: 60px auto; padding: 30px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); color: #1e293b;'>
        <h3 style='color: #e11d48; margin-top: 0; font-size: 1.4rem;'>⚠️ Koneksi ke Database Gagal</h3>
        <p style='color: #475569;'>Aplikasi tidak dapat terhubung ke server MySQL.</p>
        <div style='background: #f8fafc; padding: 14px 18px; border-radius: 8px; font-family: monospace; font-size: 0.9rem; margin: 16px 0; border: 1px solid #cbd5e1;'>
            <strong>Error [{$err_code}]:</strong> {$err_msg}<br>
            <strong>Host:</strong> {$host}:{$port}<br>
            <strong>User:</strong> {$db_username}<br>
            <strong>Database:</strong> {$database}
        </div>
        <p style='color: #334155; font-size: 0.95rem;'>💡 <strong>Solusi:</strong> {$guidance}</p>
    </div>
    ");
}

// 4. Set Charset ke UTF-8
mysqli_set_charset($conn, "utf8mb4");

// 5. Helper Function untuk Aset Gambar Menu
$dst_dir = __DIR__ . '/../assets/images/';
if (is_dir($dst_dir)) {
    if (!file_exists($dst_dir . 'kerupuk_udang.jpg') && file_exists($dst_dir . 'tahu_tempe_penyet.jpg')) {
        @copy($dst_dir . 'tahu_tempe_penyet.jpg', $dst_dir . 'kerupuk_udang.jpg');
    }
}
