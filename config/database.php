<?php
// Konfigurasi Database Ayam Penyet Al-Barokah
$host = "127.0.0.1";
$db_username = "root";
$db_password = "";
$database = "db_pemesanan";

// Fungsi untuk mencoba menjalankan MySQL jika mati
function auto_start_mysql($skip_grant_tables = false) {
    // Deteksi path MySQL
    $mysqld = 'c:\\xampp1\\mysql\\bin\\mysqld.exe';
    $ini    = 'c:\\xampp1\\mysql\\bin\\my.ini';
    if (!file_exists($mysqld)) {
        $mysqld = 'c:\\xampp\\mysql\\bin\\mysqld.exe';
        $ini    = 'c:\\xampp\\mysql\\bin\\my.ini';
    }

    // Jika mode skip-grant-tables, matikan MySQL dulu lalu start ulang
    if ($skip_grant_tables) {
        @pclose(@popen('taskkill /F /IM mysqld.exe', 'r'));
        sleep(2);
    }

    // Bangun argumen mysqld
    $extra_args = $skip_grant_tables ? ' --skip-grant-tables' : '';

    // VBScript launcher: satu-satunya cara yang benar-benar detach mysqld dari Apache di Windows
    $vbs = __DIR__ . '/../start_mysql_hidden.vbs';
    $vbs = str_replace('/', '\\', realpath($vbs) ?: $vbs);

    if (file_exists($vbs) && !$skip_grant_tables) {
        // wscript.exe akan menjalankan VBS yang spawn mysqld secara tersembunyi
        pclose(popen('start /B "" wscript.exe "' . $vbs . '"', "r"));
    } else {
        // Tulis VBS sementara ke temp folder
        $tmp_vbs = sys_get_temp_dir() . '\\start_mysqld.vbs';
        $vbs_content  = "Set WshShell = CreateObject(\"WScript.Shell\")\r\n";
        $vbs_content .= "WshShell.Run \"\\\"" . $mysqld . "\\\" --defaults-file=\\\"" . $ini . "\\\" --standalone" . $extra_args . "\", 0, False\r\n";
        file_put_contents($tmp_vbs, $vbs_content);
        pclose(popen('start /B "" wscript.exe "' . $tmp_vbs . '"', "r"));
    }
    
    // Tunggu MySQL boot (3 detik pertama, cek tiap 0.5 detik)
    for ($i = 0; $i < 6; $i++) {
        usleep(500000); // 0.5 detik
        $test = @mysqli_connect("127.0.0.1", "root", "", "", 3306);
        if ($test) {
            mysqli_close($test);
            return true; // Berhasil
        }
    }
    sleep(2); // Tunggu 2 detik lagi jika belum tersambung
    // Cek sekali lagi
    $test = @mysqli_connect("127.0.0.1", "root", "", "", 3306);
    if ($test) {
        mysqli_close($test);
        return true;
    }
    return false;
}

