<?php
// Halaman Kelola Pesanan Admin - Ayam Penyet Al-Barokah
require_once '../config/database.php';

/** @var mysqli $conn */

// Cek session login admin via layout header
include_once 'templates/header.php';
include_once 'templates/sidebar.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error_msg = "";
$success_msg = "";

// --------------------------------------------------------
// Handle Update Status Pesanan & Pembayaran (POST)
// --------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $pesanan_id = intval($_POST['id']);
    $status_pesanan = mysqli_real_escape_string($conn, $_POST['status_pesanan']);
    
    // Otomatisasi: Jika status pesanan selesai, maka status pembayaran otomatis lunas
    if ($status_pesanan === 'selesai') {
        $status_pembayaran = 'lunas';
    } else {
        $status_pembayaran = mysqli_real_escape_string($conn, $_POST['status_pembayaran']);
    }
    
    if ($pesanan_id > 0) {
        $query_update = "UPDATE pesanan SET 
                         status_pembayaran = '$status_pembayaran', 
                         status_pesanan = '$status_pesanan' 
                         WHERE id = $pesanan_id";
        
        if (mysqli_query($conn, $query_update)) {
            $success_msg = "Status pesanan berhasil diperbarui!";
        } else {
            $error_msg = "Gagal memperbarui status pesanan: " . mysqli_error($conn);
        }
    }
}

// Handle Delete Pesanan (GET)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $pesanan_id = intval($_GET['id']);
    if ($pesanan_id > 0) {
        mysqli_query($conn, "DELETE FROM detail_pesanan WHERE pesanan_id = $pesanan_id");
        if (mysqli_query($conn, "DELETE FROM pesanan WHERE id = $pesanan_id")) {
            $success_msg = "Pesanan berhasil dihapus!";
        } else {
            $error_msg = "Gagal menghapus pesanan: " . mysqli_error($conn);
        }
    }
    $action = 'list';
}

// Ambil parameter filter status pesanan
$filter_status = isset($_GET['filter']) ? mysqli_real_escape_string($conn, $_GET['filter']) : 'semua';
?>

<!-- Alert Notifikasi -->
<?php if (!empty($success_msg)): ?>
    <div style="background-color: #e8f8f5; border: 1px solid #d1f2eb; color: var(--success); padding: 15px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 500;">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_msg) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
    <div style="background-color: #fdedec; border: 1px solid #fadbd8; color: var(--danger); padding: 15px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 500;">
        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error_msg) ?>
    </div>
<?php endif; ?>

<!-- --------------------------------------------------------
     VIEW: DETAIL PESANAN
     -------------------------------------------------------- -->
