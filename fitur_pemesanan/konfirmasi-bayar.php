<?php
// Halaman Konfirmasi Pembayaran Ayam Penyet Al-Barokah
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ================================================================
// PROTEKSI KEAMANAN: Hanya user yang memiliki pesanan yang boleh mengakses
// ================================================================

// 1. Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    // User belum login → redirect ke login
    $redirect_url = 'fitur_pemesanan/konfirmasi-bayar.php?kode=' . urlencode($_GET['kode'] ?? '');
    header("Location: ../login.php?redirect=" . urlencode($redirect_url));
    exit;
}

// 2. Ambil user_id dari session dan role
$current_user_id = intval($_SESSION['user_id']);
$current_role = $_SESSION['role'] ?? '';

$kode_pesanan = isset($_GET['kode']) ? mysqli_real_escape_string($conn, trim($_GET['kode'])) : '';

if (empty($kode_pesanan)) {
    header("Location: ../index.php");
    exit;
}

// Fetch data pesanan berdasarkan kode
$query = "SELECT * FROM pesanan WHERE kode_pesanan = '$kode_pesanan'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo "<h3>Pesanan dengan kode: " . htmlspecialchars($kode_pesanan) . " tidak ditemukan!</h3>";
    echo "<a href='../index.php'>Kembali ke Beranda</a>";
    exit;
}

$pesanan = mysqli_fetch_assoc($result);

// ================================================================
// 3. Verifikasi kepemilikan pesanan
// ================================================================
// Admin boleh mengakses semua pesanan (untuk keperluan verifikasi pembayaran)
// Pelanggan hanya boleh mengakses pesanan miliknya sendiri
if ($current_role !== 'admin') {
    // Cek apakah pesanan ini milik user yang sedang login
    if ($pesanan['user_id'] === null || intval($pesanan['user_id']) !== $current_user_id) {
        // AKSES DITOLAK — bukan pemilik pesanan ini
        http_response_code(403);
        echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>Akses Ditolak</title>";
        echo "<link rel='stylesheet' href='../assets/css/fonts.css'>";
        echo "<link rel='stylesheet' href='../assets/css/style.css'>";
        echo "<link rel='stylesheet' href='../assets/vendor/fontawesome/css/all.min.css'></head>";
        echo "<body style='min-height:100vh; display:flex; align-items:center; justify-content:center; background:#fdf2f2;'>";
        echo "<div style='text-align:center; padding:40px; background:#fff; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.1); max-width:450px;'>";
        echo "<i class='fa-solid fa-shield-halved' style='font-size:4rem; color:#e74c3c; margin-bottom:20px;'></i>";
        echo "<h2 style='color:#c0392b; margin-bottom:10px;'>Akses Ditolak</h2>";
        echo "<p style='color:#666; margin-bottom:20px;'>Anda tidak memiliki izin untuk mengakses halaman konfirmasi pembayaran ini. Halaman ini hanya dapat diakses oleh pemilik pesanan.</p>";
        echo "<a href='../pelanggan/index.php' style='display:inline-block; padding:12px 24px; background:#d35400; color:#fff; border-radius:8px; text-decoration:none; font-weight:600;'><i class='fa-solid fa-arrow-left'></i> Kembali ke Dashboard</a>";
        echo "</div></body></html>";
        exit;
    }
}

$success_msg = "";
$error_msg = "";

