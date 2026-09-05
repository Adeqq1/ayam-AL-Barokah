<?php
/**
 * Halaman Diagnosa, Status & Log Error Database
 * Sistem Pemesanan Ayam Penyet Al-Barokah
 * 
 * Fitur:
 * 1. Live Database Connection Diagnostics & Ping
 * 2. Real-time Table Health & Integrity Inspector (users, menu, pesanan, detail_pesanan)
 * 3. Log Viewer Riwayat Error (Filter, Search, Detail Stack, Download, Clear)
 * 4. Troubleshooting Solusi Mandiri untuk Laragon & Docker VPS
 * 5. Resilient: Berfungsi optimal baik saat database online maupun saat MySQL mati/offline
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tandai bahwa ini adalah halaman diagnosa error database agar config/database.php tidak die()
if (!defined('IN_DB_ERROR_PAGE')) {
    define('IN_DB_ERROR_PAGE', true);
}

// Muat konfigurasi database
require_once __DIR__ . '/config/database.php';

// Menangani pesan toast notifikasi
$flash_message = null;
$flash_type = 'info';

// ==========================================
// ACTION HANDLER (Clear, Simulate, Download, Repair, Import)
// ==========================================
$action = $_GET['action'] ?? null;

if ($action === 'download_logs') {
    $log_file = dirname(__DIR__) . '/ayam-penyet/logs/database_error.log';
    if (!file_exists($log_file)) {
        $log_file = __DIR__ . '/logs/database_error.log';
    }
    if (file_exists($log_file)) {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="albarokah_db_errors_' . date('Ymd_His') . '.log"');
        header('Content-Length: ' . filesize($log_file));
        readfile($log_file);
        exit;
    } else {
        $flash_message = 'File log belum ada atau masih kosong.';
        $flash_type = 'warning';
    }
}

if ($action === 'clear_logs') {
    if (function_exists('clear_database_error_logs') && clear_database_error_logs()) {
        $flash_message = 'Semua riwayat log error database berhasil dibersihkan.';
        $flash_type = 'success';
    } else {
        $flash_message = 'Gagal membersihkan file log database.';
        $flash_type = 'danger';
    }
}

if ($action === 'simulate_log') {
    if (function_exists('log_database_error')) {
        $test_details = [
            'trigger'      => 'Pengujian Manual User/Admin via Diagnosa UI',
            'simulated_at' => date('Y-m-d H:i:s'),
            'file'         => __FILE__,
            'line'         => __LINE__,
            'context'      => 'Simulasi pencatatan error untuk memverifikasi fungsionalitas log viewer'
        ];
        log_database_error(
            'SIMULATION_TEST',
            'Simulasi Error: Pengujian sistem logging error database Al-Barokah berjalan dengan sempurna.',
            9999,
            $test_details
        );
        $flash_message = 'Simulasi log berhasil dibuat! Periksa daftar log di bawah.';
        $flash_type = 'success';
    }
}

if ($action === 'repair_tables' && !empty($conn)) {
    $tables_to_repair = ['users', 'menu', 'pesanan', 'detail_pesanan'];
    $repair_results = [];
    foreach ($tables_to_repair as $tbl) {
        $res = @mysqli_query($conn, "CHECK TABLE `{$tbl}`");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $repair_results[] = "{$tbl}: " . ($row['Msg_text'] ?? 'OK');
        }
        @mysqli_query($conn, "OPTIMIZE TABLE `{$tbl}`");
    }
    $flash_message = 'Pemeriksaan & optimasi tabel selesai: ' . implode(', ', $repair_results);
    $flash_type = 'success';
}

if ($action === 'import_sql' && !empty($conn)) {
    $sql_file = __DIR__ . '/database/db_pemesanan.sql';
    if (file_exists($sql_file)) {
        $sql_content = file_get_contents($sql_file);
        if (mysqli_multi_query($conn, $sql_content)) {
            do {
                if ($result = mysqli_store_result($conn)) {
                    mysqli_free_result($result);
                }
            } while (mysqli_more_results($conn) && mysqli_next_result($conn));
            $flash_message = 'Skema tabel dari database/db_pemesanan.sql berhasil dieksekusi!';
            $flash_type = 'success';
        } else {
            $flash_message = 'Gagal mengeksekusi SQL: ' . mysqli_error($conn);
            $flash_type = 'danger';
            if (function_exists('log_database_error')) {
                log_database_error('MIGRATION_ERROR', mysqli_error($conn), mysqli_errno($conn), ['file' => 'db_pemesanan.sql']);
            }
        }
    } else {
        $flash_message = 'File database/db_pemesanan.sql tidak ditemukan.';
        $flash_type = 'warning';
    }
}

// ==========================================
// PENGUMPULAN DATA DIAGNOSA
// ==========================================

// 1. Status Koneksi
$is_connected = !empty($conn);
$ping_time = null;
$server_version = 'Tidak terhubung';
$server_charset = 'utf8mb4';

if ($is_connected) {
    $start_ping = microtime(true);
    @mysqli_ping($conn);
    $ping_time = round((microtime(true) - $start_ping) * 1000, 2);
    $server_version = mysqli_get_server_info($conn);
    $server_charset = mysqli_character_set_name($conn);
} else {
    $last_err_code = isset($db_error_info['code']) ? $db_error_info['code'] : mysqli_connect_errno();
    $last_err_msg  = isset($db_error_info['message']) ? $db_error_info['message'] : mysqli_connect_error();
}

// 2. Diagnosa Tabel
$table_diagnostics = [];
$total_tables_ok = 0;
$total_tables_warning = 0;
$total_tables_missing = 0;

if ($is_connected && function_exists('check_table_health')) {
    $table_diagnostics = check_table_health($conn);
    foreach ($table_diagnostics as $t) {
        if ($t['status'] === 'ok') $total_tables_ok++;
        elseif ($t['status'] === 'warning') $total_tables_warning++;
        else $total_tables_missing++;
    }
}

// 3. Log Error
$filter_type = $_GET['filter_type'] ?? null;
$search_query = trim($_GET['search'] ?? '');
$all_logs = function_exists('get_database_error_logs') ? get_database_error_logs(200, $filter_type, $search_query) : [];

// Hitung metrik log berdasarkan tipe
$total_all_logs = 0;
$total_conn_logs = 0;
$total_table_logs = 0;
$total_query_logs = 0;

if (function_exists('get_database_error_logs')) {
    $unfiltered_logs = get_database_error_logs(500);
    $total_all_logs = count($unfiltered_logs);
    foreach ($unfiltered_logs as $l) {
        $t = $l['type'] ?? '';
        if ($t === 'CONNECTION_ERROR') $total_conn_logs++;
        elseif ($t === 'TABLE_ERROR') $total_table_logs++;
        elseif ($t === 'QUERY_ERROR') $total_query_logs++;
    }
}

// Metadata Konfigurasi
$cfg_host = $host ?? (getenv('DB_HOST') ?: '127.0.0.1');
$cfg_port = $port ?? (getenv('DB_PORT') ?: 3306);
$cfg_user = $db_username ?? (getenv('DB_USERNAME') ?: 'root');
$cfg_database = $database ?? (getenv('DB_DATABASE') ?: 'db_pemesanan');
$cfg_docker = !empty($is_docker);
$cfg_env_mode = $cfg_docker ? 'Docker VPS Production' : 'Laragon / Localhost';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnosa & Log Error Database - Ayam Penyet Al-Barokah</title>
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="assets/css/fonts.css">
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    
    <style>
        :root {
            --primary: #d35400;
            --primary-hover: #e67e22;
            --dark: #1e293b;
            --dark-card: #0f172a;
            --light-bg: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text: #334155;
            --text-muted: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --radius-sm: 8px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 20px -2px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 30px -4px rgba(0,0,0,0.12);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--light-bg);
            color: var(--text);
            line-height: 1.6;
            padding-bottom: 60px;
        }

        /* Top Navigation Header */
        .diag-navbar {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #ffffff;
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .diag-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(211, 84, 0, 0.35);
        }

        .brand-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .brand-subtitle {
            font-size: 0.8rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .diag-nav-links {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 0.88rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.25s ease;
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.05);
            color: #f1f5f9;
        }

        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .nav-btn-primary {
            background: var(--primary);
            border-color: var(--primary);
            color: #ffffff;
        }

        .nav-btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        /* Main Container */
        .container {
            max-width: 1280px;
            margin: 32px auto;
            padding: 0 24px;
        }

        /* Toast Alert */
        .alert-toast {
            padding: 14px 20px;
            border-radius: var(--radius-sm);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
            animation: slideDown 0.3s ease;
        }

        .alert-toast.success { background: #dcfce7; color: #166534; border-left: 5px solid var(--success); }
        .alert-toast.danger { background: #fee2e2; color: #991b1b; border-left: 5px solid var(--danger); }
        .alert-toast.warning { background: #fef3c7; color: #92400e; border-left: 5px solid var(--warning); }
        .alert-toast.info { background: #e0f2fe; color: #075985; border-left: 5px solid var(--info); }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Status Hero Banner */
        .hero-banner {
            border-radius: var(--radius-md);
            padding: 24px 30px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }

        .hero-banner.connected {
            background: linear-gradient(135deg, #065f46 0%, #047857 100%);
            color: #ecfdf5;
            border: 1px solid #10b981;
        }

        .hero-banner.disconnected {
            background: linear-gradient(135deg, #991b1b 0%, #b91c1c 100%);
            color: #fef2f2;
            border: 1px solid #ef4444;
        }

        .hero-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .hero-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }

        .hero-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .hero-desc {
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .hero-actions {
            display: flex;
            gap: 12px;
        }

        .btn-banner {
            background: rgba(255, 255, 255, 0.95);
            color: #1e293b;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .btn-banner:hover {
            background: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        /* Metric Cards Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .metric-card {
            background: var(--card-bg);
            border-radius: var(--radius-md);
            padding: 22px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: flex-start;
            gap: 18px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .metric-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .metric-icon-box {
            width: 52px;
            height: 52px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .metric-icon-box.green { background: #dcfce7; color: var(--success); }
        .metric-icon-box.red { background: #fee2e2; color: var(--danger); }
        .metric-icon-box.amber { background: #fef3c7; color: var(--warning); }
        .metric-icon-box.blue { background: #e0f2fe; color: var(--info); }
        .metric-icon-box.orange { background: #ffedd5; color: var(--primary); }

        .metric-details {
            flex-grow: 1;
        }

        .metric-label {
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            font-weight: 600;
            margin-bottom: 4px;
        }

        .metric-value {
            font-size: 1.45rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.2;
            margin-bottom: 4px;
        }

        .metric-sub {
            font-size: 0.82rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Section Cards */
        .section-card {
            background: var(--card-bg);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            margin-bottom: 32px;
            overflow: hidden;
        }

        .section-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
        }

        .section-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-subtitle {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .section-body {
            padding: 24px;
        }

        /* Data Tables */
        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.92rem;
        }

        .custom-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            padding: 12px 18px;
            border-bottom: 2px solid var(--border-color);
            white-space: nowrap;
        }

        .custom-table td {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border-color);
            color: #334155;
            vertical-align: middle;
        }

        .custom-table tr:hover td {
            background-color: #f8fafc;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-success { background: #dcfce7; color: #166534; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #e0f2fe; color: #075985; }
        .badge-neutral { background: #f1f5f9; color: #475569; }

        /* Troubleshooting Guide Box */
        .troubleshoot-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: var(--radius-md);
            padding: 22px 26px;
            margin-bottom: 28px;
        }

        .troubleshoot-title {
            color: #92400e;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .step-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 14px;
        }

        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            color: #78350f;
            font-size: 0.92rem;
        }

        .step-number {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #f59e0b;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .code-pill {
            background: #f1f5f9;
            color: #0f172a;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.88rem;
            border: 1px solid #cbd5e1;
        }

        /* Log Filter & Search Toolbar */
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .filter-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-pill {
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 0.84rem;
            font-weight: 600;
            text-decoration: none;
            color: var(--text-muted);
            background: #f1f5f9;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .filter-pill:hover, .filter-pill.active {
            background: var(--dark);
            color: #ffffff;
        }

        .search-box {
            position: relative;
            min-width: 260px;
        }

        .search-box input {
            width: 100%;
            padding: 8px 14px 8px 36px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            font-family: inherit;
            font-size: 0.88rem;
            background: #f8fafc;
            outline: none;
            transition: border-color 0.2s;
        }

        .search-box input:focus {
            border-color: var(--primary);
            background: #ffffff;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        /* Log Accordion / Cards */
        .log-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .log-item {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .log-item:hover {
            border-color: #cbd5e1;
            box-shadow: var(--shadow-sm);
        }

        .log-header {
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            cursor: pointer;
            background: #fcfdfe;
        }

        .log-header:hover {
            background: #f8fafc;
        }

        .log-main-info {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-grow: 1;
            min-width: 0;
        }

        .log-msg {
            font-weight: 500;
            color: #1e293b;
            font-size: 0.92rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .log-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            color: var(--text-muted);
            font-size: 0.82rem;
            flex-shrink: 0;
        }

        .log-body {
            display: none;
            padding: 16px 20px;
            background: #0f172a;
            color: #e2e8f0;
            font-family: monospace;
            font-size: 0.84rem;
            border-top: 1px solid var(--border-color);
            overflow-x: auto;
            white-space: pre-wrap;
            line-height: 1.5;
        }

        .log-body.open {
            display: block;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 48px 20px;
            color: var(--text-muted);
        }

        .empty-icon {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 14px;
        }

        .empty-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 6px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 0.86rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .btn-outline {
            background: #ffffff;
            border-color: var(--border-color);
            color: var(--text);
        }

        .btn-outline:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .btn-danger-outline {
            background: #ffffff;
            border-color: #fca5a5;
            color: var(--danger);
        }

        .btn-danger-outline:hover {
            background: #fee2e2;
        }

        .btn-primary {
            background: var(--primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .diag-navbar {
                flex-direction: column;
                gap: 14px;
                padding: 16px;
                text-align: center;
            }
            .diag-brand {
                flex-direction: column;
            }
            .hero-banner {
                flex-direction: column;
                gap: 18px;
                text-align: center;
            }
            .hero-info {
                flex-direction: column;
            }
            .log-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .log-meta {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>

    <!-- Top Navigation -->
    <header class="diag-navbar">
        <div class="diag-brand">
            <div class="brand-icon">
                <i class="fa-solid fa-database"></i>
            </div>
            <div>
                <div class="brand-title">Ayam Penyet Al-Barokah</div>
                <div class="brand-subtitle">Database & Table Diagnostics Center</div>
            </div>
        </div>
        <nav class="diag-nav-links">
            <a href="?action=retest" class="nav-btn" title="Muat ulang dan tes koneksi">
                <i class="fa-solid fa-arrows-rotate"></i> Refresh Diagnosa
            </a>
            <a href="admin/index.php" class="nav-btn" target="_blank">
                <i class="fa-solid fa-gauge-high"></i> Dashboard Admin
            </a>
            <a href="index.php" class="nav-btn nav-btn-primary" target="_blank">
                <i class="fa-solid fa-store"></i> Buka Toko
            </a>
        </nav>
    </header>

    <main class="container">

        <!-- Flash Alert Notifikasi -->
        <?php if (!empty($flash_message)): ?>
            <div class="alert-toast <?= htmlspecialchars($flash_type) ?>">
                <div>
                    <i class="fa-solid <?= $flash_type === 'success' ? 'fa-circle-check' : ($flash_type === 'danger' ? 'fa-circle-xmark' : 'fa-circle-info') ?> me-2"></i>
                    <?= htmlspecialchars($flash_message) ?>
                </div>
                <a href="db_error.php" style="color: inherit; text-decoration: none; font-weight: bold; margin-left: 12px;">&times;</a>
            </div>
        <?php endif; ?>

        <!-- Hero Status Banner -->
        <?php if (!$is_connected): ?>
            <div class="hero-banner disconnected">
                <div class="hero-info">
                    <div class="hero-icon">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <div class="hero-title">⚠️ Koneksi ke Database MySQL Gagal!</div>
                        <div class="hero-desc">
                            Error [<?= htmlspecialchars($last_err_code) ?>]: <?= htmlspecialchars($last_err_msg) ?> (Host: <?= htmlspecialchars($cfg_host) ?>:<?= htmlspecialchars($cfg_port) ?>)
                        </div>
                    </div>
                </div>
                <div class="hero-actions">
                    <a href="?action=retest" class="btn-banner">
                        <i class="fa-solid fa-arrows-rotate"></i> Coba Hubungkan Lagi
                    </a>
                </div>
            </div>

            <!-- Troubleshooting Solusi Mandiri jika Database Mati -->
            <div class="troubleshoot-box">
                <div class="troubleshoot-title">
                    <i class="fa-solid fa-lightbulb"></i> Panduan Penyelesaian Masalah (Troubleshooting Guide):
                </div>
                <?php if (!$cfg_docker): ?>
                    <p style="color: #78350f; font-size: 0.95rem;">
                        Aplikasi saat ini berjalan pada mode <strong>Laragon / Localhost Development</strong>. Ikuti langkah berikut:
                    </p>
                    <ul class="step-list">
                        <li class="step-item">
                            <span class="step-number">1</span>
                            <div>
                                Buka aplikasi <strong>Laragon</strong> pada komputer Anda, lalu klik tombol <strong>"Start All"</strong> untuk menyalakan MySQL.
                            </div>
                        </li>
                        <li class="step-item">
                            <span class="step-number">2</span>
                            <div>
                                Pastikan nama database yang digunakan di <code>config/database.php</code> sesuai. Saat ini disetel ke <span class="code-pill"><?= htmlspecialchars($cfg_database) ?></span>. Jika seharusnya <span class="code-pill">db_pemesanan</span>, ganti kembali nama database tersebut.
                            </div>
                        </li>
                        <li class="step-item">
                            <span class="step-number">3</span>
                            <div>
                                Periksa port MySQL (default Laragon adalah <span class="code-pill">3306</span>). Pastikan tidak ada konflik dengan aplikasi lain seperti XAMPP.
                            </div>
                        </li>
                    </ul>
                <?php else: ?>
                    <p style="color: #78350f; font-size: 0.95rem;">
                        Aplikasi saat ini berjalan pada mode <strong>Docker VPS Production</strong>. Ikuti langkah berikut:
                    </p>
                    <ul class="step-list">
                        <li class="step-item">
                            <span class="step-number">1</span>
                            <div>
                                Pastikan container MySQL berjalan: jalankan perintah <span class="code-pill">docker ps</span> di terminal server VPS.
                            </div>
                        </li>
                        <li class="step-item">
                            <span class="step-number">2</span>
                            <div>
                                Jalankan <span class="code-pill">docker-compose up -d mysql_shared</span> jika container belum aktif.
                            </div>
                        </li>
                        <li class="step-item">
                            <span class="step-number">3</span>
                            <div>
                                Periksa file <span class="code-pill">.env</span> untuk memastikan kredensial <span class="code-pill">DB_HOST=<?= htmlspecialchars($cfg_host) ?></span>, user, dan password sudah tepat.
                            </div>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        <?php elseif ($total_tables_missing > 0): ?>
            <!-- Hero Banner jika database terhubung tapi tabel aplikasi hilang -->
            <div class="hero-banner" style="background: linear-gradient(135deg, #b45309 0%, #d97706 100%); color: #ffffff; border: 1px solid #f59e0b;">
                <div class="hero-info">
                    <div class="hero-icon" style="background: rgba(255,255,255,0.25);">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <div class="hero-title">⚠️ Database '<?= htmlspecialchars($cfg_database) ?>' Terhubung, Namun Tabel Aplikasi Hilang!</div>
                        <div class="hero-desc">
                            MySQL berhasil terhubung ke database <strong><?= htmlspecialchars($cfg_database) ?></strong>, tetapi <strong><?= $total_tables_missing ?> tabel utama</strong> (seperti <code>menu</code>, <code>users</code>) tidak ditemukan.
                        </div>
                    </div>
                </div>
                <div class="hero-actions">
                    <a href="?action=import_sql" class="btn-banner" onclick="return confirm('Impor skema tabel dari database/db_pemesanan.sql sekarang ke database <?= htmlspecialchars($cfg_database) ?>?')">
                        <i class="fa-solid fa-file-import"></i> Impor Tabel ke Database Ini
                    </a>
                </div>
            </div>

            <!-- Troubleshooting Solusi Tabel Hilang -->
            <div class="troubleshoot-box" style="background: #fffbeb; border-color: #fde68a;">
                <div class="troubleshoot-title" style="color: #92400e;">
                    <i class="fa-solid fa-circle-exclamation"></i> Penyebab & Cara Mengatasi:
                </div>
                <p style="color: #78350f; font-size: 0.95rem; margin-bottom: 12px;">
                    Aplikasi terhubung ke database <strong><?= htmlspecialchars($cfg_database) ?></strong>, namun database ini tidak memiliki tabel sistem pemesanan Ayam Penyet Al-Barokah.
                </p>
                <ul class="step-list" style="color: #78350f;">
                    <li class="step-item">
                        <span class="step-number" style="background: #d97706;">1</span>
                        <div>
                            <strong>Jika Anda Salah Mengubah Nama Database di <code>config/database.php</code>:</strong><br>
                            Ubah kembali nama database menjadi <span class="code-pill">db_pemesanan</span> pada baris 43:
                            <div style="margin-top: 6px;">
                                <code style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; border: 1px solid #cbd5e1; display: inline-block;">$database = getenv('DB_DATABASE') ?: "db_pemesanan";</code>
                            </div>
                        </div>
                    </li>
                    <li class="step-item">
                        <span class="step-number" style="background: #d97706;">2</span>
                        <div>
                            <strong>Jika Anda Ingin Menggunakan Database '<?= htmlspecialchars($cfg_database) ?>':</strong><br>
                            Klik tombol <strong>"Impor Tabel ke Database Ini"</strong> di atas, atau buka HeidiSQL / phpMyAdmin dan impor file <span class="code-pill">database/db_pemesanan.sql</span>.
                        </div>
                    </li>
                </ul>
            </div>
        <?php else: ?>
            <div class="hero-banner connected">
                <div class="hero-info">
                    <div class="hero-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <div class="hero-title">Koneksi Database MySQL Normal & Aktif</div>
                        <div class="hero-desc">
                            Terhubung ke database <strong><?= htmlspecialchars($cfg_database) ?></strong> pada <strong><?= htmlspecialchars($cfg_host) ?>:<?= htmlspecialchars($cfg_port) ?></strong> (Ping: <?= $ping_time ?> ms)
                        </div>
                    </div>
                </div>
                <div class="hero-actions">
                    <a href="?action=repair_tables" class="btn-banner" onclick="return confirm('Jalankan pemeriksaan dan optimasi tabel sekarang?')">
                        <i class="fa-solid fa-screwdriver-wrench"></i> Cek & Optimasi Tabel
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- 4 Grid Kartu Metrik -->
        <div class="metrics-grid">
            <!-- Metric 1: Status Koneksi -->
            <div class="metric-card">
                <div class="metric-icon-box <?= !$is_connected ? 'red' : ($total_tables_missing > 0 ? 'amber' : 'green') ?>">
                    <i class="fa-solid <?= !$is_connected ? 'fa-plug-circle-xmark' : ($total_tables_missing > 0 ? 'fa-triangle-exclamation' : 'fa-plug-circle-check') ?>"></i>
                </div>
                <div class="metric-details">
                    <div class="metric-label">Status Koneksi</div>
                    <div class="metric-value" style="color: <?= !$is_connected ? 'var(--danger)' : ($total_tables_missing > 0 ? 'var(--warning)' : 'var(--success)') ?>;">
                        <?= !$is_connected ? 'Offline' : ($total_tables_missing > 0 ? 'Tabel Hilang' : 'Online') ?>
                    </div>
                    <div class="metric-sub"><?= htmlspecialchars($cfg_host) ?>:<?= htmlspecialchars($cfg_port) ?> &bull; <?= htmlspecialchars($cfg_database) ?></div>
                </div>
            </div>

            <!-- Metric 2: Kesehatan Tabel -->
            <div class="metric-card">
                <div class="metric-icon-box <?= ($total_tables_missing > 0) ? 'red' : (($total_tables_warning > 0) ? 'amber' : 'green') ?>">
                    <i class="fa-solid fa-table-cells"></i>
                </div>
                <div class="metric-details">
                    <div class="metric-label">Kesehatan Tabel</div>
                    <div class="metric-value">
                        <?= $total_tables_ok ?> / <?= count($table_diagnostics) ?: 4 ?> OK
                    </div>
                    <div class="metric-sub">
                        <?= $total_tables_missing > 0 ? "{$total_tables_missing} tabel belum ada" : "Semua tabel utama siap" ?>
                    </div>
                </div>
            </div>

            <!-- Metric 3: Riwayat Error Log -->
            <div class="metric-card">
                <div class="metric-icon-box <?= $total_all_logs > 0 ? 'amber' : 'green' ?>">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="metric-details">
                    <div class="metric-label">Total Error Log</div>
                    <div class="metric-value"><?= $total_all_logs ?> Insiden</div>
                    <div class="metric-sub"><?= $total_conn_logs ?> koneksi, <?= $total_table_logs ?> tabel</div>
                </div>
            </div>

            <!-- Metric 4: Lingkungan Server -->
            <div class="metric-card">
                <div class="metric-icon-box blue">
                    <i class="fa-solid fa-server"></i>
                </div>
                <div class="metric-details">
                    <div class="metric-label">Environment</div>
                    <div class="metric-value" style="font-size: 1.15rem;"><?= htmlspecialchars($cfg_env_mode) ?></div>
                    <div class="metric-sub">PHP <?= PHP_VERSION ?> &bull; MySQL <?= htmlspecialchars(substr($server_version, 0, 15)) ?></div>
                </div>
            </div>
        </div>

        <!-- Section 1: Diagnosa Detail Tabel Database -->
        <section class="section-card">
            <div class="section-header">
                <div>
                    <h2 class="section-title">
                        <i class="fa-solid fa-list-check" style="color: var(--primary);"></i>
                        Integritas & Status Tabel Aplikasi
                    </h2>
                    <div class="section-subtitle">
                        Pemeriksaan ketersediaan skema fisik tabel, jumlah baris data, dan integritas record.
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <?php if ($is_connected): ?>
                        <a href="?action=import_sql" class="btn btn-sm btn-outline" onclick="return confirm('Eksekusi database/db_pemesanan.sql untuk mengimpor atau memperbaiki tabel yang hilang?')">
                            <i class="fa-solid fa-file-import"></i> Impor Skema SQL
                        </a>
                        <a href="?action=repair_tables" class="btn btn-sm btn-outline">
                            <i class="fa-solid fa-arrows-rotate"></i> Cek Integritas
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="section-body" style="padding: 0;">
                <?php if (!$is_connected): ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fa-solid fa-database"></i></div>
                        <div class="empty-title">Tabel Tidak Dapat Diperiksa</div>
                        <p>Server MySQL sedang offline atau gagal terhubung. Nyalakan MySQL untuk melihat status tabel.</p>
                    </div>
                <?php elseif (empty($table_diagnostics)): ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fa-solid fa-circle-question"></i></div>
                        <div class="empty-title">Belum Ada Tabel Terdeteksi</div>
                        <p>Database <code><?= htmlspecialchars($cfg_database) ?></code> kosong atau belum diimpor.</p>
                        <a href="?action=import_sql" class="btn btn-primary" style="margin-top: 14px;">
                            <i class="fa-solid fa-upload"></i> Impor db_pemesanan.sql Sekarang
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Nama Tabel</th>
                                    <th>Fungsi Aplikasi</th>
                                    <th>Status Fisik</th>
                                    <th>Total Baris</th>
                                    <th>Engine & Collation</th>
                                    <th>Validasi Kolom</th>
                                    <th>Integritas (CHECK)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($table_diagnostics as $tbl): ?>
                                    <tr>
                                        <td>
                                            <strong style="font-family: monospace; font-size: 0.95rem;">
                                                <i class="fa-solid fa-table me-1" style="color: var(--text-muted);"></i>
                                                <?= htmlspecialchars($tbl['name']) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.85rem; color: var(--text-muted);">
                                                <?= htmlspecialchars($tbl['description']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($tbl['status'] === 'ok'): ?>
                                                <span class="badge badge-success"><i class="fa-solid fa-check"></i> Siap (Tersedia)</span>
                                            <?php elseif ($tbl['status'] === 'warning'): ?>
                                                <span class="badge badge-warning"><i class="fa-solid fa-triangle-exclamation"></i> Peringatan</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger"><i class="fa-solid fa-xmark"></i> Hilang (Missing)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($tbl['exists']): ?>
                                                <strong><?= number_format($tbl['rows']) ?></strong> record
                                            <?php else: ?>
                                                <span style="color: var(--text-muted);">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span style="font-family: monospace; font-size: 0.82rem;">
                                                <?= htmlspecialchars($tbl['engine'] ?? 'InnoDB') ?> &bull; <?= htmlspecialchars($tbl['collation'] ?? 'utf8mb4') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($tbl['missing_columns'])): ?>
                                                <span class="badge badge-danger" title="Kolom hilang: <?= implode(', ', $tbl['missing_columns']) ?>">
                                                    Kurang: <?= implode(', ', $tbl['missing_columns']) ?>
                                                </span>
                                            <?php elseif ($tbl['exists']): ?>
                                                <span class="badge badge-neutral"><i class="fa-solid fa-check-double"></i> Lengkap</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Tabel Belum Dibuat</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($tbl['exists']): ?>
                                                <span class="badge <?= $tbl['check_result'] === 'OK' ? 'badge-success' : 'badge-danger' ?>">
                                                    <?= htmlspecialchars($tbl['check_result']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Perlu Impor</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Section 2: Log Riwayat Error Database & Tabel -->
        <section class="section-card">
            <div class="section-header">
                <div>
                    <h2 class="section-title">
                        <i class="fa-solid fa-clock-rotate-left" style="color: var(--primary);"></i>
                        Riwayat Log Error Database
                    </h2>
                    <div class="section-subtitle">
                        Catatan kronologis semua kegagalan koneksi, kueri error, dan anomali tabel pada sistem.
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <a href="?action=simulate_log" class="btn btn-sm btn-outline" title="Buat log uji coba untuk mengetes pencatatan">
                        <i class="fa-solid fa-vial"></i> Uji Coba Log
                    </a>
                    <a href="?action=download_logs" class="btn btn-sm btn-outline" title="Unduh file log">
                        <i class="fa-solid fa-download"></i> Unduh .log
                    </a>
                    <a href="?action=clear_logs" class="btn btn-sm btn-danger-outline" onclick="return confirm('Apakah Anda yakin ingin menghapus semua catatan riwayat error log?')" title="Kosongkan file log">
                        <i class="fa-solid fa-trash-can"></i> Bersihkan Log
                    </a>
                </div>
            </div>
            <div class="section-body">
                <!-- Toolbar: Filter Pills & Search -->
                <div class="toolbar">
                    <div class="filter-group">
                        <a href="db_error.php" class="filter-pill <?= empty($filter_type) ? 'active' : '' ?>">
                            Semua (<?= $total_all_logs ?>)
                        </a>
                        <a href="db_error.php?filter_type=CONNECTION_ERROR" class="filter-pill <?= $filter_type === 'CONNECTION_ERROR' ? 'active' : '' ?>">
                            <i class="fa-solid fa-plug-circle-xmark"></i> Koneksi (<?= $total_conn_logs ?>)
                        </a>
                        <a href="db_error.php?filter_type=TABLE_ERROR" class="filter-pill <?= $filter_type === 'TABLE_ERROR' ? 'active' : '' ?>">
                            <i class="fa-solid fa-table-cells-large"></i> Tabel (<?= $total_table_logs ?>)
                        </a>
                        <a href="db_error.php?filter_type=QUERY_ERROR" class="filter-pill <?= $filter_type === 'QUERY_ERROR' ? 'active' : '' ?>">
                            <i class="fa-solid fa-code"></i> Query (<?= $total_query_logs ?>)
                        </a>
                    </div>
                    <form method="GET" action="db_error.php" class="search-box">
                        <?php if (!empty($filter_type)): ?>
                            <input type="hidden" name="filter_type" value="<?= htmlspecialchars($filter_type) ?>">
                        <?php endif; ?>
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" placeholder="Cari pesan atau kode error..." value="<?= htmlspecialchars($search_query) ?>" id="searchLogInput">
                    </form>
                </div>

                <!-- Log List Accordion -->
                <?php if (empty($all_logs)): ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fa-solid fa-shield-halved" style="color: var(--success);"></i></div>
                        <div class="empty-title">Tidak Ada Error yang Tercatat</div>
                        <p>Sistem database berjalan bersih tanpa insiden error yang belum terselesaikan.</p>
                        <a href="?action=simulate_log" class="btn btn-sm btn-outline" style="margin-top: 12px;">
                            <i class="fa-solid fa-vial"></i> Buat Log Simulasi untuk Pengujian
                        </a>
                    </div>
                <?php else: ?>
                    <div class="log-list" id="logListContainer">
                        <?php foreach ($all_logs as $idx => $log): 
                            $type = $log['type'] ?? 'GENERAL';
                            $code = $log['code'] ?? 0;
                            $msg  = $log['message'] ?? 'Unknown error';
                            $time = $log['timestamp'] ?? date('Y-m-d H:i:s');
                            $url  = $log['url'] ?? '-';
                            $ip   = $log['ip'] ?? '-';
                            $file = $log['file'] ?? '-';
                            $line = $log['line'] ?? '-';

                            $badge_class = 'badge-danger';
                            if ($type === 'TABLE_ERROR') $badge_class = 'badge-warning';
                            elseif ($type === 'SIMULATION_TEST') $badge_class = 'badge-info';
                        ?>
                            <div class="log-item" data-search="<?= strtolower(htmlspecialchars($msg . ' ' . $type . ' ' . $code)) ?>">
                                <div class="log-header" onclick="toggleLogDetail(<?= $idx ?>)">
                                    <div class="log-main-info">
                                        <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($type) ?> [<?= htmlspecialchars($code) ?>]</span>
                                        <span class="log-msg" title="<?= htmlspecialchars($msg) ?>"><?= htmlspecialchars($msg) ?></span>
                                    </div>
                                    <div class="log-meta">
                                        <span><i class="fa-regular fa-clock me-1"></i> <?= htmlspecialchars($time) ?></span>
                                        <span title="Klik untuk membuka/menutup detail"><i class="fa-solid fa-chevron-down" id="chevron-<?= $idx ?>"></i></span>
                                    </div>
                                </div>
                                <div class="log-body" id="log-body-<?= $idx ?>">
<strong>[WAKTU]</strong>: <?= htmlspecialchars($time) ?>

<strong>[TIPE ERROR]</strong>: <?= htmlspecialchars($type) ?>

<strong>[KODE ERROR]</strong>: <?= htmlspecialchars($code) ?>

<strong>[PESAN ERROR]</strong>: <?= htmlspecialchars($msg) ?>

<strong>[URL DIPANGGIL]</strong>: <?= htmlspecialchars($url) ?>

<strong>[FILE PEMANGGIL]</strong>: <?= htmlspecialchars($file) ?> (Baris: <?= htmlspecialchars($line) ?>)
<strong>[IP CLIENT]</strong>: <?= htmlspecialchars($ip) ?>

<strong>[DATA DETAIL LENGKAP]</strong>:
<?= htmlspecialchars(json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Informasi Konfigurasi Aktif -->
        <section class="section-card">
            <div class="section-header">
                <div>
                    <h3 class="section-title" style="font-size: 1.05rem;">
                        <i class="fa-solid fa-sliders" style="color: var(--primary);"></i>
                        Parameter Konfigurasi Database Aktif
                    </h3>
                </div>
            </div>
            <div class="section-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; font-size: 0.9rem;">
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">DB Host</div>
                        <div style="font-family: monospace; font-weight: 600;"><?= htmlspecialchars($cfg_host) ?></div>
                    </div>
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">DB Port</div>
                        <div style="font-family: monospace; font-weight: 600;"><?= htmlspecialchars($cfg_port) ?></div>
                    </div>
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">DB Username</div>
                        <div style="font-family: monospace; font-weight: 600;"><?= htmlspecialchars($cfg_user) ?></div>
                    </div>
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">DB Database</div>
                        <div style="font-family: monospace; font-weight: 600;"><?= htmlspecialchars($cfg_database) ?></div>
                    </div>
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Lokasi File Log</div>
                        <div style="font-family: monospace; font-size: 0.82rem; word-break: break-all;">logs/database_error.log</div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <script>
        function toggleLogDetail(idx) {
            const body = document.getElementById('log-body-' + idx);
            const chevron = document.getElementById('chevron-' + idx);
            if (body) {
                body.classList.toggle('open');
                if (chevron) {
                    chevron.classList.toggle('fa-chevron-up');
                    chevron.classList.toggle('fa-chevron-down');
                }
            }
        }

        // Live client-side filter input
        const searchInput = document.getElementById('searchLogInput');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                const items = document.querySelectorAll('#logListContainer .log-item');
                items.forEach(item => {
                    const data = item.getAttribute('data-search') || '';
                    if (data.includes(term)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
    </script>
</body>
</html>