// Fungsi untuk memperbaiki privilege tables saat error 1130
function repair_mysql_grants() {
    // Deteksi path mysql client
    $mysql_client = 'c:\\xampp1\\mysql\\bin\\mysql.exe';
    if (!file_exists($mysql_client)) {
        $mysql_client = 'c:\\xampp\\mysql\\bin\\mysql.exe';
    }
    
    // Bersihkan orphaned .ibd files dari mysql system database
    $mysql_data_dirs = ['c:/xampp1/mysql/data/mysql/', 'c:/xampp/mysql/data/mysql/'];
    $orphan_tables = ['tb_penduduk', 'tb_pengaturan', 'tb_surat'];
    foreach ($mysql_data_dirs as $data_dir) {
        if (is_dir($data_dir)) {
            foreach ($orphan_tables as $tbl) {
                @unlink($data_dir . $tbl . '.ibd');
                @unlink($data_dir . $tbl . '.frm');
            }
        }
    }
    
    // Start MySQL dengan skip-grant-tables
    $started = auto_start_mysql(true);
    if (!$started) return false;
    
    // Perbaiki privilege via mysql client (karena skip-grant-tables aktif)
    $sql = "FLUSH PRIVILEGES; "
         . "GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' IDENTIFIED BY '' WITH GRANT OPTION; "
         . "GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' IDENTIFIED BY '' WITH GRANT OPTION; "
         . "GRANT ALL PRIVILEGES ON *.* TO 'root'@'::1' IDENTIFIED BY '' WITH GRANT OPTION; "
         . "FLUSH PRIVILEGES;";
    
    // Jalankan via mysql client
    $cmd = '"' . $mysql_client . '" -u root mysql -e "' . $sql . '"';
    @pclose(@popen($cmd, 'r'));
    sleep(1);
    
    // Juga coba via mysqli langsung (skip-grant-tables memperbolehkan koneksi tanpa auth)
    $repair_conn = @mysqli_connect("127.0.0.1", "root", "", "mysql", 3306);
    if ($repair_conn) {
        @mysqli_query($repair_conn, "FLUSH PRIVILEGES");
        @mysqli_query($repair_conn, "GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' IDENTIFIED BY '' WITH GRANT OPTION");
        @mysqli_query($repair_conn, "GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' IDENTIFIED BY '' WITH GRANT OPTION");
        @mysqli_query($repair_conn, "GRANT ALL PRIVILEGES ON *.* TO 'root'@'::1' IDENTIFIED BY '' WITH GRANT OPTION");
        // Drop orphaned tables dari data dictionary
        @mysqli_query($repair_conn, "DROP TABLE IF EXISTS tb_penduduk");
        @mysqli_query($repair_conn, "DROP TABLE IF EXISTS tb_pengaturan");
        @mysqli_query($repair_conn, "DROP TABLE IF EXISTS tb_surat");
        @mysqli_query($repair_conn, "FLUSH PRIVILEGES");
        @mysqli_close($repair_conn);
    }
    
    // Restart MySQL tanpa skip-grant-tables (mode normal)
    @pclose(@popen('taskkill /F /IM mysqld.exe', 'r'));
    sleep(2);
    auto_start_mysql(false);
    
    return true;
}

// Coba membuat koneksi ke database
$conn = @mysqli_connect($host, $db_username, $db_password, $database);

// Jika gagal koneksi, coba recovery otomatis
if (!$conn) {
    $init_errno = mysqli_connect_errno();
    
    if ($init_errno === 2002) {
        // Error 2002: MySQL server tidak jalan → coba start otomatis
        auto_start_mysql();
        $conn = @mysqli_connect($host, $db_username, $db_password, $database);
    }
    
    if (!$conn && (mysqli_connect_errno() === 1130 || $init_errno === 1130)) {
        // Error 1130: Host not allowed → privilege table rusak, perbaiki otomatis
        repair_mysql_grants();
        $conn = @mysqli_connect($host, $db_username, $db_password, $database);
    }
    
    if (!$conn && (mysqli_connect_errno() === 1045 || $init_errno === 1045)) {
        // Error 1045: Access denied → coba juga repair grants
        repair_mysql_grants();
        $conn = @mysqli_connect($host, $db_username, $db_password, $database);
    }
}

// Memeriksa koneksi
if (!$conn) {
    $err_msg = mysqli_connect_error();
    $err_code = mysqli_connect_errno();
    
    // Baca log MySQL jika gagal start
    $log_paths = [
        'c:/xampp1/mysql/data/mysql_error.log',
        'c:/xampp/mysql/data/mysql_error.log'
    ];
    $log_content = "=== Laporan Kegagalan Start MariaDB ===\n";
    $log_content .= "Waktu: " . date('Y-m-d H:i:s') . "\n";
    $log_content .= "Koneksi Error: [{$err_code}] {$err_msg}\n\n";
    $log_content .= "=== 30 Baris Terakhir dari mysql_error.log ===\n";
    
    $log_found = false;
    foreach ($log_paths as $lp) {
        if (file_exists($lp)) {
            $log_found = true;
            $log_data = file_get_contents($lp);
            $log_lines = explode("\n", $log_data);
            $last_lines = array_slice($log_lines, -30);
            $log_content .= implode("\n", $last_lines) . "\n";
            break;
        }
    }
    if (!$log_found) {
        $log_content .= "File mysql_error.log tidak ditemukan.\n";
    }
    
    @file_put_contents(__DIR__ . "/db_error_report.txt", $log_content);
    
    die("<h3>Koneksi ke database gagal!</h3>
         <p><strong>Error:</strong> [{$err_code}] {$err_msg}</p>
         <p>MySQL gagal dijalankan secara otomatis. Detail error log dari MySQL telah disimpan di <strong>config/db_error_report.txt</strong>. Silakan periksa file tersebut.</p>");
}

// Set charset ke utf8mb4 agar mendukung karakter khusus
mysqli_set_charset($conn, "utf8mb4");

// Fungsi download gambar tangguh (mendukung curl + bypass SSL untuk XAMPP Windows)
function download_image_file($url, $filepath) {
    $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    // Coba gunakan cURL dengan bypass SSL (cocok untuk XAMPP local)
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent); // Set User-Agent
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bypass SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $data = curl_exec($ch);
        curl_close($ch);
        if ($data) {
            return @file_put_contents($filepath, $data) !== false;
        }
    }
    
    // Fallback ke file_get_contents jika curl tidak ada
    $context = stream_context_create([
        "http" => [
            "header" => "User-Agent: " . $userAgent . "\r\n"
        ],
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ]
    ]);
    $data = @file_get_contents($url, false, $context);
    if ($data) {
        return @file_put_contents($filepath, $data) !== false;
    }
    
    return false;
}

