<?php
// Template Sidebar Admin Ayam Penyet Al-Barokah
$active_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        Al-Barokah <span>Admin</span>
    </div>
    
    <ul class="sidebar-menu">
        <li class="<?= $active_page === 'index.php' ? 'active' : '' ?>">
            <a href="index.php">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
        </li>
        <li class="<?= $active_page === 'kelola_menu.php' ? 'active' : '' ?>">
            <a href="kelola_menu.php">
                <i class="fa-solid fa-utensils"></i> Kelola Menu
            </a>
        </li>
        <li class="<?= $active_page === 'kelola_pesanan.php' ? 'active' : '' ?>">
            <a href="kelola_pesanan.php">
                <i class="fa-solid fa-receipt"></i> Kelola Pesanan
            </a>
        </li>
        <li class="<?= $active_page === 'laporan.php' ? 'active' : '' ?>">
            <a href="laporan.php">
                <i class="fa-solid fa-chart-line"></i> Laporan Penjualan
            </a>
        </li>
        <li class="<?= $active_page === 'db_error.php' ? 'active' : '' ?>">
            <a href="../db_error.php" target="_blank">
                <i class="fa-solid fa-database"></i> Status & Log DB
            </a>
        </li>
        <li style="margin-top: 20px;">
            <a href="../index.php" target="_blank" style="background: rgba(255,255,255,0.05); color: #ecf0f1;">
                <i class="fa-solid fa-globe"></i> Lihat Toko Utama
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <a href="../logout.php" class="btn-logout" onclick="return confirm('Apakah Anda yakin ingin keluar dari panel admin?')">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</aside>

<main class="main-content">
    <header class="admin-header">
        <h1>
            <?php
            if ($active_page === 'index.php') echo 'Dashboard';
            elseif ($active_page === 'kelola_menu.php') echo 'Kelola Menu Makanan';
            elseif ($active_page === 'kelola_pesanan.php') echo 'Kelola Pesanan & Transaksi';
            elseif ($active_page === 'laporan.php') echo 'Laporan Hasil Penjualan';
            else echo 'Dashboard Admin';
            ?>
        </h1>
        <div class="admin-profile">
            <i class="fa-solid fa-user-shield"></i>
            <span>Halo, Admin Anjas</span>
        </div>
    </header>
