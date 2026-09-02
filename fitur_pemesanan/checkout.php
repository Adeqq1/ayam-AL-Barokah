<?php
// Halaman Checkout Pemesanan Ayam Penyet Al-Barokah
require_once '../config/database.php';
require_once '../includes/fungsi-keranjang.php';

/** @var mysqli $conn */

// Inisialisasi session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ================================================================
// PROTEKSI: Wajibkan login sebelum checkout
// ================================================================
if (!isset($_SESSION['user_id'])) {
    // Redirect ke halaman login dengan pesan
    header("Location: ../login.php?redirect=" . urlencode('fitur_pemesanan/checkout.php'));
    exit;
}

// Cek apakah keranjang kosong
$keranjang_items = get_keranjang_detail($conn);
if (empty($keranjang_items)) {
    header("Location: keranjang.php");
    exit;
}

$total_harga = get_keranjang_total($keranjang_items);
$error_msg = "";

// --------------------------------------------------------
// Handle Pembuatan Pesanan (POST request)
// --------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pemesan = mysqli_real_escape_string($conn, trim($_POST['nama_pemesan']));
    $no_telepon = mysqli_real_escape_string($conn, trim($_POST['no_telepon']));
    $tipe_pesanan = mysqli_real_escape_string($conn, trim($_POST['tipe_pesanan']));
    $alamat = isset($_POST['alamat']) ? mysqli_real_escape_string($conn, trim($_POST['alamat'])) : '';
    
    // Validasi input wajib
    if (empty($nama_pemesan) || empty($no_telepon) || empty($tipe_pesanan)) {
        $error_msg = "Semua kolom bertanda bintang (*) wajib diisi!";
    } elseif ($tipe_pesanan === 'delivery' && empty($alamat)) {
        $error_msg = "Alamat pengiriman wajib diisi jika memilih metode Delivery!";
    } else {
        // Tipe pesanan bukan delivery, kosongkan alamat
        if ($tipe_pesanan !== 'delivery') {
            $alamat = null;
        }
        
        // Generate kode pesanan unik dengan suffix random
        $random_suffix = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $kode_pesanan = "ALB-" . date('ymd') . "-" . $random_suffix;
        
        // KODE BARU (user_id selalu ada karena login wajib):
        $user_id = intval($_SESSION['user_id']);
        
        // Hitung Grand Total (tambahkan ongkir jika Delivery)
        $grand_total = $total_harga;
        if ($tipe_pesanan === 'delivery') {
            $grand_total += 10000;
        }
        
        // Mulai database transaction untuk konsistensi data
        mysqli_begin_transaction($conn);
        
        try {
            // 1. Insert data utama ke tabel pesanan
            $query_pesanan = "INSERT INTO pesanan (kode_pesanan, user_id, nama_pemesan, no_telepon, alamat, tipe_pesanan, total_harga, status_pembayaran, status_pesanan) 
                              VALUES ('$kode_pesanan', $user_id, '$nama_pemesan', '$no_telepon', " . ($alamat === null ? "NULL" : "'$alamat'") . ", '$tipe_pesanan', $grand_total, 'belum_bayar', 'pending')";

            
            if (!mysqli_query($conn, $query_pesanan)) {
                throw new Exception("Gagal menyimpan data utama pesanan: " . mysqli_error($conn));
            }
            
            // Dapatkan ID Pesanan yang baru dimasukkan
            $pesanan_id = mysqli_insert_id($conn);
            
            // 2. Loop dan insert item ke tabel detail_pesanan
            foreach ($keranjang_items as $item) {
                $menu_id = intval($item['id']);
                $jumlah = intval($item['jumlah']);
                $subtotal = intval($item['subtotal']);
                
                $query_detail = "INSERT INTO detail_pesanan (pesanan_id, menu_id, jumlah, subtotal) 
                                 VALUES ($pesanan_id, $menu_id, $jumlah, $subtotal)";
                
                if (!mysqli_query($conn, $query_detail)) {
                    throw new Exception("Gagal menyimpan detail pesanan: " . mysqli_error($conn));
                }
            }
            
            // Commit transaksi jika sukses semua
            mysqli_commit($conn);
            
            // Kosongkan keranjang belanja
            unset($_SESSION['keranjang']);
            
            // Redirect ke halaman konfirmasi bayar
            header("Location: konfirmasi-bayar.php?kode=" . $kode_pesanan);
            exit;
            
        } catch (Exception $e) {
            // Batalkan semua query jika ada kegagalan
            mysqli_rollback($conn);
            $error_msg = "Terjadi kesalahan saat memproses pesanan Anda. Silakan coba kembali. (Error: " . $e->getMessage() . ")";
        }
    }
}

// Include Header
include_once '../includes/header.php';
?>