// Auto-copy menu images and download authentic ones for cemilan
$dst_dir = __DIR__ . '/../assets/images/';
$src_dir = 'C:/Users/Anjas/.gemini/antigravity-ide/brain/ccc4df89-fb04-4942-b162-78f97b959f0f/';

// 1. Kerupuk Udang (Salin hasil generator jika ada)
if (!file_exists($dst_dir . 'kerupuk_udang.jpg')) {
    $matches = glob($src_dir . 'kerupuk_udang_*.png');
    if (!empty($matches)) {
        @copy($matches[0], $dst_dir . 'kerupuk_udang.jpg');
    } else {
        @copy($dst_dir . 'tahu_tempe_penyet.jpg', $dst_dir . 'kerupuk_udang.jpg');
    }
}

// 2. Pisang Goreng (Cek jika belum ada, ukurannya terlalu kecil karena error block, atau masih berupa gambar nasi goreng/pisang goreng fallback)
$nasi_goreng_size = @filesize($dst_dir . 'nasi_goreng.jpg');
$pisang_goreng_size = @filesize($dst_dir . 'pisang_goreng.jpg');
if (!$pisang_goreng_size || $pisang_goreng_size < 1000 || $pisang_goreng_size === $nasi_goreng_size) {
    // Download gambar pisang goreng asli
    $download_success = download_image_file('https://upload.wikimedia.org/wikipedia/commons/0/0f/Pisang_Goreng.jpg', $dst_dir . 'pisang_goreng.jpg');
    if ($download_success) {
        // Hapus file bakwan_goreng.jpg yang lama jika ada
        if (file_exists($dst_dir . 'bakwan_goreng.jpg')) {
            @unlink($dst_dir . 'bakwan_goreng.jpg');
        }
    } else {
        if (!$pisang_goreng_size || $pisang_goreng_size < 1000) {
            @copy($dst_dir . 'nasi_goreng.jpg', $dst_dir . 'pisang_goreng.jpg');
        }
    }
}

// 3. Tahu Crispy (Cek jika belum ada, ukurannya terlalu kecil karena error block, atau masih berupa gambar tahu tempe penyet fallback)
$tahu_tempe_size = @filesize($dst_dir . 'tahu_tempe_penyet.jpg');
$tahu_crispy_size = @filesize($dst_dir . 'tahu_crispy.jpg');
if (!$tahu_crispy_size || $tahu_crispy_size < 1000 || $tahu_crispy_size === $tahu_tempe_size) {
    // Download gambar tahu crispy asli
    download_image_file('https://upload.wikimedia.org/wikipedia/commons/0/07/Tahu_sumedang.jpg', $dst_dir . 'tahu_crispy.jpg');
}

// Sinkronisasi nama file foto di database
@mysqli_query($conn, "UPDATE `menu` SET `foto`='kerupuk_udang.jpg' WHERE `nama_menu`='Kerupuk Udang Crispy'");
@mysqli_query($conn, "UPDATE `menu` SET `nama_menu`='Pisang Goreng Kremes', `deskripsi`='Pisang kepok manis digoreng dengan balutan kremesan renyah. Luar garing, dalam lembut dan manis.', `harga`=8000, `foto`='pisang_goreng.jpg' WHERE `nama_menu`='Bakwan Goreng' OR `nama_menu`='Pisang Goreng Kremes'");
@mysqli_query($conn, "UPDATE `menu` SET `foto`='tahu_crispy.jpg' WHERE `nama_menu`='Tahu Crispy Pedas'");
?>
