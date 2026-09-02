<?php
// Halaman Keranjang Belanja Ayam Penyet Al-Barokah
require_once '../config/database.php';
require_once '../includes/fungsi-keranjang.php';

/** @var mysqli $conn */

// Inisialisasi session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --------------------------------------------------------
// Handle Aksi Keranjang (GET request)
// --------------------------------------------------------
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $menu_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($action === 'increase' && $menu_id > 0) {
        // Cek ketersediaan menu sebelum ditambah
        $query_check = "SELECT status FROM menu WHERE id = $menu_id";
        $result_check = mysqli_query($conn, $query_check);
        if ($result_check && $row = mysqli_fetch_assoc($result_check)) {
            if ($row['status'] === 'tersedia') {
                if (isset($_SESSION['keranjang'][$menu_id])) {
                    $_SESSION['keranjang'][$menu_id]++;
                } else {
                    $_SESSION['keranjang'][$menu_id] = 1;
                }
            }
        }
    }
    
    elseif ($action === 'decrease' && $menu_id > 0) {
        if (isset($_SESSION['keranjang'][$menu_id])) {
            $_SESSION['keranjang'][$menu_id]--;
            // Jika jumlah 0 atau kurang, hapus item dari keranjang
            if ($_SESSION['keranjang'][$menu_id] <= 0) {
                unset($_SESSION['keranjang'][$menu_id]);
            }
        }
    }
    
    elseif ($action === 'delete' && $menu_id > 0) {
        if (isset($_SESSION['keranjang'][$menu_id])) {
            unset($_SESSION['keranjang'][$menu_id]);
        }
    }
    
    elseif ($action === 'clear') {
        $_SESSION['keranjang'] = [];
    }
    
    // Redirect ke halaman keranjang bersih tanpa parameter query untuk estetika URL
    header("Location: keranjang.php");
    exit;
}

// Mendapatkan detail menu dalam keranjang
$keranjang_items = get_keranjang_detail($conn);
$total_harga = get_keranjang_total($keranjang_items);

// Include Header
include_once '../includes/header.php';
?>

<div class="container" style="min-height: 70vh;">
    <?php if (empty($keranjang_items)): ?>
        <!-- Tampilan Keranjang Kosong -->
        <div class="empty-cart-container">
            <div class="empty-cart-icon">
                <i class="fa-solid fa-basket-shopping"></i>
            </div>
            <h2>Keranjang Anda Masih Kosong</h2>
            <p>Sepertinya Anda belum memilih hidangan lezat kami. Yuk, pilih menu Ayam Penyet favorit Anda sekarang!</p>
            <a href="../index.php#menu" class="btn btn-primary">
                <i class="fa-solid fa-utensils"></i> Lihat Daftar Menu
            </a>
        </div>
    <?php else: ?>
        <!-- Tampilan Layout Keranjang Belanja -->
        <div class="cart-layout">
            
            <!-- Tabel Item Keranjang -->
            <div class="cart-table-container">
                <div class="cart-header">
                    <h2>Keranjang Belanja</h2>
                    <a href="keranjang.php?action=clear" class="btn-clear-cart" onclick="return confirm('Apakah Anda yakin ingin mengosongkan seluruh keranjang belanja?')">
                        <i class="fa-solid fa-trash-can"></i> Kosongkan Keranjang
                    </a>
                </div>
                
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th>Harga Satuan</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($keranjang_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="cart-item-detail">
                                        <img src="../assets/images/<?= htmlspecialchars($item['foto']) ?>" 
                                             alt="<?= htmlspecialchars($item['nama_menu']) ?>" 
                                             class="cart-item-img"
                                             onerror="this.src='../assets/images/default-menu.jpg'">
                                        <div class="cart-item-info">
                                            <h4><?= htmlspecialchars($item['nama_menu']) ?></h4>
                                            <span><?= htmlspecialchars($item['kategori']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-weight: 500;">Rp <?= number_format($item['harga'], 0, ',', '.') ?></span>
                                </td>
                                <td>
                                    <div class="qty-control">
                                        <!-- Tombol Kurangi Qty -->
                                        <a href="keranjang.php?action=decrease&id=<?= $item['id'] ?>" class="qty-btn" style="display: flex; align-items: center; justify-content: center;">-</a>
                                        <input type="text" class="qty-input" value="<?= $item['jumlah'] ?>" readonly>
                                        <!-- Tombol Tambah Qty -->
                                        <a href="keranjang.php?action=increase&id=<?= $item['id'] ?>" class="qty-btn" style="display: flex; align-items: center; justify-content: center; <?= $item['status'] !== 'tersedia' ? 'pointer-events: none; opacity: 0.5;' : '' ?>">+</a>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-weight: 600; color: var(--primary);">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                                </td>
                                <td>
                                    <!-- Tombol Hapus Baris -->
                                    <a href="keranjang.php?action=delete&id=<?= $item['id'] ?>" class="btn-delete-item" title="Hapus Item" onclick="return confirm('Hapus <?= htmlspecialchars($item['nama_menu']) ?> dari keranjang?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="margin-top: 20px; display: flex; justify-content: space-between;">
                    <a href="../index.php#menu" class="btn btn-outline" style="padding: 8px 20px;">
                        <i class="fa-solid fa-arrow-left"></i> Tambah Menu Lain
                    </a>
                </div>
            </div>
            
            <!-- Ringkasan Pembayaran -->
            <div class="cart-summary">
                <h3>Ringkasan Belanja</h3>
                
                <div class="summary-row">
                    <span>Total Item</span>
                    <strong style="color: var(--dark);"><?= count($keranjang_items) ?> Menu</strong>
                </div>
                
                <div class="summary-row">
                    <span>Biaya Layanan</span>
                    <span style="color: var(--success); font-weight: 600;">GRATIS</span>
                </div>
                
                <div class="summary-row total">
                    <span>Total Harga</span>
                    <span>Rp <?= number_format($total_harga, 0, ',', '.') ?></span>
                </div>
                
                <!-- Tombol Checkout -->
                <a href="checkout.php" class="btn btn-primary btn-checkout">
                    <i class="fa-solid fa-credit-card"></i> Lanjut Ke Checkout
                </a>
                
                <div style="margin-top: 15px; text-align: center;">
                    <p style="font-size: 0.8rem; color: var(--text-muted);">
                        <i class="fa-solid fa-shield-halved" style="color: var(--success);"></i> Transaksi Aman & Terpercaya
                    </p>
                </div>
            </div>
            
        </div>
    <?php endif; ?>
</div>

<?php 
// Include Footer
include_once '../includes/footer.php';
?>