// --------------------------------------------------------
// Handle Upload Bukti Bayar
// --------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_bayar'])) {
    
    // TAMBAHKAN: Double-check kepemilikan pesanan saat upload (mencegah manipulasi POST)
    if ($current_role !== 'admin' && intval($pesanan['user_id']) !== $current_user_id) {
        $error_msg = "Anda tidak memiliki izin untuk mengunggah bukti bayar pada pesanan ini.";
    } else {
        $file = $_FILES['bukti_bayar'];
    
    // Validasi error upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_msg = "Terjadi kesalahan saat mengunggah berkas.";
    } else {
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png'];
        
        if (!in_array($file_ext, $allowed_exts)) {
            $error_msg = "Format file tidak didukung! Harap unggah berkas berformat JPG, JPEG, atau PNG.";
        } elseif ($file_size > 2 * 1024 * 1024) { // Max 2MB
            $error_msg = "Ukuran berkas terlalu besar! Maksimal ukuran file adalah 2 MB.";
        } else {
            // Generate nama file baru: KODE_PESANAN.ext
            // Ganti tanda minus/spasi agar nama file aman
            $safe_kode = str_replace('-', '_', $kode_pesanan);
            $new_file_name = "receipt_" . $safe_kode . "_" . time() . "." . $file_ext;
            $upload_dir = "../bukti_bayar/";
            
            // Buat folder jika belum ada (safety check)
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $dest_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $dest_path)) {
                // Update database
                $query_update = "UPDATE pesanan SET 
                                 bukti_pembayaran = '$new_file_name', 
                                 status_pembayaran = 'lunas',
                                 status_pesanan = 'selesai'
                                 WHERE kode_pesanan = '$kode_pesanan'";
                
                if (mysqli_query($conn, $query_update)) {
                    $success_msg = "Bukti pembayaran berhasil diunggah! Pembayaran lunas dan pesanan selesai.";
                    // Refresh data pesanan
                    $result_refresh = mysqli_query($conn, $query);
                    $pesanan = mysqli_fetch_assoc($result_refresh);
                } else {
                    $error_msg = "Gagal memperbarui status pembayaran di database.";
                }
            } else {
                $error_msg = "Gagal memindahkan file ke folder tujuan. Pastikan folder bukti_bayar memiliki izin tulis.";
            }
            }
        }
    }
}

// Fetch items detail pesanan untuk direview di halaman ini
$pesanan_id = $pesanan['id'];
$query_items = "SELECT dp.*, m.nama_menu, m.kategori FROM detail_pesanan dp 
                JOIN menu m ON dp.menu_id = m.id 
                WHERE dp.pesanan_id = $pesanan_id";
$result_items = mysqli_query($conn, $query_items);
$items = [];
if ($result_items) {
    while ($row = mysqli_fetch_assoc($result_items)) {
        $items[] = $row;
    }
}

// Include Header
include_once '../includes/header.php';
?>

