# 📘 BAB V: HASIL IMPLEMENTASI DAN PENGUJIAN SISTEM
## Sistem Pemesanan Online Ayam Penyet Al-Barokah

Bab ini membahas tentang hasil implementasi lingkungan pengembang, basis data, antarmuka sistem (tampilan sesuai alur kerja pengguna), serta hasil pengujian Black-Box fungsionalitas dan analisis terhadap perangkat lunak yang telah dibangun. Seluruh tabel di dalam dokumen ini menggunakan format **Word-Ready** (dapat disalin dan ditempel langsung ke Microsoft Word secara rapi).

---

## 💻 5.1 HASIL IMPLEMENTASI

Implementasi sistem merupakan tahap pengaplikasian rancangan sistem ke dalam kode program dan basis data sehingga sistem dapat dijalankan secara nyata pada lingkungan produksi.

### 5.1.1 Lingkungan Implementasi (Environment)
Sistem ini dibangun dan dijalankan pada lingkungan pengembangan dengan spesifikasi:
1. **Perangkat Keras (Hardware):**
   * Processor: Intel Core i5 / AMD Ryzen 5 (atau setara)
   * RAM: 8 GB DDR4
   * Storage: SSD (Solid State Drive)
2. **Perangkat Lunak (Software):**
   * Sistem Operasi: Windows 10/11
   * Web Server: Apache (melalui XAMPP / Laragon)
   * Database Server: MySQL / MariaDB (Port: 3306)
   * Bahasa Pemrograman: PHP Native, HTML5, CSS3, JavaScript (ES6)

### 5.1.2 Implementasi Basis Data (Database)
Basis data relasional `db_pemesanan` diimplementasikan dengan struktur tabel berikut:

#### 1. Tabel `users`
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <tr style="background-color: #f2f2f2;"><th>Nama Kolom</th><th>Tipe Data</th><th>Atribut</th><th>Keterangan</th></tr>
  <tr><td>id</td><td>INT</td><td>PK, AI</td><td>Primary Key, auto increment.</td></tr>
  <tr><td>username</td><td>VARCHAR(50)</td><td>UNIQUE</td><td>Username unik untuk login.</td></tr>
  <tr><td>password</td><td>VARCHAR(255)</td><td>-</td><td>Password pengguna (hashed).</td></tr>
  <tr><td>nama_lengkap</td><td>VARCHAR(100)</td><td>-</td><td>Nama lengkap pemilik akun.</td></tr>
  <tr><td>role</td><td>ENUM('admin','pelanggan')</td><td>-</td><td>Hak akses sistem.</td></tr>
  <tr><td>created_at</td><td>TIMESTAMP</td><td>-</td><td>Waktu pembuatan akun.</td></tr>
</table>

#### 2. Tabel `menu`
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <tr style="background-color: #f2f2f2;"><th>Nama Kolom</th><th>Tipe Data</th><th>Atribut</th><th>Keterangan</th></tr>
  <tr><td>id</td><td>INT</td><td>PK, AI</td><td>Primary Key, auto increment.</td></tr>
  <tr><td>nama_menu</td><td>VARCHAR(100)</td><td>-</td><td>Nama hidangan/minuman.</td></tr>
  <tr><td>deskripsi</td><td>TEXT</td><td>NULL</td><td>Detail komposisi menu.</td></tr>
  <tr><td>harga</td><td>INT</td><td>-</td><td>Harga dalam Rupiah.</td></tr>
  <tr><td>kategori</td><td>ENUM</td><td>-</td><td>makanan, minuman, paket, cemilan.</td></tr>
  <tr><td>foto</td><td>VARCHAR(255)</td><td>-</td><td>Nama file foto.</td></tr>
  <tr><td>status</td><td>ENUM('tersedia','habis')</td><td>-</td><td>Status stok.</td></tr>
</table>

#### 3. Tabel `pesanan`
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <tr style="background-color: #f2f2f2;"><th>Nama Kolom</th><th>Tipe Data</th><th>Atribut</th><th>Keterangan</th></tr>
  <tr><td>id</td><td>INT</td><td>PK, AI</td><td>Primary Key, auto increment.</td></tr>
  <tr><td>kode_pesanan</td><td>VARCHAR(20)</td><td>UNIQUE</td><td>Kode transaksi (ALB-...).</td></tr>
  <tr><td>user_id</td><td>INT</td><td>FK, NULL</td><td>Relasi ke tabel users.</td></tr>
  <tr><td>nama_pemesan</td><td>VARCHAR(100)</td><td>-</td><td>Nama pemesan.</td></tr>
  <tr><td>no_telepon</td><td>VARCHAR(20)</td><td>-</td><td>No kontak pelanggan.</td></tr>
  <tr><td>alamat</td><td>TEXT</td><td>NULL</td><td>Alamat pengiriman.</td></tr>
  <tr><td>tipe_pesanan</td><td>ENUM</td><td>-</td><td>dine_in, take_away, delivery.</td></tr>
  <tr><td>total_harga</td><td>INT</td><td>-</td><td>Total bayar.</td></tr>
  <tr><td>bukti_pembayaran</td><td>VARCHAR(255)</td><td>NULL</td><td>File bukti transfer.</td></tr>
  <tr><td>status_pembayaran</td><td>ENUM</td><td>-</td><td>belum_bayar, menunggu_konfirmasi, lunas, ditolak.</td></tr>
  <tr><td>status_pesanan</td><td>ENUM</td><td>-</td><td>pending, diproses, selesai, dibatalkan.</td></tr>
</table>

#### 4. Tabel `detail_pesanan`
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <tr style="background-color: #f2f2f2;"><th>Nama Kolom</th><th>Tipe Data</th><th>Atribut</th><th>Keterangan</th></tr>
  <tr><td>id</td><td>INT</td><td>PK, AI</td><td>Primary Key, auto increment.</td></tr>
  <tr><td>pesanan_id</td><td>INT</td><td>FK</td><td>Relasi ke tabel pesanan.</td></tr>
  <tr><td>menu_id</td><td>INT</td><td>FK</td><td>Relasi ke tabel menu.</td></tr>
  <tr><td>jumlah</td><td>INT</td><td>-</td><td>Kuantitas porsi item.</td></tr>
  <tr><td>harga_satuan</td><td>INT</td><td>-</td><td>Harga satuan menu.</td></tr>
