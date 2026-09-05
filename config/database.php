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

// 5. Helper Function & Auto-Sync Gambar Menu
/**
 * Helper function untuk mendapatkan URL/path gambar menu dengan fallback aman & cache-busting.
 *
 * @param string|null $foto Nama file foto di database
 * @param string $prefix Path relatif menuju folder assets/images/ (misal: 'assets/images/' atau '../assets/images/')
 * @return string Path lengkap gambar dengan query string versi (?v=timestamp)
 */
function get_menu_image_src($foto, $prefix = 'assets/images/') {
    static $legacy_map = [
        'ayam_penyet_biasa.jpg'     => 'menu_1788590605.jpg',
        'ayam_penyet_jumbo.jpg'     => 'menu_1788590594.jpg',
        'bebek_penyet.jpg'          => 'default-menu.jpg',
        'tahu_tempe_penyet.jpg'     => 'menu_1788590520.jpg',
        'ayam_bakar.jpg'            => 'menu_1788590474.jpg',
        'lele_penyet.jpg'           => 'menu_1788590458.jpg',
        'ayam_geprek_mozarella.jpg' => 'default-menu.jpg',
        'nasi_goreng.jpg'           => 'menu_1788590363.jpg',
        'paket_ekonomis.jpg'        => 'default-menu.jpg',
        'paket_bebek.jpg'           => 'default-menu.jpg',
        'paket_jumbo.jpg'           => 'default-menu.jpg',
        'es_teh.jpg'                => 'menu_1788590707.jpg',
        'es_jeruk.jpg'              => 'menu_1788590694.jpg',
        'es_campur.jpg'             => 'menu_1788590676.jpg',
        'es_kelapa.jpg'             => 'menu_1788590652.jpg',
        'jus_alpukat.jpg'           => 'menu_1788590632.jpg',
        'es_lemon_tea.jpg'          => 'menu_1788590618.jpg',
        'kerupuk_udang.jpg'         => 'menu_1788590940.jpg',
        'pisang_goreng.jpg'         => 'menu_1788590844.jpg',
        'tahu_crispy.jpg'           => 'menu_1788590813.jpg',
    ];

    $filename = trim((string)$foto);
    if (!empty($filename) && isset($legacy_map[$filename])) {
        $filename = $legacy_map[$filename];
    }

    $base_dir = dirname(__DIR__) . '/assets/images/';
    if (empty($filename) || !file_exists($base_dir . $filename)) {
        $filename = 'default-menu.jpg';
    }

    $file_path = $base_dir . $filename;
    $v = file_exists($file_path) ? filemtime($file_path) : 1;

    return rtrim($prefix, '/') . '/' . $filename . '?v=' . $v;
}

// 6. Auto-Sync Self-Healing Foto Menu
// Jika di database lokal atau VPS masih ada nama foto legacy, lakukan sinkronisasi otomatis satu kali.
if ($conn) {
    $check_legacy = @mysqli_query($conn, "SELECT COUNT(*) as total FROM menu WHERE foto = 'ayam_penyet_biasa.jpg' OR foto = 'nasi_goreng.jpg'");
    if ($check_legacy && ($row_legacy = mysqli_fetch_assoc($check_legacy)) && (int)$row_legacy['total'] > 0) {
        @mysqli_query($conn, "DELETE FROM menu WHERE id > 20");
        @mysqli_query($conn, "ALTER TABLE menu AUTO_INCREMENT = 21");
        
        $sync_updates = [
            1  => 'menu_1788590605.jpg',
            2  => 'menu_1788590594.jpg',
            3  => 'default-menu.jpg',
            4  => 'menu_1788590520.jpg',
            5  => 'menu_1788590474.jpg',
            6  => 'menu_1788590458.jpg',
            7  => 'default-menu.jpg',
            8  => 'menu_1788590363.jpg',
            9  => 'default-menu.jpg',
            10 => 'default-menu.jpg',
            11 => 'default-menu.jpg',
            12 => 'menu_1788590707.jpg',
            13 => 'menu_1788590694.jpg',
            14 => 'menu_1788590676.jpg',
            15 => 'menu_1788590652.jpg',
            16 => 'menu_1788590632.jpg',
            17 => 'menu_1788590618.jpg',
            18 => 'menu_1788590940.jpg',
            19 => 'menu_1788590844.jpg',
            20 => 'menu_1788590813.jpg',
        ];
        foreach ($sync_updates as $m_id => $m_foto) {
            @mysqli_query($conn, "UPDATE menu SET foto = '$m_foto' WHERE id = $m_id");
        }
    }
}

