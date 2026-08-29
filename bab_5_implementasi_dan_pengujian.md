# 📘 BAB V: HASIL IMPLEMENTASI DAN PENGUJIAN SISTEM
## Sistem Pemesanan Online Ayam Penyet Al-Barokah

Bab ini membahas tentang hasil implementasi lingkungan pengembang, basis data, antarmuka sistem (tampilan input dan tampilan output) sesuai alur kerja transaksi, serta hasil pengujian Black-Box fungsionalitas dan analisis terhadap perangkat lunak yang telah dibangun. Seluruh tabel di dalam dokumen ini menggunakan format **Word-Ready** (dapat disalin dan ditempel langsung ke Microsoft Word secara rapi).

---

## 💻 5.1 HASIL IMPLEMENTASI

Implementasi sistem merupakan tahap di mana rancangan sistem yang telah disepakati diaplikasikan ke dalam kode program dan struktur basis data relasional sehingga sistem dapat dijalankan secara nyata pada lingkungan produksi.

### Lingkungan Implementasi (Environment)
Sistem ini dibangun dan dijalankan pada lingkungan pengembangan dengan spesifikasi sebagai berikut:
1. **Perangkat Keras (Hardware):**
   * Processor: Intel Core i5 / AMD Ryzen 5 (atau setara)
   * RAM: 8 GB DDR4
   * Storage: SSD (Solid State Drive)
2. **Perangkat Lunak (Software):**
   * Sistem Operasi: Windows 10/11
   * Web Server: Apache (melalui XAMPP / Laragon)
   * Database Server: MySQL / MariaDB (Port: 3306)
   * Bahasa Pemrograman: PHP Native (Versi 7.4 ke atas), HTML5, CSS3, JavaScript (ES6)
   * Web Browser: Google Chrome, Microsoft Edge, Mozilla Firefox

### Implementasi Basis Data (Database)
Basis data relasional yang digunakan bernama `db_pemesanan`. Berikut adalah struktur tabel basis data yang telah diimplementasikan dalam sistem:

#### 1. Tabel `users` (Penyimpanan Data Pengguna/Admin)
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Kolom</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Tipe Data</th>
      <th style="width: 15%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">Atribut</th>
      <th style="width: 35%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>id</code></td>
      <td style="border: 1px solid #a0a0a0;">INT</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">PK, AI</td>
      <td style="border: 1px solid #a0a0a0;">Primary Key, auto increment.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>username</code></td>
      <td style="border: 1px solid #a0a0a0;">VARCHAR(50)</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">UNIQUE</td>
      <td style="border: 1px solid #a0a0a0;">Username unik untuk login.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>password</code></td>
      <td style="border: 1px solid #a0a0a0;">VARCHAR(255)</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">Password pengguna yang di-hash.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>nama_lengkap</code></td>
      <td style="border: 1px solid #a0a0a0;">VARCHAR(100)</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">Nama lengkap pemilik akun.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>role</code></td>
      <td style="border: 1px solid #a0a0a0;">ENUM('admin','pelanggan')</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">Peran hak akses di sistem.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>created_at</code></td>
      <td style="border: 1px solid #a0a0a0;">TIMESTAMP</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">Waktu pembuatan akun.</td>
    </tr>
  </tbody>
</table>

#### 2. Tabel `menu` (Penyimpanan Data Katalog Menu Kuliner)
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Kolom</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Tipe Data</th>
      <th style="width: 15%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">Atribut</th>
      <th style="width: 35%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>id</code></td>
      <td style="border: 1px solid #a0a0a0;">INT</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">PK, AI</td>
      <td style="border: 1px solid #a0a0a0;">Primary Key, auto increment.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>nama_menu</code></td>
      <td style="border: 1px solid #a0a0a0;">VARCHAR(100)</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">Nama hidangan makanan/minuman.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>deskripsi</code></td>
      <td style="border: 1px solid #a0a0a0;">TEXT</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">NULL</td>
      <td style="border: 1px solid #a0a0a0;">Detail komposisi/rasa menu.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>harga</code></td>
      <td style="border: 1px solid #a0a0a0;">INT</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">Harga menu dalam Rupiah.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>kategori</code></td>
      <td style="border: 1px solid #a0a0a0;">ENUM('makanan','minuman','paket','cemilan')</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">Pengelompokan jenis produk.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>foto</code></td>
      <td style="border: 1px solid #a0a0a0;">VARCHAR(255)</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">Nama file foto di assets/images/.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>status</code></td>
      <td style="border: 1px solid #a0a0a0;">ENUM('tersedia','habis')</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">Status ketersediaan stok menu.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>created_at</code></td>
      <td style="border: 1px solid #a0a0a0;">TIMESTAMP</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">Waktu input data menu.</td>
    </tr>
  </tbody>
</table>

#### 3. Tabel `pesanan` (Penyimpanan Transaksi Pemesanan Induk)
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Kolom</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Tipe Data</th>
      <th style="width: 15%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">Atribut</th>
      <th style="width: 35%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>id</code></td>
      <td style="border: 1px solid #a0a0a0;">INT</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">PK, AI</td>
      <td style="border: 1px solid #a0a0a0;">Primary Key, auto increment.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>kode_pesanan</code></td>
      <td style="border: 1px solid #a0a0a0;">VARCHAR(20)</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">UNIQUE</td>
      <td style="border: 1px solid #a0a0a0;">Kode transaksi unik (ALB-YYMMDD-HHMMSS).</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>user_id</code></td>
      <td style="border: 1px solid #a0a0a0;">INT</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">FK, NULL</td>
      <td style="border: 1px solid #a0a0a0;">Relasi ke <code>users.id</code> (NULL jika guest).</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>nama_pemesan</code></td>
      <td style="border: 1px solid #a0a0a0;">VARCHAR(100)</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">Nama lengkap pelanggan.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>no_telepon</code></td>
      <td style="border: 1px solid #a0a0a0;">VARCHAR(20)</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">No HP/WA pelanggan untuk konfirmasi.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>alamat</code></td>
      <td style="border: 1px solid #a0a0a0;">TEXT</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">NULL</td>
      <td style="border: 1px solid #a0a0a0;">Alamat pengiriman (diisi jika Delivery).</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>tipe_pesanan</code></td>
      <td style="border: 1px solid #a0a0a0;">ENUM('dine_in','take_away','delivery')</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">Opsi metode pengambilan/kirim pesanan.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>total_harga</code></td>
      <td style="border: 1px solid #a0a0a0;">INT</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">Total bayar belanja (ongkir flat Rp10.000 jika Delivery).</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>bukti_pembayaran</code></td>
      <td style="border: 1px solid #a0a0a0;">VARCHAR(255)</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">NULL</td>
      <td style="border: 1px solid #a0a0a0;">Nama file gambar bukti transfer pelanggan.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>status_pembayaran</code></td>
      <td style="border: 1px solid #a0a0a0;">ENUM('belum_bayar', 'menunggu_konfirmasi', 'lunas', 'ditolak')</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">Status verifikasi keuangan transaksi.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>status_pesanan</code></td>
      <td style="border: 1px solid #a0a0a0;">ENUM('pending','diproses','selesai','dibatalkan')</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">Status operasional pengolahan makanan.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>tanggal_pesanan</code></td>
      <td style="border: 1px solid #a0a0a0;">TIMESTAMP</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">Waktu transaksi dibuat.</td>
    </tr>
  </tbody>