</table>

---

### 5.1.1 Implementasi Tampilan Input

Tampilan input merupakan sekumpulan modul antarmuka sistem yang memfasilitasi pengguna (Pelanggan maupun Admin) untuk memasukkan, mengirimkan, atau mengubah data di dalam sistem. Berikut adalah detail implementasi tampilan input sesuai dengan alur penggunaan sistem:

#### A. Modul Tampilan Input Pelanggan (Customer)

##### 1. Form Registrasi Akun Pelanggan (`register.php`)
Form registrasi digunakan oleh calon pelanggan untuk mendaftarkan akun baru pada sistem pemesanan online Ayam Penyet Al-Barokah. Form ini menangkap identitas diri dan kredensial keamanan pengguna.

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2; text-align: left;">
      <th style="width: 22%;">Nama Elemen Input</th>
      <th style="width: 22%;">Jenis Elemen / Tipe</th>
      <th style="width: 20%;">Atribut & Validasi</th>
      <th style="width: 36%;">Fungsi & Deskripsi Usabilitas</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><b>Nama Lengkap</b></td>
      <td>Text Input (<code>type="text"</code>)</td>
      <td><code>required</code>, max 100 karakter</td>
      <td>Mengisi nama lengkap calon pelanggan sebagai identitas pemilik akun.</td>
    </tr>
    <tr>
      <td><b>Username</b></td>
      <td>Text Input (<code>type="text"</code>)</td>
      <td><code>required</code>, 4-50 karakter, unik</td>
      <td>Membuat nama pengguna unik untuk otentikasi login sistem.</td>
    </tr>
    <tr>
      <td><b>Password</b></td>
      <td>Password Input (<code>type="password"</code>)</td>
      <td><code>required</code>, min 6 karakter</td>
      <td>Membuat kata sandi enkripsi rahasia untuk keamanan akun.</td>
    </tr>
    <tr>
      <td><b>Konfirmasi Password</b></td>
      <td>Password Input (<code>type="password"</code>)</td>
      <td><code>required</code>, match check</td>
      <td>Mengulang masukan kata sandi untuk verifikasi ketepatan penulisan.</td>
    </tr>
    <tr>
      <td><b>Tombol Daftar</b></td>
      <td>Submit Button (<code>type="submit"</code>)</td>
      <td>Primary Button</td>
      <td>Mengirimkan data registrasi ke server untuk disimpan ke tabel <code>users</code>.</td>
    </tr>
  </tbody>
</table>

##### 2. Form Login Pengguna (`login.php`)
Form login berfungsi sebagai gerbang autentikasi bagi pelanggan maupun admin untuk mengonfirmasi identitas hak akses sebelum masuk ke dalam sistem.

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2; text-align: left;">
      <th style="width: 22%;">Nama Elemen Input</th>
      <th style="width: 22%;">Jenis Elemen / Tipe</th>
      <th style="width: 20%;">Atribut & Validasi</th>
      <th style="width: 36%;">Fungsi & Deskripsi Usabilitas</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><b>Username</b></td>
      <td>Text Input (<code>type="text"</code>)</td>
      <td><code>required</code>, autofocus</td>
      <td>Menginputkan username terdaftar pengguna.</td>
    </tr>
    <tr>
      <td><b>Password</b></td>
      <td>Password Input (<code>type="password"</code>)</td>
      <td><code>required</code></td>
      <td>Menginputkan kata sandi untuk dicocokkan dengan hash database.</td>
    </tr>
    <tr>
      <td><b>Tombol Masuk (Login)</b></td>
      <td>Submit Button (<code>type="submit"</code>)</td>
      <td>Primary Button</td>
      <td>Memvalidasi kredensial pengguna dan mengarahkan ke dashboard sesuai role.</td>
    </tr>
  </tbody>
</table>

##### 3. Form Keranjang Belanja & Kuantitas Pesanan (`fitur_pemesanan/keranjang.php`)
Form ini memfasilitasi pelanggan untuk mengelola daftar pesanan hidangan, mengubah porsi (kuantitas), serta menghapus menu dari keranjang.

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2; text-align: left;">
      <th style="width: 22%;">Nama Elemen Input</th>
      <th style="width: 22%;">Jenis Elemen / Tipe</th>
      <th style="width: 20%;">Atribut & Validasi</th>
      <th style="width: 36%;">Fungsi & Deskripsi Usabilitas</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><b>Input Kuantitas (Qty)</b></td>
      <td>Number Input (<code>type="number"</code>)</td>
      <td><code>min="1"</code>, <code>max="99"</code></td>
      <td>Menentukan jumlah porsi menu yang dipesan oleh pelanggan.</td>
    </tr>
    <tr>
      <td><b>Tombol Perbarui Keranjang</b></td>
      <td>Submit Button (<code>type="submit"</code>)</td>
      <td>Secondary Button</td>
      <td>Memperbarui jumlah kuantitas dan kalkulasi otomatis subtotal belanja.</td>
    </tr>
    <tr>
      <td><b>Tombol Hapus Item</b></td>
      <td>Action Link / Button</td>
      <td><code>action=delete</code></td>
      <td>Menghapus item menu dari daftar keranjang belanja.</td>
    </tr>
    <tr>
      <td><b>Tombol Lanjut Checkout</b></td>
      <td>Link Button</td>
      <td>Redirect Link</td>
      <td>Mengarahkan pengguna ke formulir pengisian data pengiriman pesanan.</td>
    </tr>
  </tbody>
</table>

