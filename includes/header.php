<?php
// Start session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Menentukan root path agar include dan asset tetap aman diakses dari subfolder
$root_path = "";
$current_dir = dirname($_SERVER['PHP_SELF']);
$dir_parts = explode('/', trim($current_dir, '/'));

// Jika dalam subfolder admin, fitur_pemesanan, atau pelanggan
if (in_array('admin', $dir_parts) || in_array('fitur_pemesanan', $dir_parts) || in_array('pelanggan', $dir_parts)) {
    $root_path = "../";
}

// Menghitung jumlah item di keranjang
$keranjang_count = 0;
if (isset($_SESSION['keranjang']) && is_array($_SESSION['keranjang'])) {
    foreach ($_SESSION['keranjang'] as $qty) {
        $keranjang_count += $qty;
    }
}

// Info user yang sedang login
$is_logged_in  = isset($_SESSION['user_id']);
$is_admin      = $is_logged_in && $_SESSION['role'] === 'admin';
$is_pelanggan  = $is_logged_in && $_SESSION['role'] === 'pelanggan';
$display_name  = $is_logged_in ? htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayam Penyet Al-Barokah - Lezat, Pedas, Nikmat</title>
    <meta name="description" content="Pesan Ayam Penyet Al-Barokah terlezat secara online. Tersedia layanan Dine-In, Take Away, dan Delivery. Nikmati pedas mantap sambal kami!">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="<?= $root_path ?>assets/css/style.css?v=2.0">
    <style>
        /* Navbar User Pill */
        .nav-user-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #fff3e0, #ffe0b2);
            color: var(--primary);
            font-weight: 700;
            font-size: 0.88rem;
            padding: 6px 14px;
            border-radius: 20px;
            border: 1.5px solid rgba(211,84,0,0.2);
            text-decoration: none;
            transition: var(--transition);
        }
        .nav-user-pill:hover {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }
        .nav-user-pill i { font-size: 0.8rem; }

        /* Tombol Login di navbar (untuk guest) */
        .btn-nav-login {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.88rem;
            background: transparent;
            border: 1.5px solid var(--primary);
            color: var(--primary);
            text-decoration: none;
            transition: var(--transition);
        }
        .btn-nav-login:hover {
            background: var(--primary);
            color: #fff;
        }
        .btn-nav-register {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.88rem;
            background: var(--primary);
            color: #fff;
            text-decoration: none;
            transition: var(--transition);
        }
        .btn-nav-register:hover {
            background: var(--primary-hover);
            box-shadow: 0 4px 12px rgba(211,84,0,0.3);
        }
    </style>
</head>
<body>

<header>
    <nav class="navbar" id="navbar">
        <div class="container navbar-container">
            <a href="<?= $root_path ?>index.php" class="logo">
                <i class="fa-solid fa-fire-burner"></i> Ayam Penyet <span>Al-Barokah</span>
            </a>

            <ul class="nav-links">
                <li><a href="<?= $root_path ?>index.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php' && !in_array('fitur_pemesanan', $dir_parts) && !in_array('pelanggan', $dir_parts)) ? 'active' : '' ?>">Home</a></li>
                <li><a href="<?= $root_path ?>index.php#menu" class="nav-link">Menu</a></li>
                <li><a href="<?= $root_path ?>fitur_pemesanan/keranjang.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'keranjang.php') !== false ? 'active' : '' ?>">Keranjang</a></li>

                <?php if ($is_admin): ?>
                    <li><a href="<?= $root_path ?>admin/index.php" class="nav-link <?= in_array('admin', $dir_parts) ? 'active' : '' ?>">Dashboard Admin</a></li>
                <?php elseif ($is_pelanggan): ?>
                    <li><a href="<?= $root_path ?>pelanggan/index.php" class="nav-link <?= in_array('pelanggan', $dir_parts) ? 'active' : '' ?>">Pesanan Saya</a></li>
                <?php endif; ?>
            </ul>

            <div class="nav-actions">
                <!-- Keranjang icon -->
                <a href="<?= $root_path ?>fitur_pemesanan/keranjang.php" class="cart-icon" id="cart-icon" title="Keranjang Belanja">
                    <i class="fa-solid fa-basket-shopping"></i>
                    <?php if ($keranjang_count > 0): ?>
                        <span class="cart-badge" id="cart-badge"><?= $keranjang_count ?></span>
                    <?php endif; ?>
                </a>

                <?php if ($is_logged_in): ?>
                    <!-- Dropdown user -->
                    <a href="<?= $root_path ?><?= $is_admin ? 'admin/index.php' : 'pelanggan/index.php' ?>" class="nav-user-pill" title="Dashboard saya">
                        <i class="fa-solid fa-circle-user"></i>
                        <?= $display_name ?>
                    </a>
                    <a href="<?= $root_path ?>logout.php" class="btn btn-outline" style="padding:8px 16px; font-size:0.85rem;" title="Keluar" onclick="return confirm('Yakin ingin keluar?')">
                        <i class="fa-solid fa-right-from-bracket"></i> Keluar
                    </a>
                <?php else: ?>
                    <!-- Tombol Login & Daftar untuk tamu -->
                    <a href="<?= $root_path ?>login.php" class="btn-nav-login" id="btn-masuk">
                        <i class="fa-solid fa-right-to-bracket"></i> Masuk
                    </a>
                    <a href="<?= $root_path ?>register.php" class="btn-nav-register" id="btn-daftar">
                        <i class="fa-solid fa-user-plus"></i> Daftar
                    </a>
                <?php endif; ?>

                <!-- Hamburger Button (mobile only) -->
                <button class="hamburger-btn" id="hamburgerBtn" aria-label="Buka Menu" title="Menu Navigasi">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>
</header>

<!-- Mobile Nav Overlay -->
<div class="nav-overlay" id="navOverlay"></div>

<script src="<?= $root_path ?>assets/js/main.js" defer></script>