</table>

#### 4. Tabel `detail_pesanan` (Rincian Item yang Dipesan)
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Kolom</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Tipe Data</th>
      <th style="width: 15%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">Atribut</th>
      <th style="width: 35%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>id</code></td>
      <td style="border: 1px solid #a0a0a0;">INT</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">PK, AI</td>
      <td style="border: 1px solid #a0a0a0;">Primary Key, auto increment.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>pesanan_id</code></td>
      <td style="border: 1px solid #a0a0a0;">INT</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">FK</td>
      <td style="border: 1px solid #a0a0a0;">Relasi ke <code>pesanan.id</code> (ON DELETE CASCADE).</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>menu_id</code></td>
      <td style="border: 1px solid #a0a0a0;">INT</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">FK</td>
      <td style="border: 1px solid #a0a0a0;">Relasi ke <code>menu.id</code> (ON DELETE CASCADE).</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>jumlah</code></td>
      <td style="border: 1px solid #a0a0a0;">INT</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">Kuantitas porsi item menu yang dipesan.</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0;"><code>subtotal</code></td>
      <td style="border: 1px solid #a0a0a0;">INT</td>
      <td style="border: 1px solid #a0a0a0; text-align: center;">-</td>
      <td style="border: 1px solid #a0a0a0;">Harga menu dikali kuantitas belanja.</td>
    </tr>
  </tbody>
</table>

---

### 5.1.1 Implementasi Tampilan Input

Tampilan input merupakan sekumpulan modul antarmuka sistem yang memfasilitasi pengguna (Pelanggan maupun Admin) untuk memasukkan, mengirimkan, atau mengubah data di dalam sistem. Pada Sistem Pemesanan Online Ayam Penyet Al-Barokah, implementasi tampilan input dirancang secara sistematis untuk mendukung seluruh alur operasional, mulai dari pembuatan akun pelanggan, pemrosesan transaksi pesanan hidangan, hingga pengelolaan data backend oleh pihak pengelola warung.

Seluruh antarmuka input dibangun menggunakan kombinasi elemen formulir HTML5 (*input text*, *password*, *number*, *select option*, *textarea*, dan *input file*) yang dilengkapi dengan mekanisme validasi ganda (*client-side* dan *server-side*). Validasi ini bertujuan untuk memastikan kelengkapan data, mencegah input kosong, membatasi format berkas yang diunggah, serta menjaga keamanan sistem dari ancaman *SQL Injection* dan *Cross-Site Scripting* (XSS) dengan memanfaatkan pemrosesan sanitasi data (seperti `mysqli_real_escape_string()`, `trim()`, dan `password_hash()` menggunakan algoritma BCrypt).

Secara fungsional, antarmuka input dalam sistem ini dikelompokkan menjadi dua kategori utama sesuai dengan peran pengguna (*user role*), yaitu:
1. **Modul Input Sisi Pelanggan (*Customer-Side Input*)**: Meliputi formulir pendaftaran akun baru (`register.php`), autentikasi masuk sistem (`login.php`), tombol kontrol kuantitas dan manipulasi item keranjang (`fitur_pemesanan/keranjang.php`), formulir kelengkapan identitas dan opsi metode pengantaran pada halaman *checkout* (`fitur_pemesanan/checkout.php`), serta modul pengunggahan berkas bukti transfer pembayaran (`fitur_pemesanan/konfirmasi-bayar.php`).
2. **Modul Input Sisi Administrator (*Admin-Side Input*)**: Meliputi formulir *Create, Read, Update, Delete* (CRUD) data katalog masakan dan minuman (`admin/kelola_menu.php`), modul pembaruan status pembayaran dan status penanganan pesanan secara *real-time* (`admin/kelola_pesanan.php`), serta formulir filter penentuan rentang tanggal rekapitulasi laporan penjualan (`admin/laporan.php`).

Berikut adalah rincian detail implementasi masing-masing tampilan input beserta elemen pendukungnya sesuai dengan alur penggunaan sistem **Ayam Penyet Al-Barokah**:

#### 1. Form Registrasi Pelanggan (`register.php`)
Halaman ini memfasilitasi calon pelanggan baru untuk menginputkan data pendaftaran akun (*nama lengkap*, *username*, *password*, dan *konfirmasi password*) agar terdaftar di dalam sistem dan dapat melakukan transaksi pemesanan.

![Implementasi Halaman Registrasi](assets/images/login_interface_bw.png)
*Gambar 5.1 Implementasi Halaman Registrasi Pelanggan*

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 8%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">No</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Elemen Input</th>
      <th style="width: 20%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Jenis Elemen</th>
      <th style="width: 47%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Fungsi dan Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">1</td>
      <td style="border: 1px solid #a0a0a0;"><code>nama_lengkap</code></td>
      <td style="border: 1px solid #a0a0a0;">Input Text</td>
      <td style="border: 1px solid #a0a0a0;">Menginputkan nama lengkap pemilik akun pelanggan.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">2</td>
      <td style="border: 1px solid #a0a0a0;"><code>username</code></td>
      <td style="border: 1px solid #a0a0a0;">Input Text</td>
      <td style="border: 1px solid #a0a0a0;">Menginputkan username unik untuk identifikasi akun (4-50 karakter).</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">3</td>
      <td style="border: 1px solid #a0a0a0;"><code>password</code></td>
      <td style="border: 1px solid #a0a0a0;">Input Password</td>
      <td style="border: 1px solid #a0a0a0;">Menginputkan kata sandi rahasia untuk keamanan akun (minimal 6 karakter).</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">4</td>
      <td style="border: 1px solid #a0a0a0;"><code>konfirmasi_password</code></td>
      <td style="border: 1px solid #a0a0a0;">Input Password</td>
      <td style="border: 1px solid #a0a0a0;">Menginputkan kembali kata sandi untuk memastikan kesesuaian sandi.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">5</td>
      <td style="border: 1px solid #a0a0a0;">Daftar Sekarang</td>
      <td style="border: 1px solid #a0a0a0;">Button Submit</td>
      <td style="border: 1px solid #a0a0a0;">Mengirimkan data registrasi untuk divalidasi dan disimpan ke tabel <code>users</code>.</td>
    </tr>
  </tbody>