##### 4. Form Pengisian Data Checkout Pesanan (`fitur_pemesanan/checkout.php`)
Form checkout digunakan untuk mengumpulkan data kontak pemesan, metode pemesanan (Dine-in, Take-away, Delivery), serta alamat pengiriman.

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2; text-align: left;">
      <th style="width: 22%;">Nama Elemen Input</th>
      <th style="width: 22%;">Jenis Elemen / Tipe</th>
      <th style="width: 20%;">Atribut & Validasi</th>
      <th style="width: 36%;">Fungsi & Deskripsi Usabilitas</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><b>Nama Pemesan</b></td>
      <td>Text Input (<code>type="text"</code>)</td>
      <td><code>required</code>, auto-filled</td>
      <td>Mengisi nama penerima/pemesan hidangan.</td>
    </tr>
    <tr>
      <td><b>Nomor Telepon / WA</b></td>
      <td>Tel Input (<code>type="tel"</code>)</td>
      <td><code>required</code>, numeric</td>
      <td>Mengisi kontak telepon/WhatsApp aktif yang dapat dihubungi.</td>
    </tr>
    <tr>
      <td><b>Tipe Pesanan</b></td>
      <td>Select Option (<code>&lt;select&gt;</code>)</td>
      <td><code>required</code> (dine_in, take_away, delivery)</td>
      <td>Memilih skenario layanan: Makan di Tempat, Bawa Pulang, atau Delivery.</td>
    </tr>
    <tr>
      <td><b>Alamat Pengiriman</b></td>
      <td>Textarea (<code>&lt;textarea&gt;</code>)</td>
      <td>Wajib jika <code>tipe_pesanan = delivery</code></td>
      <td>Mengisi alamat detail lokasi antar pesanan beserta patokan lokasi.</td>
    </tr>
    <tr>
      <td><b>Tombol Buat Pesanan</b></td>
      <td>Submit Button (<code>type="submit"</code>)</td>
      <td>Checkout Button</td>
      <td>Menggenerasi kode transaksi unik (ALB-...), menyimpan pesanan, dan lanjut pembayaran.</td>
    </tr>
  </tbody>
</table>

##### 5. Form Konfirmasi Pembayaran & Unggah Bukti Transfer (`fitur_pemesanan/konfirmasi-bayar.php`)
Form ini memfasilitasi pelanggan untuk mengunggah foto struk/bukti transfer bank atau e-wallet sebagai konfirmasi pembayaran.

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2; text-align: left;">
      <th style="width: 22%;">Nama Elemen Input</th>
      <th style="width: 22%;">Jenis Elemen / Tipe</th>
      <th style="width: 20%;">Atribut & Validasi</th>
      <th style="width: 36%;">Fungsi & Deskripsi Usabilitas</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><b>Upload Bukti Bayar</b></td>
      <td>File Input (<code>type="file"</code>)</td>
      <td>Format: JPG, JPEG, PNG | Max: 2MB</td>
      <td>Memilih berkas gambar bukti transfer dari penyimpanan lokal pengguna.</td>
    </tr>
    <tr>
      <td><b>Tombol Unggah Bukti</b></td>
      <td>Submit Button (<code>type="submit"</code>)</td>
      <td>Upload Button</td>
      <td>Mengirim berkas struk ke server dan mengarahkan status pesanan menjadi lunas/proses.</td>
    </tr>
  </tbody>
</table>

#### B. Modul Tampilan Input Admin

##### 6. Form Tambah / Edit Data Menu Admin (`admin/kelola_menu.php`)
Form ini digunakan oleh admin untuk menambahkan produk menu hidangan baru atau memperbarui informasi produk yang sudah ada.

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2; text-align: left;">
      <th style="width: 22%;">Nama Elemen Input</th>
      <th style="width: 22%;">Jenis Elemen / Tipe</th>
      <th style="width: 20%;">Atribut & Validasi</th>
      <th style="width: 36%;">Fungsi & Deskripsi Usabilitas</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><b>Nama Menu</b></td>
      <td>Text Input (<code>type="text"</code>)</td>
      <td><code>required</code>, max 100 karakter</td>
      <td>Menginputkan nama menu hidangan/minuman baru.</td>
    </tr>
    <tr>
      <td><b>Kategori Menu</b></td>
      <td>Select Option (<code>&lt;select&gt;</code>)</td>
      <td>Options: makanan, minuman, paket, cemilan</td>
      <td>Memilih pengelompokan jenis produk kuliner.</td>
    </tr>
    <tr>
      <td><b>Harga (Rp)</b></td>
      <td>Number Input (<code>type="number"</code>)</td>
      <td><code>required</code>, <code>min="0"</code></td>
      <td>Menginputkan nominal harga jual menu per porsi dalam Rupiah.</td>
    </tr>
    <tr>
      <td><b>Deskripsi Menu</b></td>
      <td>Textarea (<code>&lt;textarea&gt;</code>)</td>
      <td>Optional</td>
      <td>Mengisi keterangan bahan hidangan, rasa, atau porsi paket.</td>
    </tr>
    <tr>
      <td><b>Status Stok</b></td>
      <td>Select Option (<code>&lt;select&gt;</code>)</td>
      <td>Options: tersedia, habis</td>
      <td>Mengatur ketersediaan stok produk pada katalog pelanggan.</td>
    </tr>
    <tr>
      <td><b>Foto Menu</b></td>
      <td>File Input (<code>type="file"</code>)</td>
      <td>Format: JPG, PNG</td>
      <td>Mengunggah file foto tampilan produk hidangan.</td>
    </tr>
    <tr>
      <td><b>Tombol Simpan Menu</b></td>
      <td>Submit Button (<code>type="submit"</code>)</td>
      <td>Admin Primary Button</td>
      <td>Menyimpan data produk baru atau pembaruan menu ke dalam tabel <code>menu</code>.</td>
    </tr>
  </tbody>
</table>

##### 7. Form Update Status Pesanan & Verifikasi Pembayaran (`admin/kelola_pesanan.php`)
Form ini memfasilitasi admin untuk memvalidasi bukti pembayaran dan memperbarui progres pengerjaan pesanan.

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2; text-align: left;">
      <th style="width: 22%;">Nama Elemen Input</th>
      <th style="width: 22%;">Jenis Elemen / Tipe</th>
      <th style="width: 20%;">Atribut & Validasi</th>
      <th style="width: 36%;">Fungsi & Deskripsi Usabilitas</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><b>Status Pembayaran</b></td>
      <td>Select Option (<code>&lt;select&gt;</code>)</td>
      <td>Options: belum_bayar, menunggu_konfirmasi, lunas, ditolak</td>
      <td>Mengubah status validasi pembayaran transaksi.</td>
    </tr>
    <tr>
      <td><b>Status Pesanan</b></td>
      <td>Select Option (<code>&lt;select&gt;</code>)</td>
      <td>Options: pending, diproses, selesai, dibatalkan</td>
      <td>Memperbarui alur pengerjaan pesanan dapur hingga selesai disajikan.</td>
    </tr>
    <tr>
      <td><b>Filter Antrean Status</b></td>
      <td>Select Option / Link</td>
      <td>Options: semua, pending, diproses, selesai</td>
      <td>Menyaring daftar antrean transaksi sesuai status operasional.</td>
    </tr>
    <tr>
      <td><b>Tombol Update Status</b></td>
      <td>Submit Button (<code>type="submit"</code>)</td>
      <td>Admin Success Button</td>
      <td>Menyimpan pembaruan status transaksi ke database secara real-time.</td>
    </tr>
  </tbody>
