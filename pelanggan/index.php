<?php
// Dashboard Pelanggan - Ayam Penyet Al-Barokah
require_once '../config/database.php';

/** @var mysqli $conn */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi: hanya pelanggan yang boleh masuk
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: ../login.php?redirect=pelanggan/index.php");
    exit;
}

$user_id       = intval($_SESSION['user_id']);
$nama_lengkap  = htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']);
$username      = htmlspecialchars($_SESSION['username']);

// ── Statistik Pesanan Pelanggan ─────────────────────────────
$q_total  = "SELECT COUNT(id) AS total FROM pesanan WHERE user_id = $user_id";
$r_total  = mysqli_query($conn, $q_total);
$total_pesanan = mysqli_fetch_assoc($r_total)['total'] ?? 0;

$q_proses = "SELECT COUNT(id) AS total FROM pesanan WHERE user_id = $user_id AND status_pesanan = 'diproses'";
$r_proses = mysqli_query($conn, $q_proses);
$total_diproses = mysqli_fetch_assoc($r_proses)['total'] ?? 0;

$q_selesai = "SELECT COUNT(id) AS total FROM pesanan WHERE user_id = $user_id AND status_pesanan = 'selesai'";
$r_selesai = mysqli_query($conn, $q_selesai);
$total_selesai = mysqli_fetch_assoc($r_selesai)['total'] ?? 0;

$q_pending = "SELECT COUNT(id) AS total FROM pesanan WHERE user_id = $user_id AND status_pesanan = 'pending'";
$r_pending = mysqli_query($conn, $q_pending);
$total_pending = mysqli_fetch_assoc($r_pending)['total'] ?? 0;

// ── Riwayat Pesanan ────────────────────────────────────────
$q_riwayat = "SELECT * FROM pesanan WHERE user_id = $user_id ORDER BY id DESC LIMIT 20";
$r_riwayat = mysqli_query($conn, $q_riwayat);

// Include Header (shared)
include_once '../includes/header.php';
?>

<style>
/* ── Dashboard Pelanggan Styles ───────────────────────────── */
.dashboard-wrapper {
    max-width: 1100px;
    margin: 0 auto;
    padding: 40px 0 80px;
}

/* Welcome Banner */
.welcome-banner {
    background: linear-gradient(135deg, var(--primary) 0%, #e67e22 60%, #c0392b 100%);
    border-radius: 20px;
    padding: 30px 35px;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 32px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(211,84,0,0.3);
}
.welcome-banner::before {
    content: '';
    position: absolute;
    width: 300px; height: 300px;
    background: rgba(255,255,255,0.07);
    border-radius: 50%;
    right: -60px; top: -80px;
}
.welcome-banner::after {
    content: '';
    position: absolute;
    width: 200px; height: 200px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
    left: -40px; bottom: -80px;
}
.welcome-text h2 {
    font-family: var(--font-heading);
    font-size: 1.8rem;
    font-weight: 800;
    margin-bottom: 6px;
}
.welcome-text p { font-size: 0.92rem; opacity: 0.88; }
.welcome-avatar {
    width: 64px; height: 64px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem;
    flex-shrink: 0;
    border: 2px solid rgba(255,255,255,0.35);
    backdrop-filter: blur(4px);
    position: relative; z-index: 1;
}

/* Stat Cards */
.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 32px;
}
.stat-box {
    background: var(--card-bg);
    border-radius: var(--radius-md);
    padding: 22px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}
.stat-box:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}
.stat-box-icon {
    width: 52px; height: 52px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}
