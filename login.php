<?php
// Halaman Login Ayam Penyet Al-Barokah (Admin & Pelanggan)
require_once 'config/database.php';

/** @var mysqli $conn */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: pelanggan/index.php");
    }
    exit;
}

$error_msg = "";
$success_msg = "";

// Pesan sukses setelah registrasi
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $success_msg = "Pendaftaran akun sukses! Silakan masuk menggunakan akun baru Anda.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error_msg = "Username dan Password wajib diisi!";
    } else {
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            // Verifikasi password menggunakan bcrypt hash
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
                $_SESSION['role'] = $row['role'];
                
                // Redirect berdasarkan role
                if ($row['role'] === 'admin') {
                    header("Location: admin/index.php");
                } else {
                    // Pelanggan: kembali ke halaman sebelumnya atau dashboard pelanggan
                    $redirect_to = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : 'pelanggan/index.php';
                    header("Location: " . $redirect_to);
                }
                exit;
            } else {
                $error_msg = "Password yang Anda masukkan salah!";
            }
        } else {
            $error_msg = "Username tidak terdaftar!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Ayam Penyet Al-Barokah</title>
    <!-- Local Fonts & FontAwesome -->
    <link rel="stylesheet" href="assets/css/fonts.css">
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-body {
            background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-lg);
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo {
            font-family: var(--font-heading);
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 8px;
        }
        .login-logo span {
            color: var(--dark);
        }
        .login-header p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        .login-error {
            background-color: #fdf2f2;
            color: #e74c3c;
            padding: 12px 15px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            border: 1px solid #fbd5d5;
        }
        .form-group-login {
            margin-bottom: 20px;
            position: relative;
        }
        .form-group-login label {
            display: block;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        .form-input-login {
            width: 100%;
            padding: 12px 16px 12px 42px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-family: var(--font-body);
            font-size: 0.95rem;
            background: #fdfdfd;
            transition: var(--transition);
        }
        .form-input-login:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(211, 84, 0, 0.15);
            background: #fff;
        }
        .form-icon {
            position: absolute;
            left: 15px;
            top: 40px;
            color: var(--text-muted);
            font-size: 1rem;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            margin-top: 10px;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 0.88rem;
            color: var(--text-muted);
            font-weight: 500;
        }
        .back-link:hover {
            color: var(--primary);
        }
    </style>
</head>
<body class="login-body">

<div class="login-card">
    <div class="login-header">
        <div class="login-logo">
            <i class="fa-solid fa-fire-burner"></i> Ayam Penyet <span>Al-Barokah</span>
        </div>
        <p>Masuk untuk mulai memesan hidangan favorit Anda</p>
    </div>

    <?php if (!empty($success_msg)): ?>
        <div style="background:#e8f8f5; color:#1e8449; padding:12px 15px; border-radius:var(--radius-md); margin-bottom:20px; font-size:0.875rem; display:flex; align-items:center; gap:8px; font-weight:500; border:1px solid #d1f2eb;">
            <i class="fa-solid fa-circle-check"></i>
            <span><?= htmlspecialchars($success_msg) ?></span>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_msg)): ?>
        <div class="login-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error_msg) ?></span>
        </div>
    <?php endif; ?>
    
    <form action="login.php" method="POST">
        <div class="form-group-login">
            <label for="username">Username</label>
            <i class="fa-solid fa-user form-icon"></i>
            <input type="text" id="username" name="username" class="form-input-login" placeholder="Masukkan username" required autofocus>
        </div>
        
        <div class="form-group-login">
            <label for="password">Password</label>
            <i class="fa-solid fa-lock form-icon"></i>
            <input type="password" id="password" name="password" class="form-input-login" placeholder="Masukkan password" required>
        </div>
        
        <button type="submit" class="btn btn-primary btn-login">
            <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
        </button>
    </form>
    
    <div style="text-align:center; margin-top:18px; border-top:1px solid var(--border-color); padding-top:18px;">
        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:10px;">Belum punya akun?</p>
        <a href="register.php" class="btn btn-outline" style="width:100%; justify-content:center; padding:10px;">
            <i class="fa-solid fa-user-plus"></i> Daftar Akun Baru
        </a>
    </div>

    <a href="index.php" class="back-link" style="margin-top:15px; display:block;">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Menu Utama
    </a>
</div>

</body>
</html>