</table>

##### 8. Form Filter Laporan Penjualan (`admin/laporan.php`)
Form ini digunakan oleh admin untuk memfilter rekapitulasi data penjualan berdasarkan rentang tanggal tertentu.

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2; text-align: left;">
      <th style="width: 22%;">Nama Elemen Input</th>
      <th style="width: 22%;">Jenis Elemen / Tipe</th>
      <th style="width: 20%;">Atribut & Validasi</th>
      <th style="width: 36%;">Fungsi & Deskripsi Usabilitas</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><b>Dari Tanggal (Start Date)</b></td>
      <td>Date Input (<code>type="date"</code>)</td>
      <td><code>required</code>, Format: YYYY-MM-DD</td>
      <td>Menentukan batas tanggal awal pencarian rekapitulasi penjualan.</td>
    </tr>
    <tr>
      <td><b>Sampai Tanggal (End Date)</b></td>
      <td>Date Input (<code>type="date"</code>)</td>
      <td><code>required</code>, Format: YYYY-MM-DD</td>
      <td>Menentukan batas tanggal akhir pencarian rekapitulasi penjualan.</td>
    </tr>
    <tr>
      <td><b>Tombol Filter Laporan</b></td>
      <td>Submit Button (<code>type="submit"</code>)</td>
      <td>Admin Primary Button</td>
      <td>Memicu proses eksekusi pencarian data omzet penjualan sesuai periode.</td>
    </tr>
    <tr>
      <td><b>Tombol Cetak Laporan</b></td>
      <td>Button (<code>onclick="window.print()"</code>)</td>
      <td>Print Button</td>
      <td>Memicu cetak dokumen fisik/PDF laporan rekapitulasi penjualan.</td>
    </tr>
  </tbody>
</table>

---

## 🧪 5.2 HASIL PENGUJIAN SISTEM

Pengujian sistem dilakukan menggunakan metode **Black Box Testing**, yaitu pengujian yang berfokus pada fungsionalitas sistem dari sudut pandang pengguna akhir tanpa memperhatikan struktur kode internal. Setiap fitur yang dikembangkan diuji berdasarkan skenario penggunaan yang telah disusun sebelumnya, mulai dari alur penggunaan oleh Pelanggan (registrasi, login, memilih menu, checkout, hingga konfirmasi bayar) serta alur pengelola oleh Admin (kelola menu, verifikasi pesanan, hingga laporan penjualan).

Pengujian ini dilakukan secara menyeluruh untuk memastikan bahwa seluruh fungsi di dalam sistem berjalan sesuai dengan spesifikasi kebutuhan fungsional yang telah ditetapkan pada Bab III dan IV. Hasil pengujian dikelompokkan secara terstruktur berdasarkan modul aktor pengguna (*Pelanggan* dan *Admin*).

---

### 5.2.1 Pengujian Modul Pelanggan (Customer)

Pengujian modul pelanggan mencakup seluruh fitur antarmuka publik dan area anggota yang diakses langsung oleh pemesan makanan.

#### 1. Pengujian Modul Registrasi Pelanggan
Pengujian ini bertujuan untuk memastikan calon pelanggan baru dapat mendaftarkan akun sebelum melakukan pemesanan hidangan di dalam sistem (`register.php`).

**Tabel 5.1 Pengujian Modul Registrasi Pelanggan**
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 5%; text-align: center;">No</th>
      <th style="width: 18%;">Fungsi yang diuji</th>
      <th style="width: 27%;">Skenario Pengujian</th>
      <th style="width: 25%;">Hasil yang diharapkan</th>
      <th style="width: 17%;">Hasil pengujian</th>
      <th style="width: 8%; text-align: center;">Status</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center;">1</td>
      <td><b>Registrasi Data Lengkap</b></td>
      <td>Pengguna mengisi form nama, username, password, dan konfirmasi password dengan lengkap lalu klik daftar</td>
      <td>Sistem menyimpan data, menampilkan pesan berhasil, lalu mengarahkan ke halaman login</td>
      <td>Sistem berhasil menyimpan data akun baru dan mengarahkan pengguna ke halaman login</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">2</td>
      <td><b>Registrasi Username Duplikat</b></td>
      <td>Pengguna mendaftar menggunakan username yang sudah terdaftar di database</td>
      <td>Sistem menolak pendaftaran dan menampilkan pesan bahwa username sudah digunakan</td>
      <td>Sistem memunculkan notifikasi bahwa username telah terdaftar</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">3</td>
      <td><b>Registrasi Form Kosong</b></td>
      <td>Pengguna mencoba menekan tombol daftar tanpa mengisi kolom wajib</td>
      <td>Sistem menahan proses dan memberikan peringatan form harus diisi</td>
      <td>Sistem menampilkan peringatan pada form yang kosong dan membatalkan pendaftaran</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">4</td>
      <td><b>Password Tidak Cocok</b></td>
      <td>Pengguna menginputkan password dan konfirmasi password dengan nilai yang berbeda</td>
      <td>Sistem menolak pendaftaran dan memberikan notifikasi konfirmasi password tidak cocok</td>
      <td>Sistem menampilkan peringatan konfirmasi password tidak cocok</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
  </tbody>
</table>

#### 2. Pengujian Modul Autentikasi Login & Logout Pelanggan
Pengujian ini bertujuan untuk menguji keamanan akses masuk dan keluar akun bagi pelanggan (`login.php` & `logout.php`).