<div class="container" style="padding: 50px 0 80px; min-height: 80vh;">
    <div style="max-width: 750px; margin: 0 auto;">
        
        <!-- Status Pemesanan Alert -->
        <?php if (!empty($success_msg)): ?>
            <div style="background-color: #e8f8f5; border: 1px solid #d1f2eb; color: var(--success); padding: 18px; border-radius: var(--radius-md); margin-bottom: 25px; font-weight: 500; display: flex; align-items: center; gap: 12px; font-size: 0.95rem;">
                <i class="fa-solid fa-circle-check" style="font-size: 1.25rem;"></i>
                <span><?= htmlspecialchars($success_msg) ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_msg)): ?>
            <div style="background-color: #fdedec; border: 1px solid #fadbd8; color: var(--danger); padding: 18px; border-radius: var(--radius-md); margin-bottom: 25px; font-weight: 500; display: flex; align-items: center; gap: 12px; font-size: 0.95rem;">
                <i class="fa-solid fa-circle-exclamation" style="font-size: 1.25rem;"></i>
                <span><?= htmlspecialchars($error_msg) ?></span>
            </div>
        <?php endif; ?>

        <div style="background: #fff; border-radius: var(--radius-lg); padding: 35px; box-shadow: var(--shadow-md); border: 1px solid var(--border-color);">
            
            <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid var(--border-color); padding-bottom: 20px;">
                <h2 style="font-family: var(--font-heading); color: var(--dark); font-size: 2rem; margin-bottom: 5px;">Konfirmasi Pembayaran</h2>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Kode Pesanan Anda: <strong style="color: var(--primary); font-size: 1.05rem; letter-spacing: 0.5px;"><?= htmlspecialchars($pesanan['kode_pesanan']) ?></strong></p>
            </div>
            
            <!-- Informasi Pemesan -->
            <div style="margin-bottom: 25px; padding: 15px 20px; background: #fdfdfd; border-left: 4px solid var(--primary); border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                <h4 style="font-size: 1.1rem; color: var(--dark); margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-user" style="color: var(--primary);"></i> Informasi Pemesan
                </h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 3px;">Nama Lengkap:</p>
                        <p style="font-weight: 600; color: var(--dark);"><?= htmlspecialchars($pesanan['nama_pemesan']) ?></p>
                    </div>
                    <div>
                        <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 3px;">No. WhatsApp / Telepon:</p>
                        <p style="font-weight: 600; color: var(--dark);"><?= htmlspecialchars($pesanan['no_telepon']) ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Ringkasan Singkat Pembayaran -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 35px; background: #faf9f6; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <div>
                    <p style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-bottom: 5px;">Total Tagihan:</p>
                    <p style="font-size: 1.5rem; font-weight: 800; color: var(--primary);">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></p>
                </div>
                <div>
                    <p style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-bottom: 5px;">Status Pembayaran:</p>
                    <?php if ($pesanan['status_pembayaran'] === 'belum_bayar'): ?>
                        <span class="badge badge-cancelled" style="font-size: 0.85rem; padding: 6px 14px;"><i class="fa-solid fa-clock"></i> Belum Dibayar</span>
                    <?php elseif ($pesanan['status_pembayaran'] === 'menunggu_konfirmasi'): ?>
                        <span class="badge badge-payment-waiting" style="font-size: 0.85rem; padding: 6px 14px;"><i class="fa-solid fa-spinner fa-spin"></i> Menunggu Konfirmasi</span>
                    <?php elseif ($pesanan['status_pembayaran'] === 'lunas'): ?>
                        <span class="badge badge-completed" style="font-size: 0.85rem; padding: 6px 14px;"><i class="fa-solid fa-circle-check"></i> Pembayaran Lunas</span>
                    <?php elseif ($pesanan['status_pembayaran'] === 'ditolak'): ?>
                        <span class="badge badge-payment-rejected" style="font-size: 0.85rem; padding: 6px 14px;"><i class="fa-solid fa-circle-xmark"></i> Pembayaran Ditolak</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr; gap: 30px;">
                
                <!-- Rekening Pembayaran -->
                <?php if ($pesanan['status_pembayaran'] === 'belum_bayar' || $pesanan['status_pembayaran'] === 'ditolak'): ?>
                    <div>
                        <h4 style="font-family: var(--font-heading); color: var(--dark); font-size: 1.3rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-building-columns" style="color: var(--primary);"></i> Rekening Tujuan Transfer / E-Wallet
                        </h4>
                        <p style="font-size: 0.92rem; color: var(--text-muted); margin-bottom: 20px;">Silakan lakukan transfer ke salah satu rekening Bank/E-Wallet di bawah ini sebesar nominal tagihan di atas:</p>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div style="background: #fff; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
                                <h5 style="color: var(--dark); font-weight: 700; font-size: 1rem; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                                    <span style="background: #eaf2f8; color: #118ee9; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 800;">DANA</span> DANA
                                </h5>
                                <p style="font-size: 1.15rem; font-weight: 700; color: var(--dark); margin-bottom: 3px; letter-spacing: 0.5px;">0812-3456-7890</p>
                                <p style="font-size: 0.82rem; color: var(--text-muted);">a.n. Ayam Penyet Al-Barokah</p>
                            </div>
                            
                            <div style="background: #fff; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
                                <h5 style="color: var(--dark); font-weight: 700; font-size: 1rem; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                                    <span style="background: #fff5eb; color: #ff5722; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 800;">SEABANK</span> Bank SeaBank
                                </h5>
                                <p style="font-size: 1.15rem; font-weight: 700; color: var(--dark); margin-bottom: 3px; letter-spacing: 0.5px;">9012-3456-7890</p>
                                <p style="font-size: 0.82rem; color: var(--text-muted);">a.n. Ayam Penyet Al-Barokah</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Upload Form -->
                    <div style="margin-top: 15px; border-top: 1px solid var(--border-color); padding-top: 30px;">
                        <h4 style="font-family: var(--font-heading); color: var(--dark); font-size: 1.3rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-file-arrow-up" style="color: var(--primary);"></i> Unggah Bukti Transfer
                        </h4>
                        
                        <form action="konfirmasi-bayar.php?kode=<?= urlencode($kode_pesanan) ?>" method="POST" enctype="multipart/form-data">
                            <div class="form-group" style="background: #faf9f6; border: 2px dashed var(--border-color); padding: 25px; border-radius: var(--radius-md); text-align: center; cursor: pointer; position: relative;">
                                <i class="fa-solid fa-image" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 12px;"></i>
                                <p style="font-weight: 600; color: var(--dark); font-size: 0.95rem; margin-bottom: 5px;">Pilih File Gambar Bukti Transfer</p>
                                <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 15px;">Mendukung format JPG, JPEG, PNG (Maks. 2 MB)</p>
                                
                                <input type="file" name="bukti_bayar" id="bukti_bayar_input" style="cursor: pointer; width: 100%;" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; margin-top: 10px;">
                                <i class="fa-solid fa-upload"></i> Kirim Bukti Transfer
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Tampilan Jika Sudah Upload / Sudah Lunas -->
                    <div style="text-align: center; padding: 20px 0;">
                        <?php if ($pesanan['status_pembayaran'] === 'menunggu_konfirmasi'): ?>
                            <i class="fa-solid fa-business-time" style="font-size: 4rem; color: var(--warning); margin-bottom: 20px;"></i>
                            <h4 style="font-family: var(--font-heading); color: var(--dark); font-size: 1.5rem; margin-bottom: 8px;">Menunggu Konfirmasi Pembayaran</h4>
                            <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 500px; margin: 0 auto 20px;">Bukti pembayaran Anda sudah tersimpan. Tim admin kami akan segera melakukan pengecekan rekening. Status pesanan Anda saat ini adalah: <strong><?= htmlspecialchars(strtoupper($pesanan['status_pesanan'])) ?></strong>.</p>
                        <?php elseif ($pesanan['status_pembayaran'] === 'lunas'): ?>
                            <i class="fa-solid fa-circle-check" style="font-size: 4rem; color: var(--success); margin-bottom: 20px;"></i>
                            <h4 style="font-family: var(--font-heading); color: var(--dark); font-size: 1.5rem; margin-bottom: 8px;">Pembayaran Terverifikasi!</h4>
                            <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 500px; margin: 0 auto 20px;">Terima kasih atas pembayaran Anda! Pesanan Anda saat ini sedang diproses. Silakan tunjukkan kode pesanan Anda saat dine-in/ambil di outlet, atau tunggu kurir kami mengantar hidangan ke rumah Anda.</p>
                        <?php endif; ?>
                        
                        <?php if (!empty($pesanan['bukti_pembayaran'])): ?>
                            <div style="margin-top: 20px;">
                                <p style="font-size: 0.88rem; font-weight: 600; color: var(--dark); margin-bottom: 8px;">Bukti yang telah diunggah:</p>
                                <img src="../bukti_bayar/<?= htmlspecialchars($pesanan['bukti_pembayaran']) ?>" 
                                     alt="Bukti Transfer" 
                                     style="max-width: 250px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Detail Item Pesanan -->
                <div style="border-top: 1px solid var(--border-color); padding-top: 30px; margin-top: 15px;">
                    <h4 style="font-family: var(--font-heading); color: var(--dark); font-size: 1.3rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-list-check" style="color: var(--primary);"></i> Rincian Menu Dipesan
                    </h4>
                    
                    <div style="background: #faf9f6; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-weight: 600;">
                                    <th style="padding-bottom: 10px;">Menu</th>
                                    <th style="padding-bottom: 10px; text-align: center;">Jumlah</th>
                                    <th style="padding-bottom: 10px; text-align: right;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr style="border-bottom: 1px solid rgba(0,0,0,0.03);">
                                        <td style="padding: 10px 0; color: var(--dark); font-weight: 500;"><?= htmlspecialchars($item['nama_menu']) ?></td>
                                        <td style="padding: 10px 0; text-align: center; color: var(--text-muted);"><?= $item['jumlah'] ?>x</td>
                                        <td style="padding: 10px 0; text-align: right; color: var(--dark); font-weight: 600;">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <!-- Delivery Fee Info -->
                                <?php if ($pesanan['tipe_pesanan'] === 'delivery'): ?>
                                    <tr style="border-bottom: 1px solid rgba(0,0,0,0.03);">
                                        <td style="padding: 10px 0; color: var(--dark); font-weight: 500;">Biaya Pengantaran (Delivery)</td>
                                        <td style="padding: 10px 0; text-align: center; color: var(--text-muted);">1x</td>
                                        <td style="padding: 10px 0; text-align: right; color: var(--dark); font-weight: 600;">Rp 10.000</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <div style="display: flex; justify-content: space-between; margin-top: 15px; border-top: 2px solid var(--border-color); padding-top: 12px; font-size: 1.05rem; font-weight: 700; color: var(--primary);">
                            <span>Total Pembayaran</span>
                            <span>Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <a href="../index.php" class="btn btn-outline">
                        <i class="fa-solid fa-house"></i> Kembali Ke Beranda
                    </a>
                </div>
                
            </div>
            
        </div>
    </div>
</div>

<?php 
// Include Footer
include_once '../includes/footer.php';
?>
