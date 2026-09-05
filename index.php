<?php
// Koneksi ke database dan inisialisasi session
require_once 'config/database.php';

/** @var mysqli $conn */

// Cek jika session belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proses tambah ke keranjang atau beli langsung
if (isset($_GET['action']) && ($_GET['action'] == 'add' || $_GET['action'] == 'buy_now')) {
    $menu_id = intval($_GET['id']);

    // Verifikasi menu ada dan tersedia
    $query_check = "SELECT * FROM menu WHERE id = $menu_id AND status = 'tersedia'";
    $result_check = mysqli_query($conn, $query_check);

    if ($result_check && mysqli_num_rows($result_check) > 0) {
        if (!isset($_SESSION['keranjang'])) {
            $_SESSION['keranjang'] = [];
        }

        // Tambahkan jumlah item
        if (isset($_SESSION['keranjang'][$menu_id])) {
            $_SESSION['keranjang'][$menu_id]++;
        } else {
            $_SESSION['keranjang'][$menu_id] = 1;
        }

        // Redirect
        if ($_GET['action'] == 'buy_now') {
            header("Location: fitur_pemesanan/checkout.php");
        } else {
            header("Location: index.php?status=success_add#menu");
        }
        exit;
    }
}

// Ambil filter kategori dari URL (untuk default active button)
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : 'semua';

// Query SEMUA menu — filter dilakukan di sisi client via JavaScript
$query_menu = "SELECT * FROM menu ORDER BY status ASC, id DESC";
$result_menu = mysqli_query($conn, $query_menu);


// Include Header
include_once 'includes/header.php';
?>

<!-- Style khusus untuk notifikasi alert di halaman index -->
<style>
    .alert-container {
        position: fixed;
        top: 90px;
        right: 20px;
        z-index: 1000;
        max-width: 400px;
    }

    .alert {
        background: #27ae60;
        color: white;
        padding: 15px 25px;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-lg);
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 500;
    }

    .alert-close {
        margin-left: auto;
        cursor: pointer;
        opacity: 0.8;
    }

    .alert-close:hover {
        opacity: 1;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
</style>

<!-- Alert Notification -->
<?php if (isset($_GET['status']) && $_GET['status'] == 'success_add'): ?>
    <div class="alert-container" id="success-alert">
        <div class="alert">
            <i class="fa-solid fa-circle-check"></i>
            <span>Menu berhasil ditambahkan ke keranjang!</span>
            <i class="fa-solid fa-xmark alert-close" onclick="document.getElementById('success-alert').style.display='none'"></i>
        </div>
    </div>
    <script>
        // Auto-hide alert setelah 3 detik
        setTimeout(function() {
            var alert = document.getElementById('success-alert');
            if (alert) alert.style.display = 'none';
        }, 3000);
    </script>
<?php endif; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-content">
            <h1>Selamat Datang di <span> Ayam Penyet</span> Al-Barokah</h1>
            <p>Rasakan kelezatan ayam goreng renyah bumbu rempah pilihan yang dipenyet dengan sambal korek super pedas yang dibuat segar setiap hari.</p>
            <div class="hero-buttons">
                <a href="#menu" class="btn btn-primary">
                    <i class="fa-solid fa-list-ul"></i> Lihat Menu Kami
                </a>
                <a href="fitur_pemesanan/keranjang.php" class="btn btn-outline">
                    <i class="fa-solid fa-basket-shopping"></i> Keranjang Belanja
                </a>
            </div>
        </div>
        <div class="hero-image-wrapper">
            <div class="hero-image-circle"></div>
            <img src="assets/images/hero-ayam-penyet.png" alt="Ayam Penyet Al-Barokah" class="hero-img" onerror="this.src='assets/images/default-menu.jpg'">
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section" id="menu">
    <div class="container">
        <h2 class="section-title">Menu Favorit Kami</h2>
        <p class="section-subtitle">Pilih hidangan lezat dan nikmat untuk memanjakan lidah Anda hari ini</p>

        <div class="category-filters">
            <button class="filter-btn <?= $kategori_filter == 'semua' ? 'active' : '' ?>" data-kategori="semua">Semua Menu</button>
            <button class="filter-btn <?= $kategori_filter == 'makanan' ? 'active' : '' ?>" data-kategori="makanan">Makanan</button>
            <button class="filter-btn <?= $kategori_filter == 'minuman' ? 'active' : '' ?>" data-kategori="minuman">Minuman</button>
            <button class="filter-btn <?= $kategori_filter == 'paket' ? 'active' : '' ?>" data-kategori="paket">Paket Hemat</button>
            <button class="filter-btn <?= $kategori_filter == 'cemilan' ? 'active' : '' ?>" data-kategori="cemilan">Cemilan</button>
        </div>
    </div>
</section>

<!-- Menu Grid Section -->
<section class="menu-section">
    <div class="container">
        <div class="menu-grid">
            <?php
            if ($result_menu && mysqli_num_rows($result_menu) > 0) {
                while ($row = mysqli_fetch_assoc($result_menu)) {
                    $is_tersedia = ($row['status'] === 'tersedia');
            ?>
                    <div class="menu-card" data-kategori="<?= htmlspecialchars($row['kategori']) ?>">
                        <div class="menu-img-container">
                            <span class="menu-badge"><?= htmlspecialchars($row['kategori']) ?></span>
                            <?php if (!$is_tersedia): ?>
                                <span class="menu-status-badge habis">Habis</span>
                            <?php endif; ?>

                            <!-- Menampilkan foto menu, fallback ke ilustrasi default jika foto kosong atau error -->
                            <img src="<?= get_menu_image_src($row['foto'], 'assets/images/') ?>"
                                alt="<?= htmlspecialchars($row['nama_menu']) ?>"
                                class="menu-card-img"
                                onerror="this.src='assets/images/default-menu.jpg'">
                        </div>

                        <div class="menu-info">
                            <h3 class="menu-title"><?= htmlspecialchars($row['nama_menu']) ?></h3>
                            <p class="menu-description"><?= htmlspecialchars($row['deskripsi']) ?></p>

                            <div class="menu-footer">
                                <span class="menu-price">Rp <?= number_format($row['harga'], 0, ',', '.') ?></span>
                                <div style="display: flex; gap: 8px;">
                                    <a href="index.php?action=buy_now&id=<?= $row['id'] ?>"
                                        class="btn-buy-now"
                                        style="background: var(--primary); color: white; border: none; padding: 8px 12px; border-radius: 8px; text-decoration: none; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; font-weight: 600; <?= !$is_tersedia ? 'pointer-events: none; opacity: 0.5;' : '' ?>"
                                        title="<?= $is_tersedia ? 'Beli Langsung' : 'Stok Habis' ?>">
                                        Bayar
                                    </a>
                                    <a href="index.php?action=add&id=<?= $row['id'] ?>"
                                        class="btn-add-cart"
                                        <?= !$is_tersedia ? 'style="pointer-events: none; opacity: 0.5;"' : '' ?>
                                        title="<?= $is_tersedia ? 'Tambah ke Keranjang' : 'Stok Habis' ?>">
                                        <i class="fa-solid <?= $is_tersedia ? 'fa-plus' : 'fa-ban' ?>"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-muted);">';
                echo '<i class="fa-solid fa-cookie-bite" style="font-size: 3rem; margin-bottom: 15px; color: #bdc3c7;"></i>';
                echo '<p>Tidak ada menu yang tersedia untuk kategori ini.</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</section>

<?php
// Include Footer
include_once 'includes/footer.php';
?>