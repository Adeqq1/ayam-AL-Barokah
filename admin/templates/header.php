<?php
// Template Header Admin Ayam Penyet Al-Barokah
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi halaman admin: hanya admin yang boleh masuk
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin</title>
    <!-- Local Fonts & FontAwesome -->
    <link rel="stylesheet" href="../assets/css/fonts.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
    <!-- Admin stylesheet -->
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <!-- Hamburger Button for Mobile -->
    <button class="sidebar-toggle-btn" id="sidebarToggle" title="Buka/Tutup Menu">
        <i class="fa-solid fa-bars"></i>
    </button>
    <!-- Overlay for mobile sidebar -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="admin-wrapper">
