<?php
// Dashboard Utama Admin - Ayam Penyet Al-Barokah
require_once '../config/database.php';

// Include Layout Header
include_once 'templates/header.php';
include_once 'templates/sidebar.php';

// --------------------------------------------------------
// Query Statistik Dashboard
// --------------------------------------------------------

// 1. Total Pendapatan (dari pesanan lunas dan tidak dibatalkan)
$query_income = "SELECT SUM(total_harga) AS total FROM pesanan WHERE status_pembayaran = 'lunas' AND status_pesanan != 'dibatalkan'";
$result_income = mysqli_query($conn, $query_income);
$income_data = mysqli_fetch_assoc($result_income);
$total_income = $income_data['total'] ?? 0;

// 2. Total Pesanan Masuk (keseluruhan)
$query_total_orders = "SELECT COUNT(id) AS total FROM pesanan";
$result_total_orders = mysqli_query($conn, $query_total_orders);
$orders_data = mysqli_fetch_assoc($result_total_orders);
$total_orders = $orders_data['total'] ?? 0;

// 3. Jumlah Pesanan Baru / Pending (menunggu diproses)
$query_pending = "SELECT COUNT(id) AS total FROM pesanan WHERE status_pesanan = 'pending'";
$result_pending = mysqli_query($conn, $query_pending);
$pending_data = mysqli_fetch_assoc($result_pending);
$total_pending = $pending_data['total'] ?? 0;

// 4. Jumlah Menu Terdaftar
$query_menu = "SELECT COUNT(id) AS total FROM menu";
$result_menu = mysqli_query($conn, $query_menu);
$menu_data = mysqli_fetch_assoc($result_menu);
$total_menu = $menu_data['total'] ?? 0;

// 5. Ambil 5 Pesanan Terbaru
$query_recent = "SELECT * FROM pesanan ORDER BY id DESC LIMIT 5";
$result_recent = mysqli_query($conn, $query_recent);
?>

<!-- Statistik Cards Grid -->
<div class="stats-grid">
    <div class="stat-card earnings">
        <div class="stat-info">
            <h3>Total Pendapatan</h3>
            <div class="stat-number">Rp <?= number_format($total_income, 0, ',', '.') ?></div>
        </div>
        <div class="stat-icon">
            <i class="fa-solid fa-rupiah-sign"></i>
        </div>
    </div>
    
    <div class="stat-card orders">
        <div class="stat-info">
            <h3>Total Pemesanan</h3>
            <div class="stat-number"><?= $total_orders ?></div>
        </div>
        <div class="stat-icon">
            <i class="fa-solid fa-cart-shopping"></i>
        </div>
    </div>
    
    <div class="stat-card pending">
        <div class="stat-info">
            <h3>Pesanan Baru</h3>
            <div class="stat-number"><?= $total_pending ?></div>
        </div>
        <div class="stat-icon">
            <i class="fa-solid fa-bell fa-shake"></i>
        </div>
    </div>
    
    <div class="stat-card menu-items">
        <div class="stat-info">
            <h3>Menu Terdaftar</h3>
            <div class="stat-number"><?= $total_menu ?></div>
        </div>
        <div class="stat-icon">
            <i class="fa-solid fa-bowl-food"></i>
        </div>
    </div>
</div>

<!-- Panel Pesanan Terbaru -->
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i class="fa-solid fa-list-check"></i> Aktivitas Pesanan Terbaru</h2>
        <a href="kelola_pesanan.php" class="btn-admin btn-admin-primary">
            Lihat Semua Pesanan <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
    
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Kode Pesanan</th>
                    <th>Nama Pemesan</th>
                    <th>Tipe</th>
                    <th>Total Bayar</th>
                    <th>Status Bayar</th>
                    <th>Status Pesanan</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (mysqli_num_rows($result_recent) > 0) {
                    while ($row = mysqli_fetch_assoc($result_recent)) {
                ?>
                        <tr>
                            <td style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($row['kode_pesanan']) ?></td>
                            <td><?= htmlspecialchars($row['nama_pemesan']) ?></td>
                            <td>
                                <span style="font-weight: 600; text-transform: uppercase; font-size: 0.85rem;">
                                    <?php 
                                    if ($row['tipe_pesanan'] === 'dine_in') echo 'Dine In';
                                    elseif ($row['tipe_pesanan'] === 'take_away') echo 'Take Away';
                                    elseif ($row['tipe_pesanan'] === 'delivery') echo 'Delivery';
                                    ?>
                                </span>
                            </td>
                            <td style="font-weight: 600;">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                            <td>
                                <?php 
                                $status_b = $row['status_pembayaran'];
                                if ($status_b === 'belum_bayar') {
                                    echo '<span class="badge badge-payment-unpaid">Belum Bayar</span>';
                                } elseif ($status_b === 'menunggu_konfirmasi') {
                                    echo '<span class="badge badge-payment-waiting">Verifikasi</span>';
                                } elseif ($status_b === 'lunas') {
                                    echo '<span class="badge badge-payment-confirmed">Lunas</span>';
                                } elseif ($status_b === 'ditolak') {
                                    echo '<span class="badge badge-payment-rejected">Ditolak</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                $status_p = $row['status_pesanan'];
                                if ($status_p === 'pending') {
                                    echo '<span class="badge badge-pending">Baru (Pending)</span>';
                                } elseif ($status_p === 'diproses') {
                                    echo '<span class="badge badge-process">Diproses</span>';
                                } elseif ($status_p === 'selesai') {
                                    echo '<span class="badge badge-completed">Selesai</span>';
                                } elseif ($status_p === 'dibatalkan') {
                                    echo '<span class="badge badge-cancelled">Dibatalkan</span>';
                                }
                                ?>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($row['tanggal_pesanan'])) ?></td>
                            <td>
                                <a href="kelola_pesanan.php?id=<?= $row['id'] ?>&action=detail" class="btn-admin btn-admin-primary" style="padding: 5px 10px; font-size: 0.8rem;">
                                    <i class="fa-solid fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                <?php 
                    }
                } else {
                    echo '<tr><td colspan="8" style="text-align: center; padding: 25px; color: var(--text-muted);"><i class="fa-solid fa-inbox"></i> Belum ada pesanan masuk.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel" style="background: linear-gradient(135deg, #2c3e50, #34495e); color: #fff;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <h3 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 5px; color: var(--accent);">Bantuan & Quick Action</h3>
            <p style="color: #bdc3c7; font-size: 0.9rem;">Kelola menu makanan Ayam Penyet Al-Barokah atau cetak laporan hasil penjualan secara instan.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="kelola_menu.php" class="btn-admin btn-admin-primary" style="background-color: var(--primary);"><i class="fa-solid fa-plus"></i> Tambah Menu Baru</a>
            <a href="laporan.php" class="btn-admin btn-admin-success" style="background-color: var(--success);"><i class="fa-solid fa-file-excel"></i> Buat Laporan Penjualan</a>
        </div>
    </div>
</div>

<?php 
// Include Layout Footer
include_once 'templates/footer.php';
?>