**Tabel 5.2 Pengujian Modul Autentikasi Login & Logout Pelanggan**
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 5%; text-align: center;">No</th>
      <th style="width: 18%;">Fungsi yang diuji</th>
      <th style="width: 27%;">Skenario Pengujian</th>
      <th style="width: 25%;">Hasil yang diharapkan</th>
      <th style="width: 17%;">Hasil pengujian</th>
      <th style="width: 8%; text-align: center;">Status</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center;">1</td>
      <td><b>Login Kredensial Valid</b></td>
      <td>Pelanggan menginputkan username dan password terdaftar dengan benar lalu klik login</td>
      <td>Sistem memverifikasi akun, membuat session pelanggan, dan mengarahkan ke katalog depan</td>
      <td>Pelanggan berhasil masuk ke dalam sistem dan mengakses fitur pemesanan</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">2</td>
      <td><b>Login Password Salah</b></td>
      <td>Pelanggan memasukkan username benar tetapi password salah lalu klik masuk</td>
      <td>Sistem menolak login dan memunculkan peringatan kesalahan password</td>
      <td>Sistem menampilkan pesan peringatan "Password yang Anda masukkan salah!"</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">3</td>
      <td><b>Login Form Kosong</b></td>
      <td>Pelanggan menekan tombol masuk tanpa mengisi username atau password</td>
      <td>Sistem menahan proses dan memunculkan validasi kolom wajib diisi</td>
      <td>Sistem menampilkan peringatan pada form yang kosong</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">4</td>
      <td><b>Logout Akun</b></td>
      <td>Pelanggan mengklik tombol logout pada navigasi header web</td>
      <td>Sistem menghancurkan session login dan mengarahkan kembali ke halaman depan</td>
      <td>Sesi login berhasil diakhiri dan akun ter-logout secara aman</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
  </tbody>
</table>

#### 3. Pengujian Modul Lihat & Filter Katalog Menu
Pengujian ini dilakukan pada halaman katalog utama (`index.php`) untuk memastikan informasi daftar menu dan penyaringan kategori berjalan interaktif.

**Tabel 5.3 Pengujian Modul Lihat dan Filter Katalog Menu**
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 5%; text-align: center;">No</th>
      <th style="width: 18%;">Fungsi yang diuji</th>
      <th style="width: 27%;">Skenario Pengujian</th>
      <th style="width: 25%;">Hasil yang diharapkan</th>
      <th style="width: 17%;">Hasil pengujian</th>
      <th style="width: 8%; text-align: center;">Status</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center;">1</td>
      <td><b>Tampil Katalog Menu</b></td>
      <td>Pelanggan membuka halaman utama katalog menu warung</td>
      <td>Sistem menyajikan seluruh hidangan/minuman beserta foto, harga, deskripsi, dan status stok</td>
      <td>Sistem berhasil menampilkan seluruh daftar menu dari database secara lengkap</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">2</td>
      <td><b>Filter Kategori Menu</b></td>
      <td>Pelanggan memilih tab filter kategori (Makanan, Minuman, Paket, Cemilan)</td>
      <td>Sistem menyaring dan menampilkan daftar menu sesuai kelompok kategori terpilih</td>
      <td>Sistem berhasil menampilkan daftar menu sesuai kategori yang dipilih secara responsif</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">3</td>
      <td><b>Penanganan Stok Habis</b></td>
      <td>Pelanggan melihat produk menu yang memiliki status stok 'Habis'</td>
      <td>Sistem menampilkan badge label 'Habis' dan menonaktifkan tombol tambah keranjang</td>
      <td>Tombol tambah keranjang otomatis dinonaktifkan untuk menu berstatus habis</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
  </tbody>
</table>

#### 4. Pengujian Modul Pemesanan dan Keranjang Belanja
Pengujian dilakukan pada fitur pemesanan pelanggan yaitu saat memilih menu, mengatur porsi di keranjang belanja (`fitur_pemesanan/keranjang.php`), hingga pengisian form checkout (`fitur_pemesanan/checkout.php`).

**Tabel 5.4 Pengujian Modul Pemesanan dan Keranjang Belanja**
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 5%; text-align: center;">No</th>
      <th style="width: 18%;">Fungsi yang diuji</th>
      <th style="width: 27%;">Skenario Pengujian</th>
      <th style="width: 25%;">Hasil yang diharapkan</th>
      <th style="width: 17%;">Hasil pengujian</th>
      <th style="width: 8%; text-align: center;">Status</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center;">1</td>
      <td><b>Tambah ke Keranjang</b></td>
      <td>Pelanggan memilih menu dan menekan tombol tambah ke keranjang</td>
      <td>Item menu masuk ke dalam keranjang belanja pelanggan dan notifikasi toast dimunculkan</td>
      <td>Sistem berhasil memasukkan menu ke keranjang belanja dan memicu notifikasi pemberitahuan</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">2</td>
      <td><b>Ubah Jumlah Pesanan</b></td>
      <td>Pelanggan mengubah kuantitas (+ / - / input porsi) pada halaman keranjang</td>
      <td>Sistem menghitung ulang total harga secara otomatis berdasarkan jumlah porsi baru</td>
      <td>Total bayar langsung menyesuaikan secara akurat dengan jumlah pesanan baru</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">3</td>
      <td><b>Hapus Item Keranjang</b></td>
      <td>Pelanggan menekan tombol hapus pada salah satu item di keranjang belanja</td>
      <td>Sistem mengeluarkan item tersebut dan melakukan kalkulasi ulang total tagihan</td>
      <td>Item berhasil dihapus dari keranjang dan total bayar diperbarui secara otomatis</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">4</td>
      <td><b>Checkout Pesanan (Form Valid)</b></td>
      <td>Pelanggan mengisi nama, no HP, tipe pesanan (Dine-in/Takeaway/Delivery), dan alamat lalu klik checkout</td>
      <td>Sistem memproses pesanan, membuat ID pesanan unik (ALB-...), mengosongkan keranjang, dan mengarahkan ke pembayaran</td>
      <td>Sistem berhasil membuat ID pesanan dan menampilkan total tagihan pembayaran</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">5</td>
      <td><b>Checkout (Form Belum Lengkap)</b></td>
      <td>Pelanggan menekan checkout tanpa mengisi nomor telepon atau alamat delivery</td>
      <td>Sistem menahan proses checkout dan menampilkan notifikasi kolom wajib diisi</td>
      <td>Sistem menampilkan peringatan validasi pada kolom yang belum diisi</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
  </tbody>
</table>

#### 5. Pengujian Modul Konfirmasi Pembayaran & Pelacakan Status Pesanan
Pengujian ini mencakup alur pelanggan melihat instruksi tagihan, mengunggah bukti transfer, dan memantau status pesanan (`fitur_pemesanan/konfirmasi-bayar.php`).

