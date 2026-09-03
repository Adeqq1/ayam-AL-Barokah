-- Database: db_pemesanan
-- Dibuat untuk sistem pemesanan Ayam Penyet Al-Barokah

CREATE DATABASE IF NOT EXISTS `db_pemesanan`;
USE `db_pemesanan`;

-- --------------------------------------------------------
-- 1. Tabel Users
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `role` ENUM('admin', 'pelanggan') NOT NULL DEFAULT 'pelanggan',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin (password: admin1234)
-- CATATAN: Password default di bawah ini adalah plain-text untuk keperluan initial seed saja.
-- Setelah diinsert, jalankan script migrasi (database/migrasi_hash_password.php) 
-- untuk mengubah semua password menjadi bcrypt hash.
INSERT INTO `users` (`username`, `password`, `nama_lengkap`, `role`) VALUES
('admin', 'admin1234', 'Admin Al-Barokah', 'admin')
ON DUPLICATE KEY UPDATE `password` = 'admin1234';

-- Insert akun pelanggan sample untuk demo & pengujian
INSERT INTO `users` (`username`, `password`, `nama_lengkap`, `role`) VALUES
('pelanggan1', 'pelanggan123', 'Budi Santoso', 'pelanggan'),
('pelanggan2', 'pelanggan123', 'Siti Rahayu', 'pelanggan'),
('demo',       'demo1234',    'Demo Pelanggan', 'pelanggan')
ON DUPLICATE KEY UPDATE `password` = VALUES(`password`), `nama_lengkap` = VALUES(`nama_lengkap`);