</table>

#### 2. Form Autentikasi Login (`login.php`)
Halaman ini memfasilitasi pengguna (baik admin maupun pelanggan) untuk menginputkan nama pengguna (*username*) dan kata sandi (*password*) guna memverifikasi hak akses masuk ke panel sistem.

![Implementasi Halaman Login](assets/images/login_interface_bw.png)
*Gambar 5.2 Implementasi Halaman Login*

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 8%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">No</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Elemen Input</th>
      <th style="width: 20%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Jenis Elemen</th>
      <th style="width: 47%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Fungsi dan Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">1</td>
      <td style="border: 1px solid #a0a0a0;"><code>username</code></td>
      <td style="border: 1px solid #a0a0a0;">Input Text</td>
      <td style="border: 1px solid #a0a0a0;">Tempat memasukkan username akun pengguna.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">2</td>
      <td style="border: 1px solid #a0a0a0;"><code>password</code></td>
      <td style="border: 1px solid #a0a0a0;">Input Password</td>
      <td style="border: 1px solid #a0a0a0;">Tempat memasukkan kata sandi rahasia akun pengguna.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">3</td>
      <td style="border: 1px solid #a0a0a0;">Masuk Sekarang</td>
      <td style="border: 1px solid #a0a0a0;">Button Submit</td>
      <td style="border: 1px solid #a0a0a0;">Mengirim kredensial form untuk divalidasi oleh sistem dan membuat session login.</td>
    </tr>
  </tbody>
</table>

#### 3. Kuantitas Keranjang Belanja (`fitur_pemesanan/keranjang.php`)
Pada halaman ini, pelanggan dapat menginputkan perubahan kuantitas (*quantity*) pesanan secara interaktif menggunakan tombol penambahan atau pengurangan jumlah porsi hidangan serta tombol penghapusan item.

![Implementasi Kuantitas Keranjang Belanja](assets/images/keranjang_belanja_bw.png)
*Gambar 5.3 Implementasi Kuantitas Keranjang Belanja*

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 8%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">No</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Elemen Input</th>
      <th style="width: 20%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Jenis Elemen</th>
      <th style="width: 47%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Fungsi dan Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">1</td>
      <td style="border: 1px solid #a0a0a0;">Tombol Tambah (+)</td>
      <td style="border: 1px solid #a0a0a0;">Button (link)</td>
      <td style="border: 1px solid #a0a0a0;">Menambah jumlah item menu terpilih sebanyak 1 unit.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">2</td>
      <td style="border: 1px solid #a0a0a0;">Tombol Kurang (-)</td>
      <td style="border: 1px solid #a0a0a0;">Button (link)</td>
      <td style="border: 1px solid #a0a0a0;">Mengurangi jumlah item menu terpilih sebanyak 1 unit.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">3</td>
      <td style="border: 1px solid #a0a0a0;">Tombol Hapus</td>
      <td style="border: 1px solid #a0a0a0;">Button (link)</td>
      <td style="border: 1px solid #a0a0a0;">Menghapus baris item menu dari keranjang belanja secara permanen.</td>
    </tr>
  </tbody>
</table>

#### 4. Formulir Data Diri & Checkout (`fitur_pemesanan/checkout.php`)
Formulir input ini wajib diisi oleh pelanggan untuk melengkapi identitas pemesanan, nomor kontak aktif, serta opsi metode penanganan pesanan (Dine in, Take away, Delivery).

![Implementasi Halaman Checkout](assets/images/checkout_pesanan_bw.png)
*Gambar 5.4 Implementasi Halaman Checkout*

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 8%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">No</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Elemen Input</th>
      <th style="width: 20%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Jenis Elemen</th>
      <th style="width: 47%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Fungsi dan Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">1</td>
      <td style="border: 1px solid #a0a0a0;"><code>nama_pemesan</code></td>
      <td style="border: 1px solid #a0a0a0;">Input Text</td>
      <td style="border: 1px solid #a0a0a0;">Menginput nama lengkap pelanggan yang memesan.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">2</td>
      <td style="border: 1px solid #a0a0a0;"><code>no_telepon</code></td>
      <td style="border: 1px solid #a0a0a0;">Input Tel/Number</td>
      <td style="border: 1px solid #a0a0a0;">Menginput nomor WhatsApp/kontak aktif pemesan.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">3</td>
      <td style="border: 1px solid #a0a0a0;"><code>tipe_pesanan</code></td>
      <td style="border: 1px solid #a0a0a0;">Select Option</td>
      <td style="border: 1px solid #a0a0a0;">Memilih metode layanan pesanan ('dine_in', 'take_away', atau 'delivery').</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">4</td>
      <td style="border: 1px solid #a0a0a0;"><code>alamat</code></td>
      <td style="border: 1px solid #a0a0a0;">Textarea</td>
      <td style="border: 1px solid #a0a0a0;">Menginput alamat lengkap pengiriman (tampil dinamis via JS jika tipe pesanan bernilai 'delivery').</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">5</td>
      <td style="border: 1px solid #a0a0a0;">Buat Pesanan</td>
      <td style="border: 1px solid #a0a0a0;">Button Submit</td>
      <td style="border: 1px solid #a0a0a0;">Menyimpan transaksi baru ke database tabel <code>pesanan</code> dan <code>detail_pesanan</code>.</td>
    </tr>
  </tbody>
</table>

#### 5. Unggah Bukti Bayar (`fitur_pemesanan/konfirmasi-bayar.php`)
Modul input ini memfasilitasi pelanggan untuk mengunggah berkas gambar hasil transfer sebagai bukti pelunasan tagihan pemesanan.