**Tabel 5.5 Pengujian Modul Konfirmasi Pembayaran & Pelacakan Status**
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 5%; text-align: center;">No</th>
      <th style="width: 18%;">Fungsi yang diuji</th>
      <th style="width: 27%;">Skenario Pengujian</th>
      <th style="width: 25%;">Hasil yang diharapkan</th>
      <th style="width: 17%;">Hasil pengujian</th>
      <th style="width: 8%; text-align: center;">Status</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center;">1</td>
      <td><b>Detail Tagihan & Rekening</b></td>
      <td>Pelanggan mengarahkan halaman ke konfirmasi pembayaran setelah checkout</td>
      <td>Sistem menyajikan total tagihan akhir, nomor rekening/tujuan transfer (Platform DANA & SeaBank), serta status pesanan awal</td>
      <td>Sistem menampilkan rincian pembayaran dan nomor rekening tujuan transfer dengan jelas</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">2</td>
      <td><b>Upload Bukti Bayar Valid</b></td>
      <td>Pelanggan mengunggah foto bukti pembayaran (JPG/JPEG/PNG) dan menekan tombol kirim</td>
      <td>Sistem menyimpan bukti foto dan mengubah status pembayaran menjadi 'Menunggu Konfirmasi'</td>
      <td>Bukti bayar berhasil diunggah pelanggan dan status pesanan otomatis berubah</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">3</td>
      <td><b>Upload File Tidak Valid</b></td>
      <td>Pelanggan mengunggah berkas non-gambar atau ukuran file melampaui batas 2MB</td>
      <td>Sistem menolak unggahan berkas dan menampilkan pesan error format/ukuran file</td>
      <td>Sistem menampilkan peringatan kesalahan format/ukuran file dan membatalkan unggahan</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">4</td>
      <td><b>Pantau Status Real-Time</b></td>
      <td>Pelanggan menyegarkan/mengakses kembali halaman pelacakan status pesanan</td>
      <td>Sistem menyajikan status pembayaran (Menunggu Konfirmasi/Lunas/Ditolak) dan status pesanan (Pending/Diproses/Selesai)</td>
      <td>Status pembayaran dan status pesanan pelanggan diperbarui secara akurat secara real-time</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
  </tbody>
</table>

#### 6. Pengujian Modul Transaksi Pemesanan dan Pembayaran
Pengujian modul transaksi dilakukan secara menyeluruh terhadap seluruh siklus alur pemesanan dan pembayaran (*End-to-End Transaction Cycle*), mulai dari penambahan hidangan ke keranjang belanja, kalkulasi biaya layanan dan ongkos kirim, eksekusi pembuatan transaksi atomic pada basis data, pengunggahan bukti pembayaran via DANA/SeaBank, verifikasi pelunasan oleh admin, hingga rekapitulasi finansial pada laporan penjualan.

**Tabel 5.5b Pengujian Modul Transaksi Pemesanan dan Pembayaran**
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 5%; text-align: center;">No</th>
      <th style="width: 18%;">Fungsi yang diuji</th>
      <th style="width: 27%;">Skenario Pengujian</th>
      <th style="width: 25%;">Hasil yang diharapkan</th>
      <th style="width: 17%;">Hasil pengujian</th>
      <th style="width: 8%; text-align: center;">Status</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center;">1</td>
      <td><b>Inisialisasi Keranjang Belanja</b></td>
      <td>Pelanggan memilih hidangan pada katalog utama (<code>index.php</code>) dan menekan tombol "+ Tambah ke Keranjang"</td>
      <td>Item hidangan tersimpan ke sesi memori keranjang (<code>$_SESSION['keranjang']</code>), indikator kuantitas bertambah, dan notifikasi alert toast dimunculkan</td>
      <td>Sistem berhasil merekam item hidangan ke dalam keranjang belanja dan menampilkan notifikasi umpan balik secara instan</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">2</td>
      <td><b>Kalkulasi Subtotal & Qty Transaksi</b></td>
      <td>Pelanggan mengubah kuantitas porsi (tombol + / -) atau menghapus item pada halaman keranjang belanja (<code>fitur_pemesanan/keranjang.php</code>)</td>
      <td>Sistem menghitung ulang subtotal per item (<code>harga * qty</code>) dan memperbarui total estimasi biaya belanja secara otomatis</td>
      <td>Kalkulasi nominal transaksi diperbarui secara tepat dan akurat sesuai perubahan porsi hidangan</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">3</td>
      <td><b>Kalkulasi Biaya Layanan Delivery</b></td>
      <td>Pelanggan memilih tipe pesanan 'Delivery' (antar ke alamat) pada formulir checkout (<code>fitur_pemesanan/checkout.php</code>)</td>
      <td>Sistem memunculkan kolom input alamat pengiriman dan menambahkan ongkos kirim otomatis sebesar Rp 10.000 ke total tagihan transaksi</td>
      <td>Sistem berhasil mengkalkulasikan tambahan ongkos kirim Rp 10.000 secara akurat pada total tagihan akhir</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">4</td>
      <td><b>Eksekusi Transaksi Atomic Database</b></td>
      <td>Pelanggan melengkapi data pemesan dan mengeklik tombol "Buat Pesanan" pada formulir checkout</td>
      <td>Sistem mengeksekusi transaksi database (<code>mysqli_begin_transaction</code>), menyimpan data ke tabel <code>pesanan</code> dan <code>detail_pesanan</code>, menerbitkan kode transaksi unik (<code>ALB-YYMMDD-HHMMSS</code>), serta mengosongkan keranjang</td>
      <td>Sistem berhasil mengkomit data transaksi secara atomik tanpa error, menggenerasi kode transaksi unik, dan mengalihkan ke halaman konfirmasi bayar</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">5</td>
      <td><b>Unggah Bukti Transfer (DANA/SeaBank)</b></td>
      <td>Pelanggan mengunggah foto struk/bukti transfer bank (JPG/PNG &lt;= 2MB) pada halaman <code>fitur_pemesanan/konfirmasi-bayar.php</code></td>
      <td>Sistem menyimpan berkas ke direktori <code>bukti_bayar/</code>, meng-enkapsulasi nama berkas terstruktur, dan mengubah <code>status_pembayaran</code> menjadi 'menunggu_konfirmasi'</td>
      <td>Berkas bukti transfer berhasil terunggah dan status pembayaran transaksi diperbarui secara otomatis di basis data</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">6</td>
      <td><b>Verifikasi Pelunasan Transaksi Admin</b></td>
      <td>Admin meninjau bukti transfer via modal zoom pada <code>admin/kelola_pesanan.php</code> dan mengubah status pembayaran menjadi 'Lunas'</td>
      <td>Status pembayaran berubah menjadi 'Lunas', status pesanan dapur dapat dilanjutkan ke 'Diproses'/'Selesai', dan transaksi tercatat pada omzet bersih</td>
      <td>Admin berhasil memverifikasi keabsahan dana dan memperbarui status pelunasan transaksi secara real-time</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">7</td>
      <td><b>Penolakan Transaksi Palsu/Batal</b></td>
      <td>Admin mendapati bukti transfer tidak valid dan mengubah status pembayaran transaksi menjadi 'Ditolak'</td>
      <td>Sistem mengubah status pembayaran menjadi 'Ditolak', membatalkan akumulasi omzet, dan menampilkan informasi penolakan pada layar pelanggan</td>
      <td>Sistem berhasil membatalkan keabsahan transaksi dan memperbarui indikator penolakan pembayaran</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">8</td>
      <td><b>Rekapitulasi Omzet Transaksi & Cetak</b></td>
      <td>Admin memfilter laporan penjualan berdasarkan rentang tanggal (<code>admin/laporan.php</code>) dan mengeklik tombol "Cetak Laporan"</td>
      <td>Sistem menyajikan seluruh daftar transaksi berstatus lunas, menjumlahkan total pendapatan bersih (<code>SUM</code>), dan memicu dialog cetak printer (<code>window.print()</code>)</td>
      <td>Rekapitulasi finansial transaksi terhitung secara akurat dan tampilan siap dicetak fisik maupun dikonversi ke PDF</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
  </tbody>