<div class="container">



    <div class="checkout-grid">
        
        <!-- Form Pengisian Data Pelanggan -->
        <div class="checkout-card">
            <h2 class="checkout-card-title">Formulir Data Diri & Checkout</h2>
            
            <?php if (!empty($error_msg)): ?>
                <div style="background: #fdf2f2; color: #ec5b5b; border: 1px solid #fbd5d5; padding: 15px; border-radius: var(--radius-md); margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?= htmlspecialchars($error_msg) ?></span>
                </div>
            <?php endif; ?>
            
            <form action="checkout.php" method="POST" id="checkout-form">
                
                <div class="form-group">
                    <label for="nama_pemesan" class="form-label">Nama Lengkap *</label>
                    <input type="text" id="nama_pemesan" name="nama_pemesan" class="form-control"
                           placeholder="Contoh: Budi Santoso"
                           value="<?= isset($_POST['nama_pemesan']) ? htmlspecialchars($_POST['nama_pemesan']) : (isset($_SESSION['nama_lengkap']) ? htmlspecialchars($_SESSION['nama_lengkap']) : '') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="no_telepon" class="form-label">Nomor Telepon / WhatsApp *</label>
                    <input type="tel" id="no_telepon" name="no_telepon" class="form-control" placeholder="Contoh: 0812XXXXXXXX" value="<?= isset($_POST['no_telepon']) ? htmlspecialchars($_POST['no_telepon']) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="tipe_pesanan" class="form-label">Metode Pemesanan *</label>
                    <select id="tipe_pesanan" name="tipe_pesanan" class="form-select" required>
                        <option value="dine_in" <?= (isset($_POST['tipe_pesanan']) && $_POST['tipe_pesanan'] === 'dine_in') ? 'selected' : '' ?>>Makan Di Tempat (Dine In)</option>
                        <option value="take_away" <?= (isset($_POST['tipe_pesanan']) && $_POST['tipe_pesanan'] === 'take_away') ? 'selected' : '' ?>>Bawa Pulang (Take Away)</option>
                        <option value="delivery" <?= (isset($_POST['tipe_pesanan']) && $_POST['tipe_pesanan'] === 'delivery') ? 'selected' : '' ?>>Pesan Antar (Delivery)</option>
                    </select>
                </div>
                
                <!-- Input Alamat (Hanya muncul jika memilih Delivery) -->
                <div class="form-group address-toggle-section" id="address-section">
                    <label for="alamat" class="form-label">Alamat Pengiriman Lengkap *</label>
                    <textarea id="alamat" name="alamat" class="form-textarea" placeholder="Tuliskan nama jalan, nomor rumah, RT/RW, kelurahan/kecamatan, dan patokan tujuan."><?= isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : '' ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; margin-top: 20px; font-size: 1.05rem;">
                    <i class="fa-solid fa-circle-check"></i> Buat Pesanan Sekarang
                </button>
            </form>
        </div>
        
        <!-- Ringkasan Pemesanan -->
        <div class="checkout-card" style="background: #fafaf9;">
            <h2 class="checkout-card-title" style="font-size: 1.4rem;">Tinjau Pesanan Anda</h2>
            
            <div style="margin-bottom: 25px;">
                <?php foreach ($keranjang_items as $item): ?>
                    <div class="summary-item-row">
                        <div>
                            <span class="summary-item-name"><?= htmlspecialchars($item['nama_menu']) ?></span>
                            <span class="summary-item-qty">x<?= $item['jumlah'] ?></span>
                        </div>
                        <strong style="color: var(--dark);">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="summary-row" style="margin-bottom: 10px; font-size: 0.95rem;">
                <span>Subtotal Menu</span>
                <strong>Rp <?= number_format($total_harga, 0, ',', '.') ?></strong>
            </div>
            
            <div class="summary-row" style="margin-bottom: 15px; font-size: 0.95rem;">
                <span>Biaya Pengiriman</span>
                <span id="delivery-cost-text" style="color: var(--text-muted); font-weight: 500;">GRATIS</span>
            </div>
            
            <div class="summary-row total">
                <span>Total Bayar</span>
                <span id="grand-total-text">Rp <?= number_format($total_harga, 0, ',', '.') ?></span>
            </div>
            
            <div style="background: #f0f7f4; color: #2e7d32; padding: 15px; border-radius: var(--radius-md); margin-top: 25px; border: 1px solid #d4edda; font-size: 0.85rem;">
                <p style="font-weight: 600; margin-bottom: 5px;"><i class="fa-solid fa-info-circle"></i> Info Pembayaran:</p>
                <p>Setelah menekan tombol buat pesanan, Anda akan diarahkan ke halaman pembayaran untuk melakukan transfer dan mengunggah bukti bayar agar segera dikonfirmasi oleh admin kami.</p>
            </div>
        </div>
        
    </div>
</div>

<script>
// Logic JavaScript untuk Menyembunyikan/Menampilkan field alamat
document.addEventListener("DOMContentLoaded", function() {
    var tipePesananSelect = document.getElementById('tipe_pesanan');
    var addressSection = document.getElementById('address-section');
    var alamatTextarea = document.getElementById('alamat');
    var deliveryCostText = document.getElementById('delivery-cost-text');
    var grandTotalText = document.getElementById('grand-total-text');
    
    var subtotal = <?= $total_harga ?>;
    
    function handleOrderTypeChange() {
        if (tipePesananSelect.value === 'delivery') {
            addressSection.style.display = 'block';
            alamatTextarea.setAttribute('required', 'required');
            
            // Simulasikan ongkir delivery Rp 10.000 (Opsional)
            deliveryCostText.textContent = "Rp 10.000";
            deliveryCostText.style.color = "var(--primary)";
            
            var grandTotal = subtotal + 10000;
            grandTotalText.textContent = "Rp " + grandTotal.toLocaleString('id-ID');
        } else {
            addressSection.style.display = 'none';
            alamatTextarea.removeAttribute('required');
            
            deliveryCostText.textContent = "GRATIS";
            deliveryCostText.style.color = "var(--text-muted)";
            
            grandTotalText.textContent = "Rp " + subtotal.toLocaleString('id-ID');
        }
    }
    
    // Panggil saat load pertama kali
    handleOrderTypeChange();
    
    // Tambahkan event listener change
    tipePesananSelect.addEventListener('change', handleOrderTypeChange);
});
</script>

<?php 
// Include Footer
include_once '../includes/footer.php';
?>
<script src="../assets/js/validation.js"></script>
