<footer>
    <div class="container footer-grid">
        <div class="footer-about">
            <h3>Ayam Penyet Al-Barokah</h3>
            <p>Menyajikan hidangan Ayam Penyet berkualitas dengan perpaduan bumbu rempah tradisional dan sambal khas Al-Barokah yang pedasnya menggugah selera.</p>
            <p style="margin-top: 15px;"><i class="fa-regular fa-clock" style="color: var(--accent);"></i> <strong>Jam Operasional:</strong><br>Setiap Hari: 10.00 - 22.00 WIB</p>
        </div>
        <div class="footer-links">
            <h4>Navigasi</h4>
            <ul>
                <li><a href="<?= $root_path ?>index.php">Home</a></li>
                <li><a href="<?= $root_path ?>index.php#menu">Menu Kami</a></li>
                <li><a href="<?= $root_path ?>fitur_pemesanan/keranjang.php">Keranjang Belanja</a></li>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                    <li><a href="<?= $root_path ?>admin/index.php">Dashboard Admin</a></li>
                    <li><a href="<?= $root_path ?>logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="footer-contact">
            <h4>Kontak &amp; Lokasi</h4>
            <p><i class="fa-solid fa-location-dot" style="color: var(--accent);"></i> Jl. Rangkayo Hitam, Bungo, Jambi</p>
            <p><i class="fa-solid fa-phone" style="color: var(--accent);"></i> 0812-3456-789</p>
            <p><i class="fa-solid fa-envelope" style="color: var(--accent);"></i> info@ayampenyetalbarokah.com</p>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container" style="position: relative;">
            <p>&copy; <?= date('Y') ?> <strong>Ayam Penyet Al-Barokah</strong>. Dibuat dengan cinta &amp; pedas. All rights reserved.</p>

            <?php if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'): ?>
            <!-- Tombol Login Admin Tersembunyi (titik hitam) -->
            <a href="<?= $root_path ?>login.php"
               id="admin-dot-btn"
               title="Login Admin"
               style="
                   position: absolute;
                   bottom: 0;
                   right: 0;
                   width: 10px;
                   height: 10px;
                   background: #1a1a1a;
                   border-radius: 50%;
                   display: inline-block;
                   opacity: 0.25;
                   transition: opacity 0.3s ease, transform 0.3s ease;
                   cursor: pointer;
               "
               onmouseover="this.style.opacity='1'; this.style.transform='scale(1.8)';"
               onmouseout="this.style.opacity='0.25'; this.style.transform='scale(1)';"
            ></a>
            <?php endif; ?>
        </div>
    </div>
</footer>
</body>
</html>
