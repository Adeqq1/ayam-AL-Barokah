<?php
// Halaman Registrasi Pelanggan Ayam Penyet Al-Barokah
require_once 'config/database.php';

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
$form_data = ['username' => '', 'nama_lengkap' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username     = trim($_POST['username'] ?? '');
    $password     = trim($_POST['password'] ?? '');
    $konfirmasi   = trim($_POST['konfirmasi_password'] ?? '');
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');

    $form_data = ['username' => $username, 'nama_lengkap' => $nama_lengkap];

    // Validasi input
    if (empty($username) || empty($password) || empty($konfirmasi) || empty($nama_lengkap)) {
        $error_msg = "Semua kolom pendaftaran wajib diisi!";
    } elseif (strlen($username) < 4 || strlen($username) > 50) {
        $error_msg = "Username harus antara 4 hingga 50 karakter.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error_msg = "Username hanya boleh berisi huruf, angka, dan garis bawah (_).";
    } elseif (strlen($password) < 6) {
        $error_msg = "Password minimal harus 6 karakter.";
    } elseif ($password !== $konfirmasi) {
        $error_msg = "Konfirmasi password tidak cocok!";
    } else {
        // Cek apakah username sudah digunakan
        $safe_username = mysqli_real_escape_string($conn, $username);
        $check_query   = "SELECT id FROM users WHERE username = '$safe_username'";
        $check_result  = mysqli_query($conn, $check_query);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $error_msg = "Username sudah digunakan, harap pilih username lain!";
        } else {
            $safe_nama = mysqli_real_escape_string($conn, $nama_lengkap);
            // Hash password menggunakan bcrypt sebelum disimpan ke database
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $safe_password = mysqli_real_escape_string($conn, $hashed_password);

            $insert_query = "INSERT INTO users (username, password, nama_lengkap, role)
                             VALUES ('$safe_username', '$safe_password', '$safe_nama', 'pelanggan')";

            if (mysqli_query($conn, $insert_query)) {
                // Redirect ke login dengan pesan sukses
                header("Location: login.php?registered=1");
                exit;
            } else {
                $error_msg = "Terjadi kesalahan sistem. Silakan coba lagi.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Ayam Penyet Al-Barokah</title>
    <meta name="description" content="Daftar akun pelanggan Ayam Penyet Al-Barokah untuk menikmati kemudahan pemesanan dan pelacakan status pesanan Anda.">
    <!-- Local Fonts & FontAwesome -->
    <link rel="stylesheet" href="assets/css/fonts.css">
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-body {
            background: linear-gradient(135deg, #d35400 0%, #c0392b 50%, #2c3e50 100%);
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden; /* hanya blokir scroll horizontal */
        }

        /* Dekorasi lingkaran pakai fixed agar tidak ganggu scroll */
        .auth-body::before {
            content: '';
            position: fixed;
            width: 600px;
            height: 600px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
            top: -200px;
            right: -150px;
            pointer-events: none;
            z-index: 0;
        }
        .auth-body::after {
            content: '';
            position: fixed;
            width: 400px;
            height: 400px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
            bottom: -100px;
            left: -100px;
            pointer-events: none;
            z-index: 0;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 45px 40px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
            position: relative;
            z-index: 2;
            animation: cardAppear 0.5s cubic-bezier(0.4, 0, 0.2, 1) both;
        }

        @keyframes cardAppear {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-logo-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary), #e67e22);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.8rem;
            color: #fff;
            box-shadow: 0 8px 20px rgba(211,84,0,0.35);
        }

        .auth-logo h1 {
            font-family: var(--font-heading);
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .auth-logo h1 span { color: var(--dark); }

        .auth-logo p {
            color: var(--text-muted);
            font-size: 0.88rem;
        }

        .auth-error {
            background: #fdf2f2;
            color: #c0392b;
            padding: 13px 16px;
            border-radius: var(--radius-md);
            margin-bottom: 22px;
            font-size: 0.875rem;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-weight: 500;
            border: 1px solid #f5c6cb;
        }
        .auth-error i { margin-top: 2px; flex-shrink: 0; }

        .auth-form-group {
            margin-bottom: 18px;
            position: relative;
        }

        .auth-form-group label {
            display: block;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 0.88rem;
            letter-spacing: 0.3px;
        }

        .auth-input-wrapper {
            position: relative;
        }

        .auth-input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.95rem;
            pointer-events: none;
        }

        .auth-input {
            width: 100%;
            padding: 12px 16px 12px 42px;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            font-family: var(--font-body);
            font-size: 0.95rem;
            color: var(--dark);
            background: #fdfdfd;
            transition: var(--transition);
        }

        .auth-input:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 3.5px rgba(211, 84, 0, 0.13);
        }

        .auth-hint {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 5px;
            padding-left: 2px;
        }

        .btn-auth {
            width: 100%;
            padding: 13px;
            font-size: 1rem;
            margin-top: 8px;
            border-radius: var(--radius-md);
            font-weight: 700;
            letter-spacing: 0.3px;
            background: linear-gradient(135deg, var(--primary), #e67e22);
            color: #fff;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-auth:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(211,84,0,0.35);
        }

        .auth-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0;
        }
        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }
        .auth-divider span {
            font-size: 0.8rem;
            color: var(--text-muted);
            white-space: nowrap;
        }

        .auth-footer-links {
            text-align: center;
        }

        .auth-footer-links p {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .auth-footer-links a {
            color: var(--primary);
            font-weight: 600;
        }
        .auth-footer-links a:hover { text-decoration: underline; }

        .back-home {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 12px;
        }
        .back-home:hover { color: var(--primary); }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            cursor: pointer;
            font-size: 0.9rem;
            transition: var(--transition);
        }
        .password-toggle:hover { color: var(--primary); }

        .strength-bar {
            height: 4px;
            border-radius: 4px;
            background: var(--border-color);
            margin-top: 8px;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            width: 0%;
            border-radius: 4px;
            transition: all 0.4s ease;
        }
    </style>
</head>
<body class="auth-body">

<div class="auth-card">
    <!-- Logo -->
    <div class="auth-logo">
        <div class="auth-logo-icon">
            <i class="fa-solid fa-fire-burner"></i>
        </div>
        <h1>Ayam Penyet <span>Al-Barokah</span></h1>
        <p>Buat akun baru dan mulai pesan hidangan favorit Anda</p>
    </div>

    <!-- Error Message -->
    <?php if (!empty($error_msg)): ?>
    <div class="auth-error">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= htmlspecialchars($error_msg) ?></span>
    </div>
    <?php endif; ?>

    <!-- Form Registrasi -->
    <form action="register.php" method="POST" id="register-form" novalidate>

        <!-- Nama Lengkap -->
        <div class="auth-form-group">
            <label for="nama_lengkap">Nama Lengkap *</label>
            <div class="auth-input-wrapper">
                <i class="fa-solid fa-id-card auth-input-icon"></i>
                <input type="text" id="nama_lengkap" name="nama_lengkap" class="auth-input"
                       placeholder="Masukkan nama lengkap Anda"
                       value="<?= htmlspecialchars($form_data['nama_lengkap']) ?>"
                       required autofocus>
            </div>
        </div>

        <!-- Username -->
        <div class="auth-form-group">
            <label for="username">Username *</label>
            <div class="auth-input-wrapper">
                <i class="fa-solid fa-at auth-input-icon"></i>
                <input type="text" id="username" name="username" class="auth-input"
                       placeholder="Pilih username unik Anda"
                       value="<?= htmlspecialchars($form_data['username']) ?>"
                       autocomplete="username" required>
            </div>
            <p class="auth-hint"><i class="fa-solid fa-circle-info"></i> Min. 4 karakter, hanya huruf, angka, dan garis bawah (_).</p>
        </div>

        <!-- Password -->
        <div class="auth-form-group">
            <label for="password">Password *</label>
            <div class="auth-input-wrapper">
                <i class="fa-solid fa-lock auth-input-icon"></i>
                <input type="password" id="password" name="password" class="auth-input"
                       placeholder="Buat password yang kuat"
                       autocomplete="new-password" required>
                <span class="password-toggle" onclick="togglePassword('password', this)" title="Tampilkan/Sembunyikan">
                    <i class="fa-regular fa-eye"></i>
                </span>
            </div>
            <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
            <p class="auth-hint" id="strength-text"><i class="fa-solid fa-circle-info"></i> Minimal 6 karakter.</p>
        </div>

        <!-- Konfirmasi Password -->
        <div class="auth-form-group">
            <label for="konfirmasi_password">Konfirmasi Password *</label>
            <div class="auth-input-wrapper">
                <i class="fa-solid fa-lock auth-input-icon"></i>
                <input type="password" id="konfirmasi_password" name="konfirmasi_password" class="auth-input"
                       placeholder="Ulangi password di atas"
                       autocomplete="new-password" required>
                <span class="password-toggle" onclick="togglePassword('konfirmasi_password', this)" title="Tampilkan/Sembunyikan">
                    <i class="fa-regular fa-eye"></i>
                </span>
            </div>
        </div>

        <button type="submit" class="btn-auth" id="btn-daftar">
            <i class="fa-solid fa-user-plus"></i> Daftar Sekarang
        </button>
    </form>

    <div class="auth-divider"><span>Sudah punya akun?</span></div>

    <div class="auth-footer-links">
        <p>Masuk menggunakan akun yang sudah ada:</p>
        <a href="login.php" class="btn btn-outline" style="width: 100%; justify-content: center; padding: 11px;">
            <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
        </a>
    </div>

    <a href="index.php" class="back-home">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
    </a>
</div>

<script>
// Toggle visibility password
function togglePassword(fieldId, icon) {
    const field = document.getElementById(fieldId);
    const isPassword = field.type === 'password';
    field.type = isPassword ? 'text' : 'password';
    icon.innerHTML = isPassword
        ? '<i class="fa-regular fa-eye-slash"></i>'
        : '<i class="fa-regular fa-eye"></i>';
}

// Indikator kekuatan password
document.getElementById('password').addEventListener('input', function() {
    const val = this.value;
    const fill = document.getElementById('strength-fill');
    const text = document.getElementById('strength-text');
    let strength = 0;

    if (val.length >= 6) strength++;
    if (val.length >= 10) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^a-zA-Z0-9]/.test(val)) strength++;

    const levels = [
        { width: '0%', color: 'transparent', label: '' },
        { width: '25%', color: '#e74c3c', label: '🔴 Sangat Lemah' },
        { width: '50%', color: '#e67e22', label: '🟠 Lemah' },
        { width: '75%', color: '#f1c40f', label: '🟡 Sedang' },
        { width: '90%', color: '#2ecc71', label: '🟢 Kuat' },
        { width: '100%', color: '#27ae60', label: '✅ Sangat Kuat' },
    ];

    const level = val.length === 0 ? levels[0] : levels[Math.min(strength, 5)];
    fill.style.width = level.width;
    fill.style.background = level.color;
    if (val.length > 0) {
        text.innerHTML = level.label;
    } else {
        text.innerHTML = '<i class="fa-solid fa-circle-info"></i> Minimal 6 karakter.';
    }
});
</script>

</body>
</html>
