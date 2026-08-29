<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika keranjang belanja kosong, arahkan kembali ke menu utama untuk memilih makanan
if (empty($_SESSION['keranjang'])) {
    header("Location: ../index.php#menu");
} else {
    header("Location: keranjang.php");
}
exit;
?>