![Implementasi Halaman Konfirmasi Pembayaran](assets/images/konfirmasi_bayar_bw.png)
*Gambar 5.5 Implementasi Halaman Konfirmasi Pembayaran*

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 8%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">No</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Elemen Input</th>
      <th style="width: 20%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Jenis Elemen</th>
      <th style="width: 47%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Fungsi dan Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">1</td>
      <td style="border: 1px solid #a0a0a0;"><code>bukti_pembayaran</code></td>
      <td style="border: 1px solid #a0a0a0;">Input File</td>
      <td style="border: 1px solid #a0a0a0;">Memilih file gambar (JPG, JPEG, PNG, maks. 2MB) bukti transfer bank.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">2</td>
      <td style="border: 1px solid #a0a0a0;">Kirim Bukti Pembayaran</td>
      <td style="border: 1px solid #a0a0a0;">Button Submit</td>
      <td style="border: 1px solid #a0a0a0;">Mengunggah gambar ke folder server (<code>bukti_bayar/</code>) dan mengubah status pembayaran menjadi 'menunggu_konfirmasi'.</td>
    </tr>
  </tbody>
</table>

#### 6. Form Kelola Katalog Menu Admin (`admin/kelola_menu.php`)
Formulir input backend ini digunakan oleh administrator untuk menambahkan produk kuliner baru atau menyunting rincian data menu yang telah terdaftar (CRUD).

![Implementasi Halaman Kelola Menu](assets/images/kelola_menu_bw.png)
*Gambar 5.6 Implementasi Halaman Kelola Menu*

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 8%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">No</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Elemen Input</th>
      <th style="width: 20%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Jenis Elemen</th>
      <th style="width: 47%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Fungsi dan Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">1</td>
      <td style="border: 1px solid #a0a0a0;"><code>nama_menu</code></td>
      <td style="border: 1px solid #a0a0a0;">Input Text</td>
      <td style="border: 1px solid #a0a0a0;">Menginput nama masakan atau minuman baru.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">2</td>
      <td style="border: 1px solid #a0a0a0;"><code>deskripsi</code></td>
      <td style="border: 1px solid #a0a0a0;">Textarea</td>
      <td style="border: 1px solid #a0a0a0;">Menginput penjelasan detail komposisi atau tingkat kepedasan hidangan.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">3</td>
      <td style="border: 1px solid #a0a0a0;"><code>harga</code></td>
      <td style="border: 1px solid #a0a0a0;">Input Number</td>
      <td style="border: 1px solid #a0a0a0;">Menginput nominal harga jual hidangan dalam satuan rupiah.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">4</td>
      <td style="border: 1px solid #a0a0a0;"><code>kategori</code></td>
      <td style="border: 1px solid #a0a0a0;">Select Option</td>
      <td style="border: 1px solid #a0a0a0;">Memilih kelompok menu ('makanan', 'minuman', 'paket', 'cemilan').</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">5</td>
      <td style="border: 1px solid #a0a0a0;"><code>foto</code></td>
      <td style="border: 1px solid #a0a0a0;">Input File</td>
      <td style="border: 1px solid #a0a0a0;">Memilih file foto hidangan untuk diunggah ke folder aset gambar (<code>assets/images/</code>).</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">6</td>
      <td style="border: 1px solid #a0a0a0;"><code>status</code></td>
      <td style="border: 1px solid #a0a0a0;">Select Option</td>
      <td style="border: 1px solid #a0a0a0;">Memilih status ketersediaan stok dapur ('tersedia' atau 'habis').</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">7</td>
      <td style="border: 1px solid #a0a0a0;">Simpan Menu / Update</td>
      <td style="border: 1px solid #a0a0a0;">Button Submit</td>
      <td style="border: 1px solid #a0a0a0;">Menyimpan atau memperbarui data hidangan ke tabel <code>menu</code>.</td>
    </tr>
  </tbody>
</table>

#### 7. Form Verifikasi & Update Status Pesanan (`admin/kelola_pesanan.php`)
Tampilan input ini digunakan oleh admin/kasir untuk mengubah status pembayaran dan status penanganan pesanan setelah meninjau transaksi dan bukti transfer pelanggan.

![Implementasi Halaman Kelola Pesanan](assets/images/kelola_pesanan_bw.png)
*Gambar 5.7 Implementasi Halaman Kelola Pesanan*

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 8%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">No</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Elemen Input</th>
      <th style="width: 20%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Jenis Elemen</th>
      <th style="width: 47%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Fungsi dan Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">1</td>
      <td style="border: 1px solid #a0a0a0;"><code>status_pembayaran</code></td>
      <td style="border: 1px solid #a0a0a0;">Select Option</td>
      <td style="border: 1px solid #a0a0a0;">Mengubah status keuangan ('belum_bayar', 'menunggu_konfirmasi', 'lunas', 'ditolak').</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">2</td>
      <td style="border: 1px solid #a0a0a0;"><code>status_pesanan</code></td>
      <td style="border: 1px solid #a0a0a0;">Select Option</td>
      <td style="border: 1px solid #a0a0a0;">Mengubah status dapur/pengantaran ('pending', 'diproses', 'selesai', 'dibatalkan').</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">3</td>
      <td style="border: 1px solid #a0a0a0;">Tombol Perbarui Status</td>
      <td style="border: 1px solid #a0a0a0;">Button Submit</td>
      <td style="border: 1px solid #a0a0a0;">Menyimpan status terkini transaksi ke database <code>pesanan</code> secara real-time.</td>
    </tr>
  </tbody>
</table>

#### 8. Filter Laporan Penjualan (`admin/laporan.php`)
Tampilan input ini berfungsi memfasilitasi admin untuk memfilter data rekapitulasi omzet bersih penjualan berdasarkan rentang tanggal tertentu.

![Implementasi Halaman Filter Laporan](assets/images/laporan_penjualan_bw.png)
*Gambar 5.8 Implementasi Halaman Laporan Penjualan*

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 8%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">No</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Elemen Input</th>
      <th style="width: 20%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Jenis Elemen</th>
      <th style="width: 47%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Fungsi dan Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">1</td>
      <td style="border: 1px solid #a0a0a0;"><code>start_date</code> (Dari Tanggal)</td>
      <td style="border: 1px solid #a0a0a0;">Input Date</td>
      <td style="border: 1px solid #a0a0a0;">Menentukan tanggal awal batas pencarian transaksi.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">2</td>
      <td style="border: 1px solid #a0a0a0;"><code>end_date</code> (Sampai Tanggal)</td>
      <td style="border: 1px solid #a0a0a0;">Input Date</td>
      <td style="border: 1px solid #a0a0a0;">Menentukan tanggal akhir batas pencarian transaksi.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">3</td>
      <td style="border: 1px solid #a0a0a0;">Filter Laporan</td>
      <td style="border: 1px solid #a0a0a0;">Button Submit</td>
      <td style="border: 1px solid #a0a0a0;">Memproses query filter data transaksi lunas & selesai ke database.</td>
    </tr>
  </tbody>
