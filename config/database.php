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

// =========================================================================
// Helper Functions: Error Logging & Database / Table Diagnostics
// =========================================================================

/**
 * Mencatat error database atau tabel ke file log terstruktur (JSON line)
 *
 * @param string $type Jenis error ('CONNECTION_ERROR', 'TABLE_ERROR', 'QUERY_ERROR', dll)
 * @param string $message Pesan error
 * @param int $code Kode status error MySQL
 * @param array $details Informasi konteks tambahan
 * @return bool
 */
function log_database_error($type, $message, $code = 0, $details = []) {
    $log_dir = dirname(__DIR__) . '/logs';
    if (!file_exists($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
    $log_file = $log_dir . '/database_error.log';

    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
    $caller = isset($backtrace[0]) ? $backtrace[0] : [];
    $file = $details['file'] ?? ($caller['file'] ?? ($_SERVER['SCRIPT_FILENAME'] ?? 'cli'));
    $line = $details['line'] ?? ($caller['line'] ?? 0);

    $log_entry = [
        'id'        => uniqid('dberr_', true),
        'timestamp' => date('Y-m-d H:i:s'),
        'type'      => $type,
        'code'      => (int)$code,
        'message'   => $message,
        'file'      => $file,
        'line'      => $line,
        'url'       => $_SERVER['REQUEST_URI'] ?? 'CLI',
        'ip'        => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        'details'   => $details
    ];

    $json_line = json_encode($log_entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    return @file_put_contents($log_file, $json_line, FILE_APPEND | LOCK_EX) !== false;
}

/**
 * Mengambil riwayat error log database
 *
 * @param int $limit Batas maksimal log yang diambil
 * @param string|null $filter_type Filter jenis error
 * @param string|null $search Kata kunci pencarian
 * @return array
 */
function get_database_error_logs($limit = 100, $filter_type = null, $search = null) {
    $log_file = dirname(__DIR__) . '/logs/database_error.log';
    if (!file_exists($log_file)) {
        return [];
    }

    $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) {
        return [];
    }

    $lines = array_reverse($lines);
    $logs = [];
    $count = 0;

    foreach ($lines as $line) {
        $data = json_decode($line, true);
        if (!$data) continue;

        if ($filter_type && (!isset($data['type']) || $data['type'] !== $filter_type)) {
            continue;
        }

        if ($search) {
            $haystack = strtolower(json_encode($data));
            if (strpos($haystack, strtolower($search)) === false) {
                continue;
            }
        }

        $logs[] = $data;
        $count++;
        if ($count >= $limit) {
            break;
        }
    }

    return $logs;
}

/**
 * Membersihkan semua data dalam file log error
 *
 * @return bool
 */
function clear_database_error_logs() {
    $log_file = dirname(__DIR__) . '/logs/database_error.log';
    if (file_exists($log_file)) {
        return @file_put_contents($log_file, '') !== false;
    }
    return true;
}

/**
 * Melakukan pengecekan kesehatan dan integritas tabel-tabel utama
 *
 * @param mysqli|false $db_conn Objek koneksi MySQL
 * @return array
 */
function check_table_health($db_conn) {
    if (!$db_conn) return [];

    $expected_tables = [
        'users' => [
            'description' => 'Akun Pengguna, Autentikasi & Role (Admin / Pelanggan)',
            'columns'     => ['id', 'username', 'password', 'nama_lengkap', 'role']
        ],
        'menu' => [
            'description' => 'Katalog Menu Makanan, Minuman, Paket, & Harga',
            'columns'     => ['id', 'nama_menu', 'harga', 'kategori', 'foto', 'status']
        ],
        'pesanan' => [
            'description' => 'Transaksi Pemesanan, Status Bayar & Pengiriman',
            'columns'     => ['id', 'kode_pesanan', 'nama_pemesan', 'no_telepon', 'total_harga', 'status_pembayaran', 'status_pesanan']
        ],
        'detail_pesanan' => [
            'description' => 'Rincian Item Menu yang Dipesan per Transaksi',
            'columns'     => ['id', 'pesanan_id', 'menu_id', 'jumlah', 'subtotal']
        ]
    ];

    $results = [];

    foreach ($expected_tables as $table_name => $meta) {
        $check_exists = @mysqli_query($db_conn, "SHOW TABLES LIKE '{$table_name}'");
        $exists = ($check_exists && mysqli_num_rows($check_exists) > 0);

        if (!$exists) {
            $results[$table_name] = [
                'name'            => $table_name,
                'description'     => $meta['description'],
                'exists'          => false,
                'status'          => 'missing',
                'rows'            => 0,
                'engine'          => '-',
                'collation'       => '-',
                'missing_columns' => $meta['columns'],
                'check_result'    => 'TABLE NOT FOUND'
            ];
            continue;
        }

        $rows = 0;
        $q_rows = @mysqli_query($db_conn, "SELECT COUNT(*) as total FROM `{$table_name}`");
        if ($q_rows && ($r = mysqli_fetch_assoc($q_rows))) {
            $rows = (int)$r['total'];
        }

        $engine = 'InnoDB';
        $collation = 'utf8mb4_unicode_ci';
        $q_status = @mysqli_query($db_conn, "SHOW TABLE STATUS LIKE '{$table_name}'");
        if ($q_status && ($st = mysqli_fetch_assoc($q_status))) {
            $engine = $st['Engine'] ?? 'InnoDB';
            $collation = $st['Collation'] ?? 'utf8mb4';
        }

        $existing_cols = [];
        $q_cols = @mysqli_query($db_conn, "SHOW COLUMNS FROM `{$table_name}`");
        if ($q_cols) {
            while ($c = mysqli_fetch_assoc($q_cols)) {
                $existing_cols[] = $c['Field'];
            }
        }
        $missing_cols = array_diff($meta['columns'], $existing_cols);

        $check_result = 'OK';
        $q_check = @mysqli_query($db_conn, "CHECK TABLE `{$table_name}`");
        if ($q_check && ($chk = mysqli_fetch_assoc($q_check))) {
            $check_result = $chk['Msg_text'] ?? 'OK';
        }

        $status = 'ok';
        if (!empty($missing_cols) || $check_result !== 'OK') {
            $status = 'warning';
        }

        $results[$table_name] = [
            'name'            => $table_name,
            'description'     => $meta['description'],
            'exists'          => true,
            'status'          => $status,
            'rows'            => $rows,
            'engine'          => $engine,
            'collation'       => $collation,
            'missing_columns' => $missing_cols,
            'check_result'    => $check_result
        ];
    }

    return $results;
}

// 3. Eksekusi Koneksi ke Database
// Nonaktifkan pelaporan exception otomatis mysqli di PHP 8.1+ agar dapat ditangani secara elegan
mysqli_report(MYSQLI_REPORT_OFF);

$conn = null;
$err_code = 0;
$err_msg = '';

try {
    $conn = @mysqli_connect($host, $db_username, $db_password, $database, $port);

    // Fallback untuk Laragon jika diakses via "localhost" vs "127.0.0.1"
    if (!$conn && !$is_docker) {
        $alt_host = ($host === '127.0.0.1') ? 'localhost' : '127.0.0.1';
        $conn = @mysqli_connect($alt_host, $db_username, $db_password, $database, $port);
        if ($conn) {
            $host = $alt_host;
        }
    }

    // Jika error 1049 (Unknown database), coba auto-create HANYA jika database adalah default 'db_pemesanan'
    if (!$conn) {
        $err_code = mysqli_connect_errno();
        $err_msg  = mysqli_connect_error();

        if ($err_code === 1049 && !$is_docker && $database === 'db_pemesanan') {
            $root_conn = @mysqli_connect($host, $db_username, $db_password, "", $port);
            if ($root_conn) {
                @mysqli_query($root_conn, "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                @mysqli_close($root_conn);
                // Coba koneksi ulang
                $conn = @mysqli_connect($host, $db_username, $db_password, $database, $port);
            }
        }
    }
} catch (Throwable $e) {
    $err_code = $e->getCode();
    $err_msg  = $e->getMessage();
}

// Penanganan jika koneksi database tetap gagal
$is_db_error_page = (defined('IN_DB_ERROR_PAGE') && constant('IN_DB_ERROR_PAGE') === true)
                 || (isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === 'db_error.php')
                 || (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) === 'db_error.php');

if (!$conn) {
    if ($err_code === 0) {
        $err_code = mysqli_connect_errno() ?: 2002;
    }
    if (empty($err_msg)) {
        $err_msg = mysqli_connect_error() ?: "Tidak dapat terhubung ke server MySQL pada {$host}:{$port}";
    }

    $db_connected = false;
    $db_error_info = [
        'code'      => $err_code,
        'message'   => $err_msg,
        'host'      => $host,
        'port'      => $port,
        'username'  => $db_username,
        'database'  => $database,
        'is_docker' => $is_docker
    ];

    // Catat insiden kegagalan koneksi ke file log
    log_database_error('CONNECTION_ERROR', "Gagal terhubung ke MySQL [{$err_code}]: {$err_msg}", $err_code, [
        'host'      => $host,
        'port'      => $port,
        'username'  => $db_username,
        'database'  => $database,
        'is_docker' => $is_docker
    ]);

    // Jika script dipanggil BUKAN dari db_error.php, tampilkan halaman diagnosa terpadu
    if (!$is_db_error_page) {
        $error_page = dirname(__DIR__) . '/db_error.php';
        if (file_exists($error_page)) {
            require_once $error_page;
            exit;
        } else {
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
    }
} else {
    $db_connected = true;
    $db_error_info = null;
    // 4. Set Charset ke UTF-8
    mysqli_set_charset($conn, "utf8mb4");

    // 4.1 Validasi Keberadaan Tabel Utama Aplikasi (users & menu)
    // Jika terhubung ke database yang salah / tidak memiliki tabel aplikasi, alihkan ke db_error.php
    $core_required_tables = ['menu', 'users'];
    $missing_core_tables = [];

    foreach ($core_required_tables as $req_tbl) {
        $check_tbl = @mysqli_query($conn, "SHOW TABLES LIKE '{$req_tbl}'");
        if (!$check_tbl || mysqli_num_rows($check_tbl) === 0) {
            $missing_core_tables[] = $req_tbl;
        }
    }

    if (!empty($missing_core_tables)) {
        $db_table_error = true;
        $missing_str = implode(', ', $missing_core_tables);
        $tbl_err_msg = "Database '{$database}' terhubung, namun tabel utama aplikasi ({$missing_str}) tidak ditemukan! Kemungkinan salah memilih nama database atau tabel belum diimpor.";

        // Catat error tabel ke log
        log_database_error('TABLE_ERROR', $tbl_err_msg, 1146, [
            'database'       => $database,
            'host'           => $host,
            'missing_tables' => $missing_core_tables
        ]);

        // Jika halaman yang dibuka BUKAN db_error.php, tampilkan db_error.php
        if (!$is_db_error_page) {
            $error_page = dirname(__DIR__) . '/db_error.php';
            if (file_exists($error_page)) {
                require_once $error_page;
                exit;
            }
        }
    }
}

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