<?php if ($action === 'detail'): ?>
    <?php
    $pesanan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    // Fetch data utama pesanan
    $query_pesanan = "SELECT * FROM pesanan WHERE id = $pesanan_id";
    $result_pesanan = mysqli_query($conn, $query_pesanan);
    
    if (!$result_pesanan || mysqli_num_rows($result_pesanan) === 0) {
        echo "<div class='panel'>Pesanan tidak ditemukan!</div>";
        include_once 'templates/footer.php';
        exit;
    }
    
    $pesanan = mysqli_fetch_assoc($result_pesanan);
    
    // Fetch detail item belanja
    $query_details = "SELECT dp.*, m.nama_menu, m.kategori, m.foto, m.harga FROM detail_pesanan dp 
                      JOIN menu m ON dp.menu_id = m.id 
                      WHERE dp.pesanan_id = $pesanan_id";
    $result_details = mysqli_query($conn, $query_details);
    ?>
    
    <div style="display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 25px; margin-bottom: 30px;">
        
        <!-- Informasi Kanan/Kiri: Detail Transaksi -->
        <div class="panel">
            <div class="panel-header" style="border-bottom: 2px solid var(--border-color); padding-bottom: 12px; margin-bottom: 25px;">
                <h2 class="panel-title"><i class="fa-solid fa-receipt"></i> Rincian Pesanan: <span style="color: var(--primary);"><?= htmlspecialchars($pesanan['kode_pesanan']) ?></span></h2>
                <a href="kelola_pesanan.php" class="btn-admin btn-admin-primary" style="background-color: var(--dark);"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <div>
                    <h4 style="font-weight: 700; color: var(--dark); font-size: 1rem; margin-bottom: 10px;">Informasi Pemesan:</h4>
                    <p style="font-size: 0.92rem; margin-bottom: 5px;"><strong>Nama:</strong> <?= htmlspecialchars($pesanan['nama_pemesan']) ?></p>
                    <p style="font-size: 0.92rem; margin-bottom: 5px;"><strong>Telepon/WA:</strong> <?= htmlspecialchars($pesanan['no_telepon']) ?></p>
                    <p style="font-size: 0.92rem; margin-bottom: 5px;"><strong>Tipe:</strong> <span style="text-transform: uppercase; font-weight: 600; font-size: 0.8rem;"><?= htmlspecialchars($pesanan['tipe_pesanan']) ?></span></p>
                    <p style="font-size: 0.92rem; margin-bottom: 5px;"><strong>Tanggal:</strong> <?= date('d F Y, H:i', strtotime($pesanan['tanggal_pesanan'])) ?></p>
                </div>
                <div>
                    <?php if ($pesanan['tipe_pesanan'] === 'delivery'): ?>
                        <h4 style="font-weight: 700; color: var(--dark); font-size: 1rem; margin-bottom: 10px;">Alamat Pengiriman:</h4>
                        <p style="font-size: 0.9rem; background: #faf9f6; padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); color: var(--text); line-height: 1.5; height: 100px; overflow-y: auto;">
                            <?= nl2br(htmlspecialchars($pesanan['alamat'])) ?>
                        </p>
                    <?php else: ?>
                        <h4 style="font-weight: 700; color: var(--dark); font-size: 1rem; margin-bottom: 10px;">Keterangan Pengambilan:</h4>
                        <p style="font-size: 0.9rem; color: var(--text-muted);">Makan Di Tempat (Dine In) atau Bawa Pulang (Take Away) di outlet Al-Barokah.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <h4 style="font-weight: 700; color: var(--dark); font-size: 1rem; margin-bottom: 12px;"><i class="fa-solid fa-list"></i> Menu yang Dipesan:</h4>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th>Harga Satuan</th>
                            <th style="text-align: center;">Jumlah</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        while ($item = mysqli_fetch_assoc($result_details)) {
                        ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <img src="../assets/images/<?= htmlspecialchars($item['foto']) ?>" onerror="this.src='../assets/images/default-menu.jpg'" style="width: 40px; height: 40px; border-radius: 4px; object-fit: cover;">
                                        <strong style="color: var(--dark); font-weight: 600;"><?= htmlspecialchars($item['nama_menu']) ?></strong>
                                    </div>
                                </td>
                                <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                <td style="text-align: center;"><?= $item['jumlah'] ?>x</td>
                                <td style="text-align: right; font-weight: 600; color: var(--primary);">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                            </tr>
                        <?php 
                        }
                        ?>
                        
                        <!-- Biaya delivery jika delivery -->
                        <?php if ($pesanan['tipe_pesanan'] === 'delivery'): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 40px; height: 40px; border-radius: 4px; background: #eaf2f8; color: var(--info); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;"><i class="fa-solid fa-truck"></i></div>
                                        <strong style="color: var(--dark); font-weight: 600;">Biaya Pengiriman (Ongkir)</strong>
                                    </div>
                                </td>
                                <td>Rp 10.000</td>
                                <td style="text-align: center;">1x</td>
                                <td style="text-align: right; font-weight: 600; color: var(--primary);">Rp 10.000</td>
                            </tr>
                        <?php endif; ?>
                        
                        <tr style="background: #f8f9fa;">
                            <td colspan="3" style="text-align: right; font-weight: 700; font-size: 1.05rem; color: var(--dark);">Grand Total Tagihan:</td>
                            <td style="text-align: right; font-weight: 800; font-size: 1.15rem; color: var(--primary);">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Kolom Kanan: Verifikasi Pembayaran & Ubah Status -->
        <div style="display: flex; flex-direction: column; gap: 25px;">
            
            <!-- Update Status Panel -->
            <div class="panel">
                <h3 class="panel-title" style="margin-bottom: 20px; border-bottom: 2px solid var(--border-color); padding-bottom: 8px;"><i class="fa-solid fa-circle-notch"></i> Update Status</h3>
                
                <form action="kelola_pesanan.php?action=detail&id=<?= $pesanan['id'] ?>" method="POST">
                    <input type="hidden" name="id" value="<?= $pesanan['id'] ?>">
                    
                    <div class="admin-form-group">
                        <label for="status_pembayaran" class="admin-form-label">Verifikasi Pembayaran *</label>
                        <select id="status_pembayaran" name="status_pembayaran" class="admin-form-select" required>
                            <option value="belum_bayar" <?= $pesanan['status_pembayaran'] === 'belum_bayar' ? 'selected' : '' ?>>Belum Dibayar</option>
                            <option value="menunggu_konfirmasi" <?= $pesanan['status_pembayaran'] === 'menunggu_konfirmasi' ? 'selected' : '' ?>>Menunggu Verifikasi Bukti</option>
                            <option value="lunas" <?= $pesanan['status_pembayaran'] === 'lunas' ? 'selected' : '' ?>>Lunas / Terverifikasi</option>
                            <option value="ditolak" <?= $pesanan['status_pembayaran'] === 'ditolak' ? 'selected' : '' ?>>Bukti Ditolak / Tidak Valid</option>
                        </select>
                    </div>
                    
                    <div class="admin-form-group">
                        <label for="status_pesanan" class="admin-form-label">Status Pesanan *</label>
                        <select id="status_pesanan" name="status_pesanan" class="admin-form-select" required onchange="cekStatusPesanan(this.value)">
                            <option value="pending" <?= $pesanan['status_pesanan'] === 'pending' ? 'selected' : '' ?>>Pending (Antrian Baru)</option>
                            <option value="diproses" <?= $pesanan['status_pesanan'] === 'diproses' ? 'selected' : '' ?>>Sedang Diproses Dapur</option>
                            <option value="selesai" <?= $pesanan['status_pesanan'] === 'selesai' ? 'selected' : '' ?>>Selesai (Diambil/Terkirim)</option>
                            <option value="dibatalkan" <?= $pesanan['status_pesanan'] === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                        </select>
                    </div>

                    <script>
                    function cekStatusPesanan(status) {
                        if (status === 'selesai') {
                            document.getElementById('status_pembayaran').value = 'lunas';
                        }
                    }
                    </script>
                    
                    <button type="submit" name="update_status" class="btn-admin btn-admin-success" style="width: 100%; justify-content: center; padding: 12px; margin-top: 10px;">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Status Baru
                    </button>
                </form>
            </div>
            
            <!-- Bukti Pembayaran Panel -->
            <div class="panel" style="text-align: center;">
                <h3 class="panel-title" style="margin-bottom: 20px; border-bottom: 2px solid var(--border-color); padding-bottom: 8px; text-align: left;"><i class="fa-solid fa-file-invoice"></i> Bukti Transfer</h3>
                
                <?php if (!empty($pesanan['bukti_pembayaran']) && file_exists("../bukti_bayar/" . $pesanan['bukti_pembayaran'])): ?>
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 12px; text-align: left;"><i class="fa-solid fa-circle-info"></i> Klik gambar di bawah untuk memperbesar:</p>
                    <img src="../bukti_bayar/<?= htmlspecialchars($pesanan['bukti_pembayaran']) ?>" 
                         alt="Bukti Bayar" 
                         class="receipt-preview"
                         style="max-width: 100%; max-height: 250px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);"
                         onclick="openReceiptModal('../bukti_bayar/<?= htmlspecialchars($pesanan['bukti_pembayaran']) ?>')">
                <?php else: ?>
                    <div style="padding: 40px 10px; color: var(--text-muted);">
                        <i class="fa-regular fa-image" style="font-size: 3rem; color: #bdc3c7; margin-bottom: 15px;"></i>
                        <p style="font-size: 0.88rem;">Belum ada bukti pembayaran yang diunggah oleh pemesan.</p>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
        
    </div>

    <!-- JavaScript Modal Helper -->
    <div class="modal-overlay" id="receipt-modal">
        <div class="modal-content-container">
            <div class="modal-header">
                <h4>Bukti Transfer Pembayaran</h4>
                <span class="modal-close-btn" onclick="closeReceiptModal()">&times;</span>
            </div>
            <div class="modal-body">
                <img id="modal-img" src="" alt="Bukti Transfer Zoomed">
            </div>
        </div>
    </div>

    <script>
    function openReceiptModal(imgSrc) {
        var modal = document.getElementById('receipt-modal');
        var modalImg = document.getElementById('modal-img');
        modal.style.display = 'flex';
        modalImg.src = imgSrc;
    }

    function closeReceiptModal() {
        var modal = document.getElementById('receipt-modal');
        modal.style.display = 'none';
    }

    // Tutup saat klik di overlay luar modal
    window.onclick = function(event) {
        var modal = document.getElementById('receipt-modal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    </script>

<!-- --------------------------------------------------------
     VIEW: LIST PESANAN (DEFAULT)
     -------------------------------------------------------- -->
<?php else: ?>
    <?php
    // Bangun query list dengan filter
    $where_clause = "";
    if ($filter_status === 'pending') {
        $where_clause = "WHERE status_pesanan = 'pending'";
    } elseif ($filter_status === 'diproses') {
        $where_clause = "WHERE status_pesanan = 'diproses'";
    } elseif ($filter_status === 'selesai') {
        $where_clause = "WHERE status_pesanan = 'selesai'";
    } elseif ($filter_status === 'dibatalkan') {
        $where_clause = "WHERE status_pesanan = 'dibatalkan'";
    } elseif ($filter_status === 'verifikasi') {
        $where_clause = "WHERE status_pembayaran = 'menunggu_konfirmasi'";
    }
    
    $query_orders = "SELECT * FROM pesanan $where_clause ORDER BY id DESC";
    $result_orders = mysqli_query($conn, $query_orders);
    ?>
    
    <!-- Filter Bar -->
    <div class="filter-card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                <span style="font-weight: 600; color: var(--dark); font-size: 0.92rem; margin-right: 5px;">Filter Status:</span>
                <a href="kelola_pesanan.php?filter=semua" class="btn-admin <?= $filter_status == 'semua' ? 'btn-admin-primary' : 'btn-admin-primary' ?>" style="<?= $filter_status == 'semua' ? '' : 'background:#e2e5e7; color:var(--text);' ?>">Semua</a>
                <a href="kelola_pesanan.php?filter=selesai" class="btn-admin" style="<?= $filter_status == 'selesai' ? 'background:var(--success); color:#white;' : 'background:#e2e5e7; color:var(--text);' ?>">Selesai</a>
                <a href="kelola_pesanan.php?filter=pending" class="btn-admin" style="<?= $filter_status == 'pending' ? 'background:var(--primary); color:#white;' : 'background:#e2e5e7; color:var(--text);' ?>">Baru (Pending)</a>
                <a href="kelola_pesanan.php?filter=diproses" class="btn-admin" style="<?= $filter_status == 'diproses' ? 'background:var(--info); color:#white;' : 'background:#e2e5e7; color:var(--text);' ?>">Diproses</a>
                <a href="kelola_pesanan.php?filter=selesai" class="btn-admin" style="<?= $filter_status == 'selesai' ? 'background:var(--success); color:#white;' : 'background:#e2e5e7; color:var(--text);' ?>">Selesai</a>
                <a href="kelola_pesanan.php?filter=dibatalkan" class="btn-admin" style="<?= $filter_status == 'dibatalkan' ? 'background:var(--danger); color:#white;' : 'background:#e2e5e7; color:var(--text);' ?>">Dibatalkan</a>
            </div>
            
            <p style="font-size: 0.88rem; color: var(--text-muted);">Menampilkan <strong><?= mysqli_num_rows($result_orders) ?></strong> pesanan ditemukan.</p>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title"><i class="fa-solid fa-list-check"></i> Daftar Semua Transaksi Pemesanan</h2>
        </div>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Kode Pesanan</th>
                        <th>Nama Pemesan</th>
                        <th>Telepon/WA</th>
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
                    if (mysqli_num_rows($result_orders) > 0) {
                        while ($row = mysqli_fetch_assoc($result_orders)) {
                    ?>
                            <tr>
                                <td style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($row['kode_pesanan']) ?></td>
                                <td style="font-weight: 600;"><?= htmlspecialchars($row['nama_pemesan']) ?></td>
                                <td><?= htmlspecialchars($row['no_telepon']) ?></td>
                                <td style="text-transform: uppercase; font-weight: 600; font-size: 0.8rem;"><?= htmlspecialchars($row['tipe_pesanan']) ?></td>
                                <td style="font-weight: 700; color: var(--dark);">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
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
                                        echo '<span class="badge badge-pending">Pending</span>';
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
                                    <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                        <a href="kelola_pesanan.php?action=detail&id=<?= $row['id'] ?>" class="btn-admin btn-admin-primary" style="padding: 6px 10px; font-size: 0.8rem;" title="Detail">
                                            <i class="fa-solid fa-eye"></i> Detail
                                        </a>
                                        <a href="kelola_pesanan.php?action=delete&id=<?= $row['id'] ?>" class="btn-admin btn-admin-danger" style="padding: 6px 10px; font-size: 0.8rem;" onclick="return confirm('Apakah Anda yakin ingin menghapus pesanan ini beserta seluruh rinciannya?');" title="Hapus">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                    <?php 
                        }
                    } else {
                        echo '<tr><td colspan="9" style="text-align: center; padding: 25px; color: var(--text-muted);"><i class="fa-solid fa-triangle-exclamation"></i> Tidak ada data pesanan sesuai kriteria.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php 
// Include Layout Footer
include_once 'templates/footer.php';
?>
