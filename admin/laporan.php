<?php
// Halaman Laporan Hasil Penjualan Admin - Ayam Penyet Al-Barokah
require_once '../config/database.php';

/** @var mysqli $conn */

// Cek session login admin via layout header
include_once 'templates/header.php';
include_once 'templates/sidebar.php';

// --------------------------------------------------------
// Tentukan Default Tanggal (Awal bulan s/d Hari ini)
// --------------------------------------------------------
$default_start = date('Y-m-01'); // Awal bulan ini
$default_end = date('Y-m-d');    // Hari ini

$start_date = isset($_GET['start_date']) ? mysqli_real_escape_string($conn, $_GET['start_date']) : $default_start;
$end_date = isset($_GET['end_date']) ? mysqli_real_escape_string($conn, $_GET['end_date']) : $default_end;

// --------------------------------------------------------
// Query Laporan Penjualan (Hanya pesanan lunas & selesai)
// --------------------------------------------------------
$query_laporan = "SELECT * FROM pesanan 
                  WHERE status_pembayaran = 'lunas' 
                  AND status_pesanan = 'selesai' 
                  AND DATE(tanggal_pesanan) BETWEEN '$start_date' AND '$end_date' 
                  ORDER BY id ASC";
$result_laporan = mysqli_query($conn, $query_laporan);

// Hitung total akumulasi
$total_transaksi = mysqli_num_rows($result_laporan);
$total_pendapatan = 0;
?>

<!-- Filter Rentang Tanggal -->
<div class="filter-card">
    <form action="laporan.php" method="GET">
        <div class="filter-form-grid">
            <div class="admin-form-group" style="margin-bottom: 0; min-width: 180px;">
                <label for="start_date" class="admin-form-label"><i class="fa-regular fa-calendar-days"></i> Dari Tanggal</label>
                <input type="date" id="start_date" name="start_date" class="admin-form-control" value="<?= htmlspecialchars($start_date) ?>" required>
            </div>
            
            <div class="admin-form-group" style="margin-bottom: 0; min-width: 180px;">
                <label for="end_date" class="admin-form-label"><i class="fa-regular fa-calendar-days"></i> Sampai Tanggal</label>
                <input type="date" id="end_date" name="end_date" class="admin-form-control" value="<?= htmlspecialchars($end_date) ?>" required>
            </div>
            
            <button type="submit" class="btn-admin btn-admin-primary" style="padding: 11px 20px;">
                <i class="fa-solid fa-magnifying-glass"></i> Filter Laporan
            </button>
            
            <button type="button" class="btn-admin btn-admin-success" style="padding: 11px 20px;" onclick="window.print()">
                <i class="fa-solid fa-print"></i> Cetak Laporan
            </button>
        </div>
    </form>
</div>

<!-- Statistik Laporan -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
    <div class="stat-card earnings">
        <div class="stat-info">
            <h3>Total Pendapatan Terhitung</h3>
            <div class="stat-number" id="laporan-pendapatan">Rp 0</div>
        </div>
        <div class="stat-icon">
            <i class="fa-solid fa-wallet"></i>
        </div>
    </div>
    
    <div class="stat-card orders">
        <div class="stat-info">
            <h3>Transaksi Berhasil</h3>
            <div class="stat-number"><?= $total_transaksi ?> Pesanan</div>
        </div>
        <div class="stat-icon">
            <i class="fa-solid fa-circle-check"></i>
        </div>
    </div>
</div>

<!-- Panel Tabel Laporan -->
<div class="panel">
    <div class="panel-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 10px; margin-bottom: 15px;">
        <h2 class="panel-title">
            <i class="fa-solid fa-file-invoice-dollar"></i> Rekapitulasi Laporan Penjualan
            <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: normal; display: block; margin-top: 5px;">
                Periode: <strong><?= date('d M Y', strtotime($start_date)) ?></strong> s/d <strong><?= date('d M Y', strtotime($end_date)) ?></strong>
            </span>
        </h2>
    </div>
    
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">No</th>
                    <th>Tanggal</th>
                    <th>Kode Pesanan</th>
                    <th>Nama Pelanggan</th>
                    <th>Tipe Pesanan</th>
                    <th style="text-align: right;">Total Bayar</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if ($total_transaksi > 0) {
                    while ($row = mysqli_fetch_assoc($result_laporan)) {
                        $total_pendapatan += $row['total_harga'];
                ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-muted);"><?= $no++ ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['tanggal_pesanan'])) ?></td>
                            <td style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($row['kode_pesanan']) ?></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($row['nama_pemesan']) ?></td>
                            <td style="text-transform: uppercase; font-size: 0.82rem; font-weight: 500;">
                                <?php 
                                if ($row['tipe_pesanan'] === 'dine_in') echo 'Dine In';
                                elseif ($row['tipe_pesanan'] === 'take_away') echo 'Take Away';
                                elseif ($row['tipe_pesanan'] === 'delivery') echo 'Delivery';
                                ?>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: var(--dark);">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                        </tr>
                <?php 
                    }
                } else {
                    echo '<tr><td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);"><i class="fa-solid fa-triangle-exclamation"></i> Tidak ada transaksi penjualan yang terverifikasi (Lunas & Selesai) pada rentang tanggal ini.</td></tr>';
                }
                ?>
                
                <?php if ($total_transaksi > 0): ?>
                    <tr style="background: #f8f9fa; font-weight: 800; font-size: 1.05rem;">
                        <td colspan="5" style="text-align: right; color: var(--dark); padding: 18px;">TOTAL AKUMULASI PENDAPATAN:</td>
                        <td style="text-align: right; color: var(--primary); padding: 18px;">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Update Jumlah Pendapatan di Stat Card secara Dinamis via JS -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    var statIncome = document.getElementById('laporan-pendapatan');
    statIncome.textContent = "Rp <?= number_format($total_pendapatan, 0, ',', '.') ?>";
});
</script>

<?php 
// Include Layout Footer
include_once 'templates/footer.php';
?>
