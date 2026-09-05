<?php
// Halaman Kelola Menu Admin - Ayam Penyet Al-Barokah
require_once '../config/database.php';

/** @var mysqli $conn */

// Cek session login admin via layout header
include_once 'templates/header.php';
include_once 'templates/sidebar.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error_msg = "";
$success_msg = "";

// Ambil status dari query parameter untuk alert sukses
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success_add') $success_msg = "Menu baru berhasil ditambahkan!";
    elseif ($_GET['status'] === 'success_edit') $success_msg = "Menu berhasil diperbarui!";
    elseif ($_GET['status'] === 'success_delete') $success_msg = "Menu berhasil dihapus!";
}

// --------------------------------------------------------
// ACTION: DELETE MENU
// --------------------------------------------------------
if ($action === 'delete') {
    $menu_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($menu_id > 0) {
        // Ambil info foto terlebih dahulu untuk dihapus dari server
        $query_get_foto = "SELECT foto FROM menu WHERE id = $menu_id";
        $result_foto = mysqli_query($conn, $query_get_foto);
        if ($result_foto && mysqli_num_rows($result_foto) > 0) {
            $menu_item = mysqli_fetch_assoc($result_foto);
            $foto_name = $menu_item['foto'];
            
            // Hapus file dari server jika ada dan bukan gambar default
            if (!empty($foto_name) && $foto_name !== 'default-menu.jpg' && file_exists("../assets/images/" . $foto_name)) {
                unlink("../assets/images/" . $foto_name);
            }
        }
        
        $query_del = "DELETE FROM menu WHERE id = $menu_id";
        if (mysqli_query($conn, $query_del)) {
            header("Location: kelola_menu.php?status=success_delete");
            exit;
        } else {
            $error_msg = "Gagal menghapus menu karena terhubung dengan transaksi pesanan yang ada.";
            $action = 'list'; // kembalikan ke list view
        }
    }
}

// --------------------------------------------------------
// ACTION: ADD MENU (POST)
// --------------------------------------------------------
if ($action === 'add_process' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_menu = mysqli_real_escape_string($conn, trim($_POST['nama_menu']));
    $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
    $harga = intval($_POST['harga']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Upload file
    $foto_name = "";
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png'];
        
        if (in_array($file_ext, $allowed_exts)) {
            $foto_name = "menu_" . time() . "." . $file_ext;
            $dest_path = "../assets/images/" . $foto_name;
            
            if (!is_dir("../assets/images/")) {
                mkdir("../assets/images/", 0777, true);
            }
            
            move_uploaded_file($file_tmp, $dest_path);
        } else {
            $error_msg = "Ekstensi foto tidak valid! Hanya diperbolehkan JPG, JPEG, PNG.";
        }
    }
    
    if (empty($error_msg)) {
        if (empty($nama_menu) || $harga <= 0 || empty($kategori)) {
            $error_msg = "Semua kolom wajib diisi dengan benar!";
        } else {
            $query_add = "INSERT INTO menu (nama_menu, deskripsi, harga, kategori, foto, status) 
                          VALUES ('$nama_menu', '$deskripsi', $harga, '$kategori', '$foto_name', '$status')";
            
            if (mysqli_query($conn, $query_add)) {
                header("Location: kelola_menu.php?status=success_add");
                exit;
            } else {
                $error_msg = "Gagal menyimpan menu ke database: " . mysqli_error($conn);
            }
        }
    }
    $action = 'add'; // tampilkan form add kembali jika error
}

// --------------------------------------------------------
// ACTION: EDIT MENU (POST)
// --------------------------------------------------------
if ($action === 'edit_process' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $menu_id = intval($_POST['id']);
    $nama_menu = mysqli_real_escape_string($conn, trim($_POST['nama_menu']));
    $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
    $harga = intval($_POST['harga']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Ambil foto lama
    $query_old = "SELECT foto FROM menu WHERE id = $menu_id";
    $result_old = mysqli_query($conn, $query_old);
    $old_data = mysqli_fetch_assoc($result_old);
    $foto_name = $old_data['foto'];
    
    // Cek jika ada upload foto baru
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png'];
        
        if (in_array($file_ext, $allowed_exts)) {
            // Hapus foto lama dari server jika ada dan bukan gambar default
            if (!empty($foto_name) && $foto_name !== 'default-menu.jpg' && file_exists("../assets/images/" . $foto_name)) {
                unlink("../assets/images/" . $foto_name);
            }
            
            // Simpan foto baru
            $foto_name = "menu_" . time() . "." . $file_ext;
            $dest_path = "../assets/images/" . $foto_name;
            move_uploaded_file($file_tmp, $dest_path);
        } else {
            $error_msg = "Ekstensi foto tidak valid! Hanya diperbolehkan JPG, JPEG, PNG.";
        }
    }
    
    if (empty($error_msg)) {
        if (empty($nama_menu) || $harga <= 0 || empty($kategori)) {
            $error_msg = "Semua kolom wajib diisi dengan benar!";
        } else {
            $query_up = "UPDATE menu SET 
                         nama_menu = '$nama_menu', 
                         deskripsi = '$deskripsi', 
                         harga = $harga, 
                         kategori = '$kategori', 
                         foto = '$foto_name', 
                         status = '$status' 
                         WHERE id = $menu_id";
            
            if (mysqli_query($conn, $query_up)) {
                header("Location: kelola_menu.php?status=success_edit");
                exit;
            } else {
                $error_msg = "Gagal memperbarui database: " . mysqli_error($conn);
            }
        }
    }
    $action = 'edit'; // tampilkan form edit kembali jika error
}
?>