-- --------------------------------------------------------
-- 2. Tabel Menu
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `menu` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_menu` VARCHAR(100) NOT NULL,
  `deskripsi` TEXT DEFAULT NULL,
  `harga` INT NOT NULL,
  `kategori` ENUM('makanan', 'minuman', 'paket', 'cemilan') NOT NULL,
  `foto` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('tersedia', 'habis') NOT NULL DEFAULT 'tersedia',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data menu sampel (20 menu)
INSERT INTO `menu` (`nama_menu`, `deskripsi`, `harga`, `kategori`, `foto`, `status`) VALUES
-- MAKANAN (8 item)
('Ayam Penyet Biasa', 'Ayam goreng renyah yang digeprek dengan sambal khas Al-Barokah yang pedas mantap, disajikan lengkap dengan tahu, tempe, dan lalapan segar.', 18000, 'makanan', 'ayam_penyet_biasa.jpg', 'tersedia'),
('Ayam Penyet Jumbo', 'Porsi puas dengan ayam ukuran jumbo dibalur sambal bawang pedas level tinggi, tahu, tempe, lalapan, dan taburan kremesan renyah.', 23000, 'makanan', 'ayam_penyet_jumbo.jpg', 'tersedia'),
('Bebek Penyet', 'Bebek goreng empuk dan gurih dengan bumbu meresap, dipenyet dengan sambal korek super pedas beserta lalapan.', 28000, 'makanan', 'bebek_penyet.jpg', 'tersedia'),
('Tahu Tempe Penyet', 'Tahu dan tempe goreng gurih yang dipenyet bersama sambal terasi matang dan lalapan segar.', 10000, 'makanan', 'tahu_tempe_penyet.jpg', 'tersedia'),
('Ayam Bakar Kecap', 'Ayam bakar dengan olesan kecap manis dan bumbu rempah pilihan, dibakar hingga harum kecokelatan. Disajikan dengan nasi, sambal, dan lalapan.', 25000, 'makanan', 'ayam_bakar.jpg', 'tersedia'),
('Lele Penyet Sambal Bawang', 'Lele goreng garing dan renyah dipenyet dengan sambal bawang pedas khas Al-Barokah, tahu, tempe, dan lalapan segar.', 15000, 'makanan', 'lele_penyet.jpg', 'tersedia'),
('Ayam Geprek Mozarella', 'Ayam geprek crispy berlumur sambal pedas dengan lelehan keju mozzarella di atasnya. Perpaduan pedas dan gurih yang menggugah selera.', 28000, 'makanan', 'ayam_geprek_mozarella.jpg', 'tersedia'),
('Nasi Goreng Spesial', 'Nasi goreng bumbu rahasia Al-Barokah dengan telur ceplok, ayam suwir, sayuran, dan kerupuk udang. Porsi besar dan mengenyangkan.', 18000, 'makanan', 'nasi_goreng.jpg', 'tersedia'),
-- PAKET (3 item)
('Paket Ayam Penyet Ekonomis', 'Paket hemat sudah termasuk nasi putih hangat, ayam penyet ukuran sedang, tahu, tempe, lalapan, dan es teh manis.', 22000, 'paket', 'paket_ekonomis.jpg', 'tersedia'),
('Paket Bebek Penyet Komplit', 'Paket lengkap bebek penyet dengan nasi putih, sambal korek, lalapan segar, tahu, tempe, dan minuman pilihan.', 35000, 'paket', 'paket_bebek.jpg', 'tersedia'),
('Paket Jumbo Keluarga', 'Paket makan keluarga isi 4 porsi: 3 ayam penyet + 1 bebek penyet, 4 nasi putih, lalapan, tahu, tempe, dan 4 minuman. Hemat dan kenyang!', 95000, 'paket', 'paket_jumbo.jpg', 'tersedia'),
-- MINUMAN (6 item)
('Es Teh Manis', 'Teh manis dingin yang menyegarkan dahaga setelah menyantap hidangan pedas.', 5000, 'minuman', 'es_teh.jpg', 'tersedia'),
('Es Jeruk Peras', 'Jeruk peras asli dengan rasa asam manis yang segar, disajikan dengan es batu.', 7000, 'minuman', 'es_jeruk.jpg', 'tersedia'),
('Es Campur Al-Barokah', 'Es campur spesial dengan isian kelapa muda, alpukat, nangka, kolang-kaling, jelly, disiram susu kental manis dan sirup merah.', 12000, 'minuman', 'es_campur.jpg', 'tersedia'),
('Es Kelapa Muda Segar', 'Kelapa muda pilihan langsung dari petani, segar dan manis alami. Disajikan dengan es batu dan isian kelapa mudanya.', 10000, 'minuman', 'es_kelapa.jpg', 'tersedia'),
('Jus Alpukat Susu', 'Jus alpukat kental lembut berpadu susu segar, disajikan dingin dengan es batu dan siraman cokelat. Sehat dan mengenyangkan.', 12000, 'minuman', 'jus_alpukat.jpg', 'tersedia'),
('Es Lemon Tea', 'Teh lemon dingin menyegarkan dengan rasa asam manis yang pas. Cocok menemani hidangan pedas Al-Barokah.', 8000, 'minuman', 'es_lemon_tea.jpg', 'tersedia'),
-- CEMILAN (3 item)
('Kerupuk Udang Crispy', 'Kerupuk udang asli goreng garing dan renyah. Cocok sebagai teman makan atau camilan santai.', 5000, 'cemilan', 'kerupuk_udang.jpg', 'tersedia'),
('Pisang Goreng Kremes', 'Pisang kepok manis digoreng dengan balutan kremesan renyah. Luar garing, dalam lembut dan manis.', 8000, 'cemilan', 'pisang_goreng.jpg', 'tersedia'),
('Tahu Crispy Pedas', 'Tahu sumedang goreng krispy berlumur bumbu pedas manis. Camilan favorit yang bikin nagih.', 7000, 'cemilan', 'tahu_crispy.jpg', 'tersedia')
ON DUPLICATE KEY UPDATE `nama_menu` = `nama_menu`;

-- --------------------------------------------------------
-- 3. Tabel Pesanan (Transaksi Utama)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pesanan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_pesanan` VARCHAR(20) NOT NULL UNIQUE,
  `user_id` INT DEFAULT NULL,
  `nama_pemesan` VARCHAR(100) NOT NULL,
  `no_telepon` VARCHAR(20) NOT NULL,
  `alamat` TEXT DEFAULT NULL, -- NULL jika Dine In atau Take Away
  `tipe_pesanan` ENUM('dine_in', 'take_away', 'delivery') NOT NULL DEFAULT 'dine_in',
  `total_harga` INT NOT NULL,
  `bukti_pembayaran` VARCHAR(255) DEFAULT NULL, -- Nama file bukti bayar yang diupload
  `status_pembayaran` ENUM('belum_bayar', 'menunggu_konfirmasi', 'lunas', 'ditolak') NOT NULL DEFAULT 'belum_bayar',
  `status_pesanan` ENUM('pending', 'diproses', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'pending',
  `tanggal_pesanan` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Tabel Detail Pesanan (Relasi Item yang Dipesan)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `detail_pesanan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pesanan_id` INT NOT NULL,
  `menu_id` INT NOT NULL,
  `jumlah` INT NOT NULL,
  `subtotal` INT NOT NULL,
  FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