</table>

---

### 5.1.2 Implementasi Tampilan Output

Tampilan output merupakan visualisasi hasil pemrosesan data oleh sistem yang disajikan kepada pengguna, baik berupa informasi daftar produk, rincian biaya transaksi, status pelacakan pesanan, maupun rekapitulasi data grafik/tabel laporan keuangan.

#### 1. Katalog Utama Halaman Pelanggan (`index.php`)
Merupakan landing page utama yang menyajikan output data katalog menu kuliner (makanan, minuman, paket, cemilan) secara dinamis lengkap dengan foto, harga, status stok, serta deskripsi rasa.

![Implementasi Katalog Utama Halaman Pelanggan](assets/images/katalog_menu_bw.png)
*Gambar 5.8 Implementasi Katalog Utama Halaman Pelanggan*

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 8%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">No</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Elemen Output</th>
      <th style="width: 20%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Jenis Elemen</th>
      <th style="width: 47%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Fungsi dan Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">1</td>
      <td style="border: 1px solid #a0a0a0;">Kartu Menu (Grid)</td>
      <td style="border: 1px solid #a0a0a0;">Container Box</td>
      <td style="border: 1px solid #a0a0a0;">Menampilkan nama menu, harga, gambar hidangan, dan deskripsi produk dari database.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">2</td>
      <td style="border: 1px solid #a0a0a0;">Badge Status Stok</td>
      <td style="border: 1px solid #a0a0a0;">Label Badge</td>
      <td style="border: 1px solid #a0a0a0;">Menampilkan status ketersediaan menu ('Tersedia' berwarna hijau atau 'Habis' berwarna merah).</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">3</td>
      <td style="border: 1px solid #a0a0a0;">Notifikasi Tambah Keranjang</td>
      <td style="border: 1px solid #a0a0a0;">Toast Alert</td>
      <td style="border: 1px solid #a0a0a0;">Notifikasi melayang yang muncul selama 3 detik setelah berhasil menambahkan item menu ke keranjang.</td>
    </tr>
  </tbody>
</table>

#### 2. Ringkasan Belanja (`fitur_pemesanan/keranjang.php`)
Output ini menyajikan data review daftar hidangan yang telah dipilih pelanggan, lengkap dengan perhitungan subtotal per item dan total akumulasi nominal belanja yang harus dibayar.

![Implementasi Ringkasan Keranjang Belanja](assets/images/keranjang_belanja_bw.png)
*Gambar 5.9 Implementasi Ringkasan Keranjang Belanja*

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 8%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">No</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Elemen Output</th>
      <th style="width: 20%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Jenis Elemen</th>
      <th style="width: 47%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Fungsi dan Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">1</td>
      <td style="border: 1px solid #a0a0a0;">Tabel Detail Item</td>
      <td style="border: 1px solid #a0a0a0;">Tabel Dinamis</td>
      <td style="border: 1px solid #a0a0a0;">Menampilkan nama menu, foto, harga satuan, jumlah porsi, dan subtotal harga.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">2</td>
      <td style="border: 1px solid #a0a0a0;">Total Belanja</td>
      <td style="border: 1px solid #a0a0a0;">Text Field Box</td>
      <td style="border: 1px solid #a0a0a0;">Menampilkan total tagihan sebelum ongkos kirim.</td>
    </tr>
  </tbody>
</table>

#### 3. Detail Pembayaran & Pelacakan Status (`fitur_pemesanan/konfirmasi-bayar.php`)
Output ini menyajikan informasi detail tagihan akhir transaksi, nomor rekening/tujuan transfer (Platform DANA & SeaBank), nama pemilik rekening, serta pelacakan real-time status pembayaran dan status pesanan dari dapur.

![Implementasi Halaman Detail & Pelacakan Pesanan](assets/images/konfirmasi_bayar_bw.png)
*Gambar 5.10 Implementasi Halaman Detail & Pelacakan Pesanan*

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 8%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">No</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Elemen Output</th>
      <th style="width: 20%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Jenis Elemen</th>
      <th style="width: 47%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Fungsi dan Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">1</td>
      <td style="border: 1px solid #a0a0a0;">Rincian Tagihan Akhir</td>
      <td style="border: 1px solid #a0a0a0;">Text Label</td>
      <td style="border: 1px solid #a0a0a0;">Menampilkan total uang yang harus ditransfer (termasuk ongkos kirim Rp10.000 jika bertipe Delivery).</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">2</td>
      <td style="border: 1px solid #a0a0a0;">Nomor Rekening Bank</td>
      <td style="border: 1px solid #a0a0a0;">Container Box</td>
      <td style="border: 1px solid #a0a0a0;">Menampilkan nomor rekening bank resmi warung untuk tujuan pembayaran.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">3</td>
      <td style="border: 1px solid #a0a0a0;">Badge Status Pembayaran</td>
      <td style="border: 1px solid #a0a0a0;">Badge Status</td>
      <td style="border: 1px solid #a0a0a0;">Status verifikasi bayar (Belum Bayar / Menunggu Konfirmasi / Lunas / Ditolak).</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">4</td>
      <td style="border: 1px solid #a0a0a0;">Badge Status Pesanan</td>
      <td style="border: 1px solid #a0a0a0;">Badge Status</td>
      <td style="border: 1px solid #a0a0a0;">Status pemrosesan masakan di dapur (Pending / Diproses / Selesai / Dibatalkan).</td>
    </tr>
  </tbody>
</table>

#### 4. Dashboard Ringkasan Finansial & Operasional (`admin/index.php`)
Output visual backend untuk menyajikan rekap data omzet lunas, total pesanan harian, jumlah antrean pending di dapur, jumlah menu kuliner terdaftar, serta tabel rangkuman 5 transaksi terbaru.