<!-- Alert Notifikasi -->
<?php if (!empty($success_msg)): ?>
    <div style="background-color: #e8f8f5; border: 1px solid #d1f2eb; color: var(--success); padding: 15px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 500;">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_msg) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
    <div style="background-color: #fdedec; border: 1px solid #fadbd8; color: var(--danger); padding: 15px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 500;">
        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error_msg) ?>
    </div>
<?php endif; ?>

<!-- --------------------------------------------------------
     VIEW: ADD FORM
     -------------------------------------------------------- -->
<?php if ($action === 'add'): ?>
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title"><i class="fa-solid fa-plus"></i> Tambah Menu Makanan Baru</h2>
            <a href="kelola_menu.php" class="btn-admin btn-admin-primary" style="background-color: var(--dark);"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
        </div>
        
        <form action="kelola_menu.php?action=add_process" method="POST" enctype="multipart/form-data">
            <div class="admin-form-group">
                <label for="nama_menu" class="admin-form-label">Nama Menu *</label>
                <input type="text" id="nama_menu" name="nama_menu" class="admin-form-control" placeholder="Contoh: Ayam Goreng Penyet Pedas" required>
            </div>
            
            <div class="admin-form-group">
                <label for="deskripsi" class="admin-form-label">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" class="admin-form-textarea" placeholder="Tulis deskripsi makanan atau minuman..."></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="admin-form-group">
                    <label for="harga" class="admin-form-label">Harga Jual (Rp) *</label>
                    <input type="number" id="harga" name="harga" class="admin-form-control" placeholder="Contoh: 18000" min="100" required>
                </div>
                
                <div class="admin-form-group">
                    <label for="kategori" class="admin-form-label">Kategori *</label>
                    <select id="kategori" name="kategori" class="admin-form-select" required>
                        <option value="makanan">Makanan</option>
                        <option value="minuman">Minuman</option>
                        <option value="paket">Paket Hemat</option>
                        <option value="cemilan">Cemilan</option>
                    </select>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="admin-form-group">
                    <label for="foto" class="admin-form-label">Foto Produk</label>
                    <input type="file" id="foto" name="foto" class="admin-form-control" accept=".jpg,.jpeg,.png">
                </div>
                
                <div class="admin-form-group">
                    <label for="status" class="admin-form-label">Status Ketersediaan *</label>
                    <select id="status" name="status" class="admin-form-select" required>
                        <option value="tersedia">Tersedia</option>
                        <option value="habis">Habis / Stok Kosong</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn-admin btn-admin-success" style="margin-top: 15px; padding: 12px 24px; font-size: 0.95rem;">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Menu
            </button>
        </form>
    </div>

<!-- --------------------------------------------------------
     VIEW: EDIT FORM
     -------------------------------------------------------- -->