</table>

---

### 5.2.2 Pengujian Modul Admin

Pengujian modul admin mencakup seluruh fitur pengelolaan produk, verifikasi transaksi keuangan, manajemen antrean pesanan dapur, hingga pencetakan laporan penjualan.

#### 1. Pengujian Modul Autentikasi Login & Akses Panel Admin
Pengujian ini dilakukan untuk memastikan otentikasi akun pengelola warung dan memproteksi halaman admin dari akses tanpa izin (`login.php`).

**Tabel 5.6 Pengujian Modul Autentikasi Login Admin**
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 5%; text-align: center;">No</th>
      <th style="width: 18%;">Fungsi yang diuji</th>
      <th style="width: 27%;">Skenario Pengujian</th>
      <th style="width: 25%;">Hasil yang diharapkan</th>
      <th style="width: 17%;">Hasil pengujian</th>
      <th style="width: 8%; text-align: center;">Status</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center;">1</td>
      <td><b>Login Admin Kredensial Valid</b></td>
      <td>Admin menginputkan username <code>admin</code> dan password yang terdaftar dengan benar</td>
      <td>Sistem memverifikasi role admin, membuat session admin, dan mengarahkan ke dashboard backend</td>
      <td>Admin berhasil masuk ke panel dashboard pengelola warung</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">2</td>
      <td><b>Login Admin Password Salah</b></td>
      <td>Admin menginputkan username <code>admin</code> tetapi password salah</td>
      <td>Sistem memblokir login dan menampilkan notifikasi pesan kesalahan</td>
      <td>Sistem menampilkan peringatan kesalahan password</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">3</td>
      <td><b>Proteksi URL Direct Access</b></td>
      <td>Pengguna non-login/pelanggan mencoba mengunduh atau mengakses URL <code>admin/index.php</code> secara langsung</td>
      <td>Sistem menolak akses halaman backend dan mengarahkan paksa kembali ke form login</td>
      <td>Sistem berhasil mengamankan halaman backend dari akses ilegal tanpa session admin</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
  </tbody>
</table>

#### 2. Pengujian Modul Manajemen Data Menu (Admin)
Pengujian ini dilakukan pada panel backend (`admin/kelola_menu.php`) untuk memastikan kelancaran pengelolaan produk menu (CRUD) dan status stok dapur.

**Tabel 5.7 Pengujian Modul Manajemen Menu (Admin)**
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 5%; text-align: center;">No</th>
      <th style="width: 18%;">Fungsi yang diuji</th>
      <th style="width: 27%;">Skenario Pengujian</th>
      <th style="width: 25%;">Hasil yang diharapkan</th>
      <th style="width: 17%;">Hasil pengujian</th>
      <th style="width: 8%; text-align: center;">Status</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center;">1</td>
      <td><b>Tambah Menu Baru</b></td>
      <td>Admin mengisi form data menu baru (nama, harga, foto, deskripsi, kategori, status stok) lalu klik simpan</td>
      <td>Sistem menyimpan data dan menampilkan menu baru di halaman daftar menu</td>
      <td>Menu baru berhasil ditambahkan ke dalam sistem beserta fotonya</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">2</td>
      <td><b>Edit Data Menu</b></td>
      <td>Admin mengubah harga, nama, deskripsi, atau foto pada menu yang sudah ada lalu klik simpan perubahan</td>
      <td>Sistem memperbarui data menu tersebut di dalam database dan tampilan web</td>
      <td>Perubahan data menu berhasil disimpan dan diperbarui</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">3</td>
      <td><b>Hapus Menu</b></td>
      <td>Admin menekan tombol hapus pada salah satu menu</td>
      <td>Sistem menampilkan konfirmasi, lalu menghapus menu dari sistem jika disetujui</td>
      <td>Menu berhasil dihapus sepenuhnya dari daftar menu sistem</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">4</td>
      <td><b>Update Status Stok</b></td>
      <td>Admin mengubah status ketersediaan menu dari 'Tersedia' menjadi 'Habis'</td>
      <td>Sistem memperbarui status stok di database dan mengubah tampilan di katalog pelanggan</td>
      <td>Status stok menu berhasil diperbarui dan tampilan di katalog otomatis menyesuaikan</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
  </tbody>
</table>

#### 3. Pengujian Modul Kelola Pesanan & Verifikasi Pembayaran (Admin)
Pengujian ini mencakup alur admin meninjau bukti bayar via modal zoom, memperbarui status keuangan, serta memperbarui alur pengerjaan pesanan dapur (`admin/kelola_pesanan.php`).