![Implementasi Dashboard Admin](assets/images/dashboard_admin_bw.png)
*Gambar 5.11 Implementasi Dashboard Admin*

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 8%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">No</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Elemen Output</th>
      <th style="width: 20%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Jenis Elemen</th>
      <th style="width: 47%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Fungsi dan Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">1</td>
      <td style="border: 1px solid #a0a0a0;">Kartu Rekap Omzet Lunas</td>
      <td style="border: 1px solid #a0a0a0;">Card Box</td>
      <td style="border: 1px solid #a0a0a0;">Menampilkan jumlah akumulasi nominal uang masuk dari pesanan yang berstatus 'Lunas'.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">2</td>
      <td style="border: 1px solid #a0a0a0;">Kartu Total Antrean Pesanan</td>
      <td style="border: 1px solid #a0a0a0;">Card Box</td>
      <td style="border: 1px solid #a0a0a0;">Menampilkan jumlah pesanan masuk yang sedang dikerjakan atau diantar.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">3</td>
      <td style="border: 1px solid #a0a0a0;">Tabel 5 Pesanan Teratas</td>
      <td style="border: 1px solid #a0a0a0;">Tabel Dinamis</td>
      <td style="border: 1px solid #a0a0a0;">Menampilkan kode pesanan, nama pembeli, total belanja, dan status terupdate secara real-time.</td>
    </tr>
  </tbody>
</table>

#### 5. Tabel Daftar Katalog Menu (`admin/kelola_menu.php`)
Output ini menampilkan list data menu kuliner yang terdaftar di dalam database warung, lengkap dengan tampilan gambar hidangan, nominal harga, kategori, dan opsi tombol manajemen data.

![Implementasi Tabel Daftar Menu](assets/images/kelola_menu_bw.png)
*Gambar 5.12 Implementasi Tabel Daftar Menu*

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 8%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">No</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Elemen Output</th>
      <th style="width: 20%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Jenis Elemen</th>
      <th style="width: 47%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Fungsi dan Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">1</td>
      <td style="border: 1px solid #a0a0a0;">Tabel Daftar Menu</td>
      <td style="border: 1px solid #a0a0a0;">Tabel Dinamis</td>
      <td style="border: 1px solid #a0a0a0;">Menampilkan kolom Nomor, Gambar Hidangan, Nama Menu, Kategori, Harga, dan Status Stok.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">2</td>
      <td style="border: 1px solid #a0a0a0;">Gambar Thumbnail Menu</td>
      <td style="border: 1px solid #a0a0a0;">Image Container</td>
      <td style="border: 1px solid #a0a0a0;">Menampilkan gambar menu dari database yang tersimpan di direktori server.</td>
    </tr>
  </tbody>
</table>

#### 6. Tabel Manajemen Transaksi & Modal Zoom Bukti Bayar (`admin/kelola_pesanan.php`)
Output ini menampilkan seluruh data transaksi dari pelanggan. Admin dapat membuka tautan bukti bayar yang memicu kemunculan modal popup JavaScript untuk meninjau kecocokan nominal transfer tanpa meninggalkan halaman antrean pesanan.

![Implementasi Halaman Kelola Pesanan](assets/images/kelola_pesanan_bw.png)
*Gambar 5.13 Implementasi Halaman Kelola Pesanan*

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 8%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">No</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Elemen Output</th>
      <th style="width: 20%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Jenis Elemen</th>
      <th style="width: 47%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Fungsi dan Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">1</td>
      <td style="border: 1px solid #a0a0a0;">Tabel Pesanan Masuk</td>
      <td style="border: 1px solid #a0a0a0;">Tabel Dinamis</td>
      <td style="border: 1px solid #a0a0a0;">Menampilkan data transaksi lengkap, detail porsi hidangan yang dibeli, dan metode order.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">2</td>
      <td style="border: 1px solid #a0a0a0;">Modal Zoom Gambar Bukti Bayar</td>
      <td style="border: 1px solid #a0a0a0;">Modal Overlay Box</td>
      <td style="border: 1px solid #a0a0a0;">Pop-up JavaScript untuk memperbesar berkas gambar bukti transfer keuangan yang diunggah pelanggan.</td>
    </tr>
  </tbody>
</table>

#### 7. Hasil Rekap & Format Cetak Laporan Keuangan (`admin/laporan.php`)
Output ini menampilkan baris transaksi yang masuk sesuai filter tanggal, total penjumlahan omzet bersih yang diterima, serta tombol cetak laporan keuangan yang menginisiasi *browser print dialog* ramah cetakan kertas.

![Implementasi Hasil Laporan & Cetak](assets/images/laporan_penjualan_bw.png)
*Gambar 5.14 Implementasi Hasil Laporan & Cetak*

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 8%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">No</th>
      <th style="width: 25%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Nama Elemen Output</th>
      <th style="width: 20%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Jenis Elemen</th>
      <th style="width: 47%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Fungsi dan Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">1</td>
      <td style="border: 1px solid #a0a0a0;">Tabel Penjualan Terfilter</td>
      <td style="border: 1px solid #a0a0a0;">Tabel Dinamis</td>
      <td style="border: 1px solid #a0a0a0;">Menampilkan data transaksi harian yang masuk pada rentang filter tanggal.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">2</td>
      <td style="border: 1px solid #a0a0a0;">Total Akumulasi Omzet</td>
      <td style="border: 1px solid #a0a0a0;">Text Label Box</td>
      <td style="border: 1px solid #a0a0a0;">Menampilkan total pendapatan bersih lunas hasil penjumlahan otomatis sistem.</td>
    </tr>
    <tr>
      <td style="text-align: center; border: 1px solid #a0a0a0;">3</td>
      <td style="border: 1px solid #a0a0a0;">Tata Letak Cetak Fisik</td>
      <td style="border: 1px solid #a0a0a0;">CSS @media print</td>
      <td style="border: 1px solid #a0a0a0;">Format tampilan bersih tanpa sidebar/navbar yang otomatis diaplikasikan saat dicetak fisik lewat printer.</td>
    </tr>
  </tbody>
</table>

---

## 🧪 5.2 HASIL PENGUJIAN SISTEM (TESTING)

Pengujian sistem dilakukan dengan menggunakan metode **Black-Box Testing** (Pengujian Kotak Hitam). Fokus pengujian ini adalah untuk memvalidasi fungsionalitas sistem berdasarkan masukan (input) dan keluaran (output) yang dihasilkan, guna memastikan seluruh Use Case berjalan sesuai dengan kebutuhan sistem tanpa harus menguji struktur kode internal program.

### 5.2.1 Skenario Pengujian Black-Box

Berikut adalah tabel skenario pengujian fungsionalitas sistem untuk aktor Pelanggan dan Admin:

