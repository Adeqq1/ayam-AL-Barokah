<?php
/**
 * Ayam Penyet Al-Barokah - Docker & DB Connectivity Verification
 */

$startTime = microtime(true);

// 1. Read .env if exists
$envFile = dirname(__DIR__) . '/.env';
$env = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        list($k, $v) = explode('=', $line, 2);
        $env[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
    }
}

$dbHost = getenv('DB_HOST') ?: ($env['DB_HOST'] ?? 'mysql_shared');
$dbPort = (int)(getenv('DB_PORT') ?: ($env['DB_PORT'] ?? 3306));
$dbName = getenv('DB_DATABASE') ?: ($env['DB_DATABASE'] ?? 'db_pemesanan');
$dbUser = getenv('DB_USERNAME') ?: ($env['DB_USERNAME'] ?? 'albarokah_user');
$dbPass = getenv('DB_PASSWORD') ?: ($env['DB_PASSWORD'] ?? 'AlBarokahSecret2026!');
$appName = getenv('APP_NAME') ?: ($env['APP_NAME'] ?? 'Ayam AL Barokah');

// Required extensions
$requiredExts = ['pdo_mysql', 'mysqli', 'mbstring', 'bcmath', 'gd', 'zip', 'opcache', 'redis'];
$extStatus = [];
foreach ($requiredExts as $ext) {
    $extStatus[$ext] = ($ext === 'opcache') ? (extension_loaded('Zend OPcache') || extension_loaded('opcache')) : extension_loaded($ext);
}

// Test DB Connection
$dbConnected = false;
$dbError = null;
$tableStats = [];
$dbVersion = null;

try {
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 3,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $dbConnected = true;
    $dbVersion = $pdo->query("SELECT VERSION()")->fetchColumn();
    
    // Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $cnt = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        $tableStats[$table] = (int)$cnt;
    }
} catch (Exception $e) {
    $dbConnected = false;
    $dbError = $e->getMessage();
}

$latencyMs = round((microtime(true) - $startTime) * 1000, 2);

// JSON response if requested
if (isset($_GET['json']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $dbConnected ? 'ok' : 'degraded',
        'app' => $appName,
        'php_version' => PHP_VERSION,
        'opcache_enabled' => function_exists('opcache_get_status') && !empty(opcache_get_status()['opcache_enabled']),
        'database' => [
            'connected' => $dbConnected,
            'host' => $dbHost,
            'port' => $dbPort,
            'database' => $dbName,
            'user' => $dbUser,
            'version' => $dbVersion,
            'error' => $dbError,
            'tables' => $tableStats
        ],
        'extensions' => $extStatus,
        'latency_ms' => $latencyMs,
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($appName) ?> - Status & Connectivity</title>
    <style>
        :root {
            --primary: #d97706;
            --success: #16a34a;
            --danger: #dc2626;
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #1e293b;
            --muted: #64748b;
        }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 30px 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 2px solid #e2e8f0; padding-bottom: 16px; }
        h1 { margin: 0; font-size: 1.6rem; color: #b45309; }
        .badge { padding: 6px 14px; border-radius: 9999px; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .card { background: var(--card); border-radius: 12px; padding: 24px; margin-bottom: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07); border: 1px solid #e2e8f0; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 16px; }
        .metric-box { background: #f1f5f9; padding: 14px; border-radius: 8px; }
        .metric-label { font-size: 0.75rem; text-transform: uppercase; color: var(--muted); font-weight: 600; margin-bottom: 4px; }
        .metric-val { font-size: 1.1rem; font-weight: 700; }
        .ext-grid { display: flex; flex-wrap: wrap; gap: 8px; }
        .ext-chip { font-size: 0.8rem; padding: 4px 10px; border-radius: 6px; font-family: monospace; }
        .ext-ok { background: #dcfce7; color: #166534; }
        .ext-fail { background: #fee2e2; color: #991b1b; }
        .btn-link { display: inline-block; background: #b45309; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-top: 10px; }
        .btn-link:hover { background: #92400e; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <h1>🍗 <?= htmlspecialchars($appName) ?></h1>
            <small style="color:var(--muted)">Laravel & Docker Production Environment</small>
        </div>
        <div>
            <?php if ($dbConnected): ?>
                <span class="badge badge-success">✓ Stack Operational</span>
            <?php else: ?>
                <span class="badge badge-danger">✗ DB Degraded</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <h3>Database Connectivity Status</h3>
        <div class="grid">
            <div class="metric-box">
                <div class="metric-label">Database Status</div>
                <div class="metric-val" style="color: <?= $dbConnected ? 'var(--success)' : 'var(--danger)' ?>">
                    <?= $dbConnected ? '✓ Connected' : '✗ Failed' ?>
                </div>
            </div>
            <div class="metric-box">
                <div class="metric-label">DB Host & Port</div>
                <div class="metric-val"><?= htmlspecialchars($dbHost) ?>:<?= $dbPort ?></div>
            </div>
            <div class="metric-box">
                <div class="metric-label">Database Name</div>
                <div class="metric-val"><?= htmlspecialchars($dbName) ?></div>
            </div>
            <div class="metric-box">
                <div class="metric-label">DB User</div>
                <div class="metric-val"><?= htmlspecialchars($dbUser) ?></div>
            </div>
        </div>

        <?php if ($dbConnected && !empty($tableStats)): ?>
            <h4 style="margin-top: 16px; margin-bottom: 8px;">Database Tables & Records:</h4>
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px;">
                <ul style="margin:0; padding-left: 20px; font-family: monospace;">
                    <?php foreach ($tableStats as $tbl => $count): ?>
                        <li><strong><?= htmlspecialchars($tbl) ?>:</strong> <?= $count ?> record(s)</li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif (!$dbConnected): ?>
            <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:8px; font-family:monospace; margin-top:10px;">
                <strong>Error:</strong> <?= htmlspecialchars($dbError) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Runtime & Extensions</h3>
        <div class="grid">
            <div class="metric-box">
                <div class="metric-label">PHP Version</div>
                <div class="metric-val"><?= PHP_VERSION ?></div>
            </div>
            <div class="metric-box">
                <div class="metric-label">SAPI Interface</div>
                <div class="metric-val"><?= php_sapi_name() ?></div>
            </div>
            <div class="metric-box">
                <div class="metric-label">OPcache Status</div>
                <div class="metric-val">
                    <?= (function_exists('opcache_get_status') && !empty(opcache_get_status()['opcache_enabled'])) ? '✓ Enabled' : 'Disabled' ?>
                </div>
            </div>
            <div class="metric-box">
                <div class="metric-label">Response Time</div>
                <div class="metric-val"><?= $latencyMs ?> ms</div>
            </div>
        </div>

        <h4>Required Laravel Extensions:</h4>
        <div class="ext-grid">
            <?php foreach ($extStatus as $ext => $loaded): ?>
                <span class="ext-chip <?= $loaded ? 'ext-ok' : 'ext-fail' ?>">
                    <?= $loaded ? '✓' : '✗' ?> <?= htmlspecialchars($ext) ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <a href="/" class="btn-link">🍽️ Buka Web Utama Ayam Penyet Al-Barokah</a>
        <br><br>
        <small style="color:var(--muted)">Format JSON: <a href="?json=1" style="color:#b45309">?json=1</a></small>
    </div>
</div>
</body>
</html>