**Tabel 5.8 Pengujian Modul Kelola Pesanan & Verifikasi Pembayaran (Admin)**
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 5%; text-align: center;">No</th>
      <th style="width: 18%;">Fungsi yang diuji</th>
      <th style="width: 27%;">Skenario Pengujian</th>
      <th style="width: 25%;">Hasil yang diharapkan</th>
      <th style="width: 17%;">Hasil pengujian</th>
      <th style="width: 8%; text-align: center;">Status</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center;">1</td>
      <td><b>Tampil Antrean Pesanan</b></td>
      <td>Admin membuka halaman kelola pesanan pada panel kontrol admin</td>
      <td>Sistem menyajikan daftar tabel antrean transaksi terbaru lengkap dengan rincian pesanan</td>
      <td>Admin dapat melihat seluruh daftar transaksi masuk beserta detail hidangan dipesan</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">2</td>
      <td><b>Modal Zoom Bukti Bayar</b></td>
      <td>Admin menekan tautan/thumbnail bukti bayar pelanggan</td>
      <td>Sistem menampilkan pop-up modal zoom gambar bukti transfer secara jelas tanpa berpindah halaman</td>
      <td>Gambar bukti bayar berhasil diperbesar melalui modal pop-up JavaScript</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">3</td>
      <td><b>Verifikasi Pembayaran & Process</b></td>
      <td>Admin mengecek pesanan baru, melihat bukti bayar, lalu mengubah status bayar menjadi 'Lunas' dan status pesanan 'Diproses' / 'Selesai'</td>
      <td>Status pembayaran dan pesanan pelanggan berubah dan pesanan tercatat sebagai transaksi sukses</td>
      <td>Admin berhasil memverifikasi dan memperbarui status pesanan pelanggan</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">4</td>
      <td><b>Penolakan Pembayaran</b></td>
      <td>Admin mendapati bukti bayar tidak valid lalu mengubah status pembayaran menjadi 'Ditolak'</td>
      <td>Sistem memperbarui status pembayaran di database menjadi 'Ditolak' dan menginformasikannya ke pelanggan</td>
      <td>Status pembayaran transaksi berhasil diubah menjadi 'Ditolak' di dalam sistem</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">5</td>
      <td><b>Filter Status Antrean</b></td>
      <td>Admin memilih filter antrean transaksi berdasarkan status pesanan (Pending, Diproses, Selesai)</td>
      <td>Sistem menyaring dan menyajikan daftar pesanan sesuai dengan kategori status terpilih</td>
      <td>Daftar antrean pesanan berhasil disaring sesuai kriteria status operasional</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
  </tbody>
</table>

#### 4. Pengujian Modul Laporan Penjualan (Admin)
Pengujian dilakukan untuk menguji fitur rekapitulasi finansial harian/bulanan serta fitur pencetakan fisik dokumen laporan penjualan (`admin/laporan.php`).

**Tabel 5.9 Pengujian Modul Laporan Penjualan (Admin)**
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th style="width: 5%; text-align: center;">No</th>
      <th style="width: 18%;">Fungsi yang diuji</th>
      <th style="width: 27%;">Skenario Pengujian</th>
      <th style="width: 25%;">Hasil yang diharapkan</th>
      <th style="width: 17%;">Hasil pengujian</th>
      <th style="width: 8%; text-align: center;">Status</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: center;">1</td>
      <td><b>Filter Laporan Periode Tanggal</b></td>
      <td>Admin memasukkan rentang tanggal (Dari Tanggal - Sampai Tanggal) lalu menekan tombol filter</td>
      <td>Sistem menampilkan tabel rekapitulasi data pesanan yang berstatus lunas dan selesai pada periode tersebut</td>
      <td>Data penjualan ditampilkan dengan benar sesuai dengan transaksi yang masuk pada rentang tanggal</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">2</td>
      <td><b>Akumulasi Total Omzet</b></td>
      <td>Admin melihat bagian bawah tabel rekapitulasi laporan penjualan</td>
      <td>Sistem secara otomatis menjumlahkan total pendapatan dari seluruh transaksi yang terfilter</td>
      <td>Total pendapatan/omzet bersih terhitung secara akurat dan tepat oleh sistem</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
    <tr>
      <td style="text-align: center;">3</td>
      <td><b>Cetak Laporan Penjualan</b></td>
      <td>Admin menekan tombol cetak laporan pada halaman laporan penjualan</td>
      <td>Sistem memicu dialog cetak printer bawaan browser (window.print) dengan tata letak ramah cetakan fisik</td>
      <td>Sistem berhasil menampilkan dialog cetak dokumen laporan penjualan secara rapi</td>
      <td style="text-align: center; color: green; font-weight: bold;">Berhasil</td>
    </tr>
  </tbody>
</table>

---

## 📌 5.3 KESIMPULAN IMPLEMENTASI DAN PENGUJIAN

Berdasarkan seluruh proses implementasi antarmuka dan pengujian fungsionalitas menggunakan metode *Black-Box Testing*, dapat disimpulkan bahwa:
1. **Otomatisasi Transaksi Berhasil:** Sistem berhasil mengintegrasikan alur pemesanan pelanggan (mulai dari Katalog Menu, Keranjang, Checkout, hingga Upload Bukti Bayar) secara langsung ke panel antrean admin. Proses pencatatan manual nota kertas berhasil digantikan oleh pencatatan digital berbasis database MySQL.
2. **Kemudahan Penggunaan Antarmuka:** Tampilan antarmuka yang diimplementasikan berdasarkan alur kerja aktor berjalan dengan sangat intuitif, responsif, dan minim kendala saat diuji.
3. **Keandalan dan Fungsionalitas 100% Valid:** Seluruh modul utama yang diuji (CRUD Kelola Menu, Registrasi & Login Autentikasi, Katalog & Filter Menu, Keranjang & Checkout, Upload & Modal Zoom Bukti Bayar, Verifikasi Transaksi Admin, serta Pencetakan Laporan Penjualan) dinyatakan **100% Berhasil (Passed)** dan memenuhi seluruh spesifikasi kebutuhan fungsional Sistem Pemesanan Online Ayam Penyet Al-Barokah.