#### 1. Tabel Pengujian Fungsionalitas Aktor: Pelanggan
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 5%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">No</th>
      <th style="width: 20%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Fungsi Uji</th>
      <th style="width: 30%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Prosedur Pengujian</th>
      <th style="width: 30%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Hasil yang Diharapkan</th>
      <th style="width: 15%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">Status</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">1</td>
      <td style="border: 1px solid #a0a0a0;">Melihat Katalog Menu</td>
      <td style="border: 1px solid #a0a0a0;">Mengakses halaman utama <code>index.php</code> via browser dan menekan filter kategori menu.</td>
      <td style="border: 1px solid #a0a0a0;">Sistem menampilkan seluruh kartu menu aktif dan memfilter menu sesuai kategori secara instan.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">BERHASIL</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">2</td>
      <td style="border: 1px solid #a0a0a0;">Kelola Keranjang Belanja</td>
      <td style="border: 1px solid #a0a0a0;">Mengklik tombol "+ Tambah ke Keranjang" pada katalog menu, lalu merubah kuantitas item di halaman keranjang.</td>
      <td style="border: 1px solid #a0a0a0;">Item masuk keranjang, jumlah porsi bertambah/berkurang, subtotal harga terhitung otomatis.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">BERHASIL</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">3</td>
      <td style="border: 1px solid #a0a0a0;">Melakukan Checkout</td>
      <td style="border: 1px solid #a0a0a0;">Mengisi data diri pada form checkout dan memilih tipe pengambilan (Dine in/Delivery).</td>
      <td style="border: 1px solid #a0a0a0;">Data terkirim ke DB, pesanan baru terbuat, dan halaman dialihkan ke halaman konfirmasi bayar.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">BERHASIL</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">4</td>
      <td style="border: 1px solid #a0a0a0;">Mengunggah Bukti Bayar</td>
      <td style="border: 1px solid #a0a0a0;">Memilih berkas bukti transfer berformat JPG/JPEG/PNG berukuran &lt; 2MB lalu menekan tombol "Kirim Bukti Transfer".</td>
      <td style="border: 1px solid #a0a0a0;">File berhasil terupload ke server, status di DB berubah menjadi 'menunggu_konfirmasi'.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">BERHASIL</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">5</td>
      <td style="border: 1px solid #a0a0a0;">Memantau Status</td>
      <td style="border: 1px solid #a0a0a0;">Mengakses status pesanan via URL kode transaksi (<code>fitur_pemesanan/konfirmasi-bayar.php?kode=ALB-YYMMDD-HHMMSS</code>).</td>
      <td style="border: 1px solid #a0a0a0;">Sistem menyajikan badge status pembayaran dan status pesanan terkini secara real-time.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">BERHASIL</td>
    </tr>
  </tbody>
</table>

#### 2. Tabel Pengujian Fungsionalitas Aktor: Admin (Gabungan Seluruh Fitur Backend)
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 5%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">No</th>
      <th style="width: 20%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Fungsi yang diuji</th>
      <th style="width: 30%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Skenario Pengujian</th>
      <th style="width: 30%; font-weight: bold; text-align: left; border: 1px solid #a0a0a0;">Hasil pengujian</th>
      <th style="width: 15%; font-weight: bold; text-align: center; border: 1px solid #a0a0a0;">Status</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">1</td>
      <td style="border: 1px solid #a0a0a0;">Login Admin</td>
      <td style="border: 1px solid #a0a0a0;">Admin menginputkan username <code>admin</code> dan password <code>admin1234</code> pada form login lalu klik tombol login.</td>
      <td style="border: 1px solid #a0a0a0;">Sesi login admin berhasil dibuat dan halaman dialihkan ke dashboard panel admin.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">Berhasil</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">2</td>
      <td style="border: 1px solid #a0a0a0;">Dashboard Admin</td>
      <td style="border: 1px solid #a0a0a0;">Admin membuka halaman dashboard setelah autentikasi berhasil untuk melihat statistik penjualan.</td>
      <td style="border: 1px solid #a0a0a0;">Dashboard menyajikan ringkasan omzet bersih, total pesanan masuk, antrean dapur, dan 5 transaksi terbaru secara real-time.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">Berhasil</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">3</td>
      <td style="border: 1px solid #a0a0a0;">Tambah Menu Baru</td>
      <td style="border: 1px solid #a0a0a0;">Admin mengisi form data menu baru (nama, harga, foto, deskripsi, kategori, status stok) lalu klik simpan.</td>
      <td style="border: 1px solid #a0a0a0;">Menu baru berhasil ditambahkan ke dalam sistem beserta fotonya.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">Berhasil</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">4</td>
      <td style="border: 1px solid #a0a0a0;">Edit Data Menu</td>
      <td style="border: 1px solid #a0a0a0;">Admin mengubah harga, nama, deskripsi, atau foto pada menu yang sudah ada lalu klik simpan perubahan.</td>
      <td style="border: 1px solid #a0a0a0;">Perubahan data menu berhasil disimpan dan diperbarui.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">Berhasil</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">5</td>
      <td style="border: 1px solid #a0a0a0;">Hapus Menu</td>
      <td style="border: 1px solid #a0a0a0;">Admin menekan tombol hapus pada salah satu menu.</td>
      <td style="border: 1px solid #a0a0a0;">Menu berhasil dihapus sepenuhnya dari daftar menu sistem.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">Berhasil</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">6</td>
      <td style="border: 1px solid #a0a0a0;">Update Status Stok</td>
      <td style="border: 1px solid #a0a0a0;">Admin mengubah status ketersediaan menu dari 'Tersedia' menjadi 'Habis'.</td>
      <td style="border: 1px solid #a0a0a0;">Status stok menu berhasil diperbarui dan tampilan di katalog otomatis menyesuaikan.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">Berhasil</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">7</td>
      <td style="border: 1px solid #a0a0a0;">Tampil Antrean Pesanan</td>
      <td style="border: 1px solid #a0a0a0;">Admin membuka halaman kelola pesanan pada panel kontrol admin.</td>
      <td style="border: 1px solid #a0a0a0;">Admin dapat melihat seluruh daftar transaksi masuk beserta rincian hidangan dipesan.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">Berhasil</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">8</td>
      <td style="border: 1px solid #a0a0a0;">Modal Zoom Bukti Bayar</td>
      <td style="border: 1px solid #a0a0a0;">Admin menekan tautan/thumbnail bukti bayar pelanggan.</td>
      <td style="border: 1px solid #a0a0a0;">Gambar bukti bayar berhasil diperbesar melalui modal pop-up JavaScript.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">Berhasil</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">9</td>
      <td style="border: 1px solid #a0a0a0;">Verifikasi Status Pembayaran</td>
      <td style="border: 1px solid #a0a0a0;">Admin mengecek pesanan baru, melihat bukti bayar, lalu mengubah status bayar menjadi 'Lunas' atau 'Ditolak'.</td>
      <td style="border: 1px solid #a0a0a0;">Status pembayaran transaksi berhasil diperbarui dan tersimpan ke dalam sistem.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">Berhasil</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">10</td>
      <td style="border: 1px solid #a0a0a0;">Update Status Pesanan Dapur</td>
      <td style="border: 1px solid #a0a0a0;">Admin mengubah status pengerjaan pesanan menjadi 'Diproses', 'Selesai', atau 'Dibatalkan'.</td>
      <td style="border: 1px solid #a0a0a0;">Status alur pengerjaan pesanan dapur berhasil diperbarui dan pelacakan pelanggan otomatis menyesuaikan.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">Berhasil</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">11</td>
      <td style="border: 1px solid #a0a0a0;">Filter Status Pesanan</td>
      <td style="border: 1px solid #a0a0a0;">Admin memilih filter transaksi berdasarkan status pesanan (Pending, Diproses, Selesai).</td>
      <td style="border: 1px solid #a0a0a0;">Daftar antrean pesanan berhasil disaring sesuai kriteria status operasional.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">Berhasil</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">12</td>
      <td style="border: 1px solid #a0a0a0;">Filter Laporan Periode Tanggal</td>
      <td style="border: 1px solid #a0a0a0;">Admin memasukkan rentang tanggal (Dari Tanggal - Sampai Tanggal) lalu menekan tombol filter.</td>
      <td style="border: 1px solid #a0a0a0;">Data penjualan ditampilkan dengan benar sesuai dengan transaksi yang masuk pada rentang tanggal.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">Berhasil</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">13</td>
      <td style="border: 1px solid #a0a0a0;">Akumulasi Total Omzet</td>
      <td style="border: 1px solid #a0a0a0;">Admin melihat bagian bawah tabel rekapitulasi laporan penjualan.</td>
      <td style="border: 1px solid #a0a0a0;">Total pendapatan/omzet bersih terhitung secara akurat dan tepat oleh sistem.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">Berhasil</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">14</td>
      <td style="border: 1px solid #a0a0a0;">Cetak Laporan Penjualan</td>
      <td style="border: 1px solid #a0a0a0;">Admin menekan tombol cetak laporan pada halaman laporan penjualan.</td>
      <td style="border: 1px solid #a0a0a0;">Sistem berhasil menampilkan dialog cetak dokumen laporan penjualan secara rapi.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">Berhasil</td>
    </tr>
    <tr>
      <td style="border: 1px solid #a0a0a0; text-align: center;">15</td>
      <td style="border: 1px solid #a0a0a0;">Logout Admin</td>
      <td style="border: 1px solid #a0a0a0;">Admin menekan tombol logout pada panel navigasi admin.</td>
      <td style="border: 1px solid #a0a0a0;">Sesi autentikasi admin dihancurkan total dan halaman dialihkan kembali ke halaman utama.</td>
      <td style="border: 1px solid #a0a0a0; text-align: center; font-weight: bold; color: green;">Berhasil</td>
    </tr>
  </tbody>
