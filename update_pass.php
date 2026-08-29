<?php
// Script Perbaikan Password Admin - Ayam Penyet Al-Barokah
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Update Password Admin</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .box { padding: 15px 20px; border-radius: 6px; margin-bottom: 15px; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td, th { border: 1px solid #ddd; padding: 8px 12px; text-align: left; }
        th { background: #f8f9fa; }
        .btn { display: inline-block; padding: 10px 20px; background: #d35400; color: white;
               text-decoration: none; border-radius: 4px; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>
    <h2>🔧 Update Password Admin</h2>

    <?php
    // 1. Cek data admin saat ini di database
    $cek = mysqli_query($conn, "SELECT id, username, password, nama_lengkap, role FROM users WHERE username = 'admin'");
    if ($cek && mysqli_num_rows($cek) > 0) {
        $admin = mysqli_fetch_assoc($cek);
        echo "<div class='box info'>";
        echo "<strong>Data admin yang ada di database saat ini:</strong>";
        echo "<table>";
        echo "<tr><th>ID</th><td>" . $admin['id'] . "</td></tr>";
        echo "<tr><th>Username</th><td>" . $admin['username'] . "</td></tr>";
        echo "<tr><th>Password (di DB)</th><td><code>" . htmlspecialchars($admin['password']) . "</code></td></tr>";
        echo "<tr><th>Nama</th><td>" . $admin['nama_lengkap'] . "</td></tr>";
        echo "<tr><th>Role</th><td>" . $admin['role'] . "</td></tr>";
        echo "</table>";
        echo "</div>";

        // 2. Update password ke admin1234 (plain text)
        $update = mysqli_query($conn, "UPDATE users SET password = 'admin1234' WHERE username = 'admin'");
        if ($update) {
            echo "<div class='box success'>";
            echo "<strong>✅ Berhasil!</strong> Password admin telah diubah menjadi: <code>admin1234</code><br>";
            echo "Silakan login menggunakan:<br>";
            echo "• Username: <strong>admin</strong><br>";
            echo "• Password: <strong>admin1234</strong>";
            echo "</div>";
        } else {
            echo "<div class='box danger'>❌ Gagal update: " . mysqli_error($conn) . "</div>";
        }
    } else {
        // Admin tidak ada, buat baru
        $insert = mysqli_query($conn, "INSERT INTO users (username, password, nama_lengkap, role) VALUES ('admin', 'admin1234', 'Admin Al-Barokah', 'admin')");
        if ($insert) {
            echo "<div class='box success'>";
            echo "<strong>✅ Admin baru berhasil dibuat!</strong><br>";
            echo "• Username: <strong>admin</strong><br>";
            echo "• Password: <strong>admin1234</strong>";
            echo "</div>";
        } else {
            echo "<div class='box danger'>❌ Gagal membuat admin: " . mysqli_error($conn) . "</div>";
        }
    }
    ?>

    <a href="login.php" class="btn">🔑 Buka Halaman Login</a>
    <br><br>
    <small style="color: #888;">⚠️ Setelah berhasil login, hapus file <code>update_pass.php</code> ini dari folder proyek Anda.</small>
</body>
</html>
