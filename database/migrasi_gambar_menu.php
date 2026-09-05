<?php
/**
 * Script Migrasi Foto Menu Terbaru
 * Ayam Penyet Al-Barokah
 * Dapat dijalankan via CLI: php database/migrasi_gambar_menu.php
 * Atau via browser oleh Admin.
 */

require_once __DIR__ . '/../config/database.php';

echo "<pre>\n";
echo "=== MEMULAI SINKRONISASI FOTO MENU ===\n\n";

// 1. Bersihkan duplikat jika ada (id > 20)
$del_duplikat = mysqli_query($conn, "DELETE FROM menu WHERE id > 20");
if ($del_duplikat) {
    echo "✓ Pembersihan data menu duplikat berhasil.\n";
    @mysqli_query($conn, "ALTER TABLE menu AUTO_INCREMENT = 21");
}

// 2. Mapping ID menu ke foto terbaru
$mapping = [
    1  => 'menu_1788590605.jpg', // Ayam Penyet Biasa
    2  => 'menu_1788590594.jpg', // Ayam Penyet Jumbo
    3  => 'default-menu.jpg',     // Bebek Penyet
    4  => 'menu_1788590520.jpg', // Tahu Tempe Penyet
    5  => 'menu_1788590474.jpg', // Ayam Bakar Kecap
    6  => 'menu_1788590458.jpg', // Lele Penyet Sambal Bawang
    7  => 'default-menu.jpg',     // Ayam Geprek Mozarella
    8  => 'menu_1788590363.jpg', // Nasi Goreng Spesial
    9  => 'default-menu.jpg',     // Paket Ayam Penyet Ekonomis
    10 => 'default-menu.jpg',     // Paket Bebek Penyet Komplit
    11 => 'default-menu.jpg',     // Paket Jumbo Keluarga
    12 => 'menu_1788590707.jpg', // Es Teh Manis
    13 => 'menu_1788590694.jpg', // Es Jeruk Peras
    14 => 'menu_1788590676.jpg', // Es Campur Al-Barokah
    15 => 'menu_1788590652.jpg', // Es Kelapa Muda Segar
    16 => 'menu_1788590632.jpg', // Jus Alpukat Susu
    17 => 'menu_1788590618.jpg', // Es Lemon Tea
    18 => 'menu_1788590940.jpg', // Kerupuk Udang Crispy
    19 => 'menu_1788590844.jpg', // Pisang Goreng Kremes
    20 => 'menu_1788590813.jpg', // Tahu Crispy Pedas
];

$success_count = 0;
foreach ($mapping as $id => $foto) {
    $sql = "UPDATE menu SET foto = '$foto' WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        $success_count++;
    } else {
        echo "✗ Gagal update ID $id: " . mysqli_error($conn) . "\n";
    }
}

echo "✓ Berhasil memperbarui $success_count item menu dengan foto terbaru.\n\n";
echo "=== SINKRONISASI SELESAI ===\n";
echo "</pre>";