</table>

---

## 📌 5.3 ANALISIS HASIL YANG DICAPAI PERANGKAT LUNAK

Analisis hasil yang dicapai bertujuan untuk mengukur sejauh mana sistem informasi yang telah dibangun berhasil menyelesaikan permasalahan operasional pada Warung Ayam Penyet Al-Barokah serta memenuhi kriteria analisis kebutuhan yang didefinisikan pada tahap perancangan.

Berdasarkan seluruh hasil implementasi dan pengujian fungsionalitas menggunakan metode *Black-Box Testing*, analisis pencapaian perangkat lunak dijabarkan sebagai berikut:

1. **Pemenuhan Kebutuhan Fungsional:**
   * **Otomatisasi Alur Transaksi:** Sistem berhasil mengintegrasikan pemesanan pelanggan (melalui Landing Page, Keranjang Belanja, dan Checkout) secara langsung ke panel kelola pesanan admin. Proses manual penulisan nota kertas berhasil digantikan dengan pencatatan digital di database MySQL.
   * **Validasi Keuangan:** Unggahan bukti pembayaran digital yang dapat ditinjau langsung oleh admin via pop-up modal mempermudah pencocokan dana masuk, sehingga mempercepat proses verifikasi pembayaran non-tunai.
   * **Keakuratan Penjumlahan Omzet:** Sistem berhasil menyajikan total rekapitulasi omzet bersih secara otomatis sesuai dengan filter rentang tanggal. Hal ini mengeliminasi kesalahan perhitungan akibat kelalaian manusia (*human error*) dan memangkas waktu penutupan kas dari jam ke menit.
   * **Penyajian Informasi Real-Time:** Pelanggan dapat memantau status pengerjaan makanan secara langsung lewat halaman pelacakan status.

2. **Pemenuhan Kebutuhan Non-Fungsional:**
   * **Usability (Kemudahan Penggunaan):** Antarmuka web terbukti responsif saat diuji pada berbagai resolusi perangkat (*desktop* kasir dan *smartphone* pelanggan). Menu navigasi yang ramah pengguna memudahkan pelanggan awam dalam memesan hidangan.
   * **Reliability (Keandalan Sistem):** Penerapan struktur data relasional dengan integrasi data transaksi induk dan data transaksi detail memastikan integritas data tetap terjaga, mencegah terjadinya kehilangan atau inkonsistensi pencatatan pesanan di basis data.
   * **Security (Keamanan Akses):** Sesi login admin menggunakan validasi data terproteksi sehingga memblokir akses ilegal langsung ke panel *dashboard* maupun kelola data tanpa autentikasi yang sah.

**Kesimpulan:**
Secara keseluruhan, Sistem Pemesanan Online Ayam Penyet Al-Barokah telah berhasil diimplementasikan sesuai dengan tujuan pengembangan awal. Sistem ini terbukti andal, akurat, dan mudah digunakan untuk menggantikan pencatatan manual nota kertas, mengamankan data transaksi keuangan, serta mempermudah rekapitulasi pelaporan penjualan pemilik warung secara efisien.