.stat-box-icon.orange  { background: linear-gradient(135deg, #fff3e0, #ffe0b2); color: #e65100; }
.stat-box-icon.blue    { background: linear-gradient(135deg, #e3f2fd, #bbdefb); color: #1565c0; }
.stat-box-icon.green   { background: linear-gradient(135deg, #e8f5e9, #c8e6c9); color: #2e7d32; }
.stat-box-icon.yellow  { background: linear-gradient(135deg, #fffde7, #fff9c4); color: #f57f17; }
.stat-box-label {
    font-size: 0.78rem;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.stat-box-value {
    font-size: 1.9rem;
    font-weight: 800;
    color: var(--dark);
    line-height: 1;
    font-family: var(--font-heading);
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 32px;
}
.quick-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 18px;
    border-radius: var(--radius-md);
    background: var(--card-bg);
    border: 1.5px solid var(--border-color);
    color: var(--dark);
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
}
.quick-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: #fff8f5;
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.quick-btn i {
    width: 38px; height: 38px;
    background: linear-gradient(135deg, #fff3e0, #ffe0b2);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: var(--primary);
    font-size: 1rem;
    flex-shrink: 0;
}

/* Panel Riwayat */
.history-panel {
    background: var(--card-bg);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
    overflow: hidden;
}
.history-panel-header {
    padding: 20px 25px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(to right, #faf9f6, #fff);
}
.history-panel-header h2 {
    font-family: var(--font-heading);
    font-size: 1.25rem;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 10px;
}
.history-panel-header h2 i { color: var(--primary); }

.history-table { width: 100%; border-collapse: collapse; }
.history-table thead th {
    background: #f8f9fa;
    padding: 13px 16px;
    text-align: left;
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.4px;
    border-bottom: 1px solid var(--border-color);
}
.history-table tbody td {
    padding: 14px 16px;
    font-size: 0.875rem;
    color: var(--text);
    border-bottom: 1px solid rgba(0,0,0,0.04);
    vertical-align: middle;
}
.history-table tbody tr:last-child td { border-bottom: none; }
.history-table tbody tr:hover { background: #faf9f6; }

.kode-pesanan-cell {
    font-weight: 700;
    color: var(--primary);
    font-size: 0.82rem;
    letter-spacing: 0.3px;
}

/* Badges */
.badge-db {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.3px;
}
.badge-belum-bayar  { background: #fdf2f2; color: #c0392b; }
.badge-menunggu     { background: #fef9e7; color: #d4ac0d; }
.badge-lunas        { background: #e8f8f5; color: #1e8449; }
.badge-ditolak      { background: #fdf2f2; color: #922b21; }
.badge-pending      { background: #f0f4ff; color: #2471a3; }
.badge-diproses     { background: #fff3cd; color: #856404; }
.badge-selesai      { background: #d4edda; color: #155724; }
.badge-dibatalkan   { background: #f8d7da; color: #721c24; }

.tipe-chip {
    display: inline-block;
    padding: 3px 9px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    background: #f4f6f7;
    color: var(--text-muted);
}

.btn-aksi-detail {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    background: var(--primary);
    color: #fff;
    text-decoration: none;
    transition: var(--transition);
}
.btn-aksi-detail:hover {
    background: var(--primary-hover);
    transform: translateY(-1px);
}

/* Upload button variant */
.btn-aksi-upload {
    background: #f1c40f;
    color: var(--dark);
}
.btn-aksi-upload:hover { background: #d4ac0d; color: #fff; }

.empty-history {
    text-align: center;
    padding: 60px 20px;
}
.empty-history i {
    font-size: 3.5rem;
    color: #dfe6e9;
    margin-bottom: 16px;
}
.empty-history h3 {
    font-family: var(--font-heading);
    color: var(--dark);
    margin-bottom: 8px;
    font-size: 1.2rem;
}
.empty-history p { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px; }

@media (max-width: 900px) {
    .dashboard-stats   { grid-template-columns: repeat(2, 1fr); }
    .quick-actions     { grid-template-columns: 1fr; }
    .welcome-banner    { flex-direction: column; align-items: flex-start; }
    .history-table     { display: block; overflow-x: auto; }
}
@media (max-width: 600px) {
    .dashboard-stats   { grid-template-columns: 1fr 1fr; }
    .stat-box-value    { font-size: 1.5rem; }
}
</style>

<div class="container">
    <div class="dashboard-wrapper">

        <!-- ── Welcome Banner ─────────────────────────────── -->
        <div class="welcome-banner">
            <div class="welcome-text" style="position:relative;z-index:1;">
                <h2>Selamat Datang, <?= $nama_lengkap ?>! 👋</h2>
                <p>Kelola pesanan Anda, pantau status pembayaran, dan nikmati kemudahan memesan hidangan Al-Barokah.</p>
            </div>
            <div class="welcome-avatar">
                <i class="fa-solid fa-user"></i>
            </div>
        </div>

        <!-- ── Statistik Pesanan ───────────────────────────── -->
        <div class="dashboard-stats">
            <div class="stat-box">
                <div class="stat-box-icon orange"><i class="fa-solid fa-bag-shopping"></i></div>
                <div>
                    <div class="stat-box-label">Total Pesanan</div>
                    <div class="stat-box-value"><?= $total_pesanan ?></div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-box-icon yellow"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div>
                    <div class="stat-box-label">Menunggu</div>
                    <div class="stat-box-value"><?= $total_pending ?></div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-box-icon blue"><i class="fa-solid fa-fire-flame-curved"></i></div>
                <div>
                    <div class="stat-box-label">Sedang Diproses</div>
                    <div class="stat-box-value"><?= $total_diproses ?></div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-box-icon green"><i class="fa-solid fa-circle-check"></i></div>
                <div>
                    <div class="stat-box-label">Selesai</div>
                    <div class="stat-box-value"><?= $total_selesai ?></div>
                </div>
            </div>
        </div>

        <!-- ── Quick Actions ──────────────────────────────── -->
        <div class="quick-actions">
            <a href="../index.php#menu" class="quick-btn">
                <i class="fa-solid fa-utensils"></i>
                <div>
                    <div>Pesan Sekarang</div>
                    <div style="font-size:0.78rem;color:var(--text-muted);font-weight:400;">Lihat katalog menu</div>
                </div>
            </a>
            <a href="../fitur_pemesanan/keranjang.php" class="quick-btn">
                <i class="fa-solid fa-basket-shopping"></i>
                <div>
                    <div>Keranjang Saya</div>
                    <div style="font-size:0.78rem;color:var(--text-muted);font-weight:400;">Lihat item di keranjang</div>
                </div>
            </a>
            <a href="../logout.php" class="quick-btn" onclick="return confirm('Yakin ingin keluar dari akun ini?')">
                <i class="fa-solid fa-right-from-bracket" style="background:linear-gradient(135deg,#fdf2f2,#f5c6cb);color:#c0392b;"></i>
                <div>
                    <div>Keluar Akun</div>
                    <div style="font-size:0.78rem;color:var(--text-muted);font-weight:400;">Logout dari <?= $username ?></div>
                </div>
            </a>
        </div>

        <!-- ── Riwayat Pesanan ────────────────────────────── -->
        <div class="history-panel">
            <div class="history-panel-header">
                <h2><i class="fa-solid fa-list-check"></i> Riwayat Pesanan Saya</h2>
                <a href="../index.php#menu" class="btn btn-primary" style="padding:8px 18px;font-size:0.85rem;">
                    <i class="fa-solid fa-plus"></i> Pesan Lagi
                </a>
            </div>

            <?php if (mysqli_num_rows($r_riwayat) > 0): ?>
            <div style="overflow-x:auto;">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kode Pesanan</th>
                            <th>Tipe</th>
                            <th>Total</th>
                            <th>Status Bayar</th>
                            <th>Status Pesanan</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($r_riwayat)):
                        // Badge status pembayaran
                        $sp = $row['status_pembayaran'];
                        if ($sp === 'belum_bayar')          $badge_p = '<span class="badge-db badge-belum-bayar"><i class="fa-solid fa-clock"></i> Belum Bayar</span>';
                        elseif ($sp === 'menunggu_konfirmasi') $badge_p = '<span class="badge-db badge-menunggu"><i class="fa-solid fa-spinner fa-spin"></i> Verifikasi</span>';
                        elseif ($sp === 'lunas')             $badge_p = '<span class="badge-db badge-lunas"><i class="fa-solid fa-check"></i> Lunas</span>';
                        else                                 $badge_p = '<span class="badge-db badge-ditolak"><i class="fa-solid fa-xmark"></i> Ditolak</span>';

                        // Badge status pesanan
                        $so = $row['status_pesanan'];
                        if ($so === 'pending')        $badge_o = '<span class="badge-db badge-pending">Pending</span>';
                        elseif ($so === 'diproses')   $badge_o = '<span class="badge-db badge-diproses">Diproses</span>';
                        elseif ($so === 'selesai')    $badge_o = '<span class="badge-db badge-selesai">Selesai</span>';
                        else                          $badge_o = '<span class="badge-db badge-dibatalkan">Dibatalkan</span>';

                        // Tipe pesanan label
                        $tipe_label = ['dine_in' => 'Dine In', 'take_away' => 'Take Away', 'delivery' => 'Delivery'];
                        $tipe = $tipe_label[$row['tipe_pesanan']] ?? $row['tipe_pesanan'];

                        // Tombol aksi: upload jika belum/ditolak, lihat detail jika sudah
                        $konfirmasi_url = "../fitur_pemesanan/konfirmasi-bayar.php?kode=" . urlencode($row['kode_pesanan']);
                        if ($sp === 'belum_bayar' || $sp === 'ditolak') {
                            $btn_aksi = "<a href=\"$konfirmasi_url\" class=\"btn-aksi-detail btn-aksi-upload\"><i class=\"fa-solid fa-upload\"></i> Upload Bukti</a>";
                        } else {
                            $btn_aksi = "<a href=\"$konfirmasi_url\" class=\"btn-aksi-detail\"><i class=\"fa-solid fa-eye\"></i> Detail</a>";
                        }
                    ?>
                        <tr>
                            <td style="color:var(--text-muted);font-size:0.8rem;"><?= $no++ ?></td>
                            <td class="kode-pesanan-cell"><?= htmlspecialchars($row['kode_pesanan']) ?></td>
                            <td><span class="tipe-chip"><?= $tipe ?></span></td>
                            <td style="font-weight:700;">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                            <td><?= $badge_p ?></td>
                            <td><?= $badge_o ?></td>
                            <td style="color:var(--text-muted);font-size:0.8rem;white-space:nowrap;">
                                <?= date('d M Y', strtotime($row['tanggal_pesanan'])) ?><br>
                                <span style="font-size:0.72rem;"><?= date('H:i', strtotime($row['tanggal_pesanan'])) ?> WIB</span>
                            </td>
                            <td><?= $btn_aksi ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-history">
                <i class="fa-solid fa-receipt"></i>
                <h3>Belum Ada Riwayat Pesanan</h3>
                <p>Anda belum pernah melakukan pemesanan. Yuk, mulai pesan hidangan lezat Ayam Penyet Al-Barokah!</p>
                <a href="../index.php#menu" class="btn btn-primary">
                    <i class="fa-solid fa-utensils"></i> Mulai Pesan Sekarang
                </a>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
