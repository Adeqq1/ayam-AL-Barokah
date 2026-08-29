<?php
// File Helper Fungsi Keranjang Belanja Ayam Penyet Al-Barokah

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Mendapatkan detail item di keranjang belanja berdasarkan data di database.
 * 
 * @param mysqli $conn Koneksi database
 * @return array List item keranjang beserta detail menu dan subtotal
 */
function get_keranjang_detail($conn) {
    $items = [];
    
    if (empty($_SESSION['keranjang'])) {
        return $items;
    }
    
    // Ambil semua ID menu di keranjang
    $ids = array_keys($_SESSION['keranjang']);
    // Filter ID agar bertipe integer (menghindari SQL Injection)
    $ids_safe = array_map('intval', $ids);
    $ids_string = implode(',', $ids_safe);
    
    if (empty($ids_string)) {
        return $items;
    }
    
    // Query data menu dari database
    $query = "SELECT * FROM menu WHERE id IN ($ids_string)";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $id = $row['id'];
            $jumlah = $_SESSION['keranjang'][$id];
            $subtotal = $row['harga'] * $jumlah;
            
            $items[] = [
                'id' => $id,
                'nama_menu' => $row['nama_menu'],
                'deskripsi' => $row['deskripsi'],
                'harga' => $row['harga'],
                'kategori' => $row['kategori'],
                'foto' => $row['foto'],
                'status' => $row['status'],
                'jumlah' => $jumlah,
                'subtotal' => $subtotal
            ];
        }
    }
    
    return $items;
}

/**
 * Menghitung total harga belanja di keranjang.
 * 
 * @param array $items Array item dari get_keranjang_detail()
 * @return int Total belanja
 */
function get_keranjang_total($items) {
    $total = 0;
    foreach ($items as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}
?>