<?php elseif ($action === 'edit'): ?>
    <?php
    $menu_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $query_fetch = "SELECT * FROM menu WHERE id = $menu_id";
    $result_fetch = mysqli_query($conn, $query_fetch);
    
    if (!$result_fetch || mysqli_num_rows($result_fetch) === 0) {
        echo "<div class='panel'>Menu tidak ditemukan!</div>";
        include_once 'templates/footer.php';
        exit;
    }
    
    $menu = mysqli_fetch_assoc($result_fetch);
    ?>
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title"><i class="fa-solid fa-pen-to-square"></i> Edit Menu Makanan</h2>
            <a href="kelola_menu.php" class="btn-admin btn-admin-primary" style="background-color: var(--dark);"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
        </div>
        
        <form action="kelola_menu.php?action=edit_process" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $menu['id'] ?>">
            
            <div class="admin-form-group">
                <label for="nama_menu" class="admin-form-label">Nama Menu *</label>
                <input type="text" id="nama_menu" name="nama_menu" class="admin-form-control" value="<?= htmlspecialchars($menu['nama_menu']) ?>" required>
            </div>
            
            <div class="admin-form-group">
                <label for="deskripsi" class="admin-form-label">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" class="admin-form-textarea"><?= htmlspecialchars($menu['deskripsi']) ?></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="admin-form-group">
                    <label for="harga" class="admin-form-label">Harga Jual (Rp) *</label>
                    <input type="number" id="harga" name="harga" class="admin-form-control" value="<?= $menu['harga'] ?>" min="100" required>
                </div>
                
                <div class="admin-form-group">
                    <label for="kategori" class="admin-form-label">Kategori *</label>
                    <select id="kategori" name="kategori" class="admin-form-select" required>
                        <option value="makanan" <?= $menu['kategori'] === 'makanan' ? 'selected' : '' ?>>Makanan</option>
                        <option value="minuman" <?= $menu['kategori'] === 'minuman' ? 'selected' : '' ?>>Minuman</option>
                        <option value="paket" <?= $menu['kategori'] === 'paket' ? 'selected' : '' ?>>Paket Hemat</option>
                        <option value="cemilan" <?= $menu['kategori'] === 'cemilan' ? 'selected' : '' ?>>Cemilan</option>
                    </select>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="admin-form-group">
                    <label for="foto" class="admin-form-label">Foto Produk (Biarkan kosong jika tidak diganti)</label>
                    <input type="file" id="foto" name="foto" class="admin-form-control" accept=".jpg,.jpeg,.png">
                    <div style="margin-top: 8px; display: flex; align-items: center; gap: 10px;">
                        <img src="<?= get_menu_image_src($menu['foto'] ?? '', '../assets/images/') ?>" alt="Preview" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd;">
                        <span style="font-size: 0.82rem; color: var(--text-muted);">Foto saat ini: <strong><?= htmlspecialchars(!empty($menu['foto']) ? $menu['foto'] : 'default-menu.jpg') ?></strong></span>
                    </div>
                </div>
                
                <div class="admin-form-group">
                    <label for="status" class="admin-form-label">Status Ketersediaan *</label>
                    <select id="status" name="status" class="admin-form-select" required>
                        <option value="tersedia" <?= $menu['status'] === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                        <option value="habis" <?= $menu['status'] === 'habis' ? 'selected' : '' ?>>Habis / Stok Kosong</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn-admin btn-admin-success" style="margin-top: 15px; padding: 12px 24px; font-size: 0.95rem;">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
            </button>
        </form>
    </div>

<!-- --------------------------------------------------------
     VIEW: LIST MENU (DEFAULT)
     -------------------------------------------------------- -->
<?php else: ?>
    <?php
    // Fetch semua menu
    $query_list = "SELECT * FROM menu ORDER BY kategori ASC, id DESC";
    $result_list = mysqli_query($conn, $query_list);
    ?>
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title"><i class="fa-solid fa-table-list"></i> Daftar Menu Penjualan</h2>
            <a href="kelola_menu.php?action=add" class="btn-admin btn-admin-primary">
                <i class="fa-solid fa-plus"></i> Tambah Menu Baru
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Foto</th>
                        <th>Nama Menu</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (mysqli_num_rows($result_list) > 0) {
                        while ($row = mysqli_fetch_assoc($result_list)) {
                    ?>
                            <tr>
                                <td>
                                    <img src="<?= get_menu_image_src($row['foto'], '../assets/images/') ?>" alt="<?= htmlspecialchars($row['nama_menu']) ?>" class="menu-thumbnail">
                                </td>
                                <td style="font-weight: 600; color: var(--dark);"><?= htmlspecialchars($row['nama_menu']) ?></td>
                                <td style="text-transform: uppercase; font-weight: 500; font-size: 0.82rem;"><?= htmlspecialchars($row['kategori']) ?></td>
                                <td style="font-weight: 600; color: var(--primary);">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                <td>
                                    <?php if ($row['status'] === 'tersedia'): ?>
                                        <span class="badge badge-completed">Tersedia</span>
                                    <?php else: ?>
                                        <span class="badge badge-cancelled">Habis</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <a href="kelola_menu.php?action=edit&id=<?= $row['id'] ?>" class="btn-admin btn-admin-primary" style="padding: 6px 12px; font-size: 0.8rem;" title="Edit Menu">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </a>
                                        <a href="kelola_menu.php?action=delete&id=<?= $row['id'] ?>" class="btn-admin btn-admin-danger" style="padding: 6px 12px; font-size: 0.8rem;" title="Hapus Menu" onclick="return confirm('Apakah Anda yakin ingin menghapus menu <?= htmlspecialchars($row['nama_menu']) ?>?')">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                    <?php 
                        }
                    } else {
                        echo '<tr><td colspan="6" style="text-align: center; padding: 25px; color: var(--text-muted);"><i class="fa-solid fa-circle-question"></i> Belum ada menu terdaftar.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php 
// Include Layout Footer
include_once 'templates/footer.php';
?>
