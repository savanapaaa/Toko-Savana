# TOKO SAVANA - Aplikasi Penjualan Sederhana

Aplikasi penjualan berbasis web menggunakan PHP Native dengan fitur CRUD lengkap dan laporan penjualan.

## 📋 Deskripsi Proyek

Toko Savana adalah aplikasi manajemen penjualan sederhana yang dibuat untuk memenuhi tugas Pemrograman Basis Data. Aplikasi ini memiliki fitur lengkap untuk mengelola data barang, pembeli, dan transaksi dengan sistem laporan yang komprehensif.

## 🚀 Fitur Utama

### 1. Dashboard
- Ringkasan total transaksi dan pendapatan
- Barang terlaris berdasarkan jumlah transaksi
- Transaksi terbaru
- Notifikasi stok menipis
- Aksi cepat untuk semua modul

### 2. Manajemen Barang (CRUD)
- ✅ **Create**: Tambah barang baru dengan validasi
- 📖 **Read**: Tampilkan daftar barang dengan pencarian
- ✏️ **Update**: Edit data barang
- 🗑️ **Delete**: Hapus barang (dengan validasi relasi)
- **Validasi Khusus**:
  - Nama barang otomatis HURUF KAPITAL
  - Stok tidak boleh negatif
  - Pencegahan duplikasi nama barang

### 3. Manajemen Pembeli (CRUD)
- ✅ **Create**: Tambah pembeli baru
- 📖 **Read**: Daftar pembeli dengan info total transaksi
- ✏️ **Update**: Edit data pembeli
- 🗑️ **Delete**: Hapus pembeli (dengan validasi relasi)
- **Fitur**:
  - Pencarian berdasarkan nama dan alamat
  - Validasi minimal karakter

### 4. Manajemen Transaksi (CRUD)
- ✅ **Create**: Buat transaksi baru dengan kalkulator real-time
- 📖 **Read**: Daftar transaksi dengan filter tanggal
- ✏️ **Update**: Edit transaksi dengan update stok otomatis
- 🗑️ **Delete**: Hapus transaksi dengan restore stok
- **Validasi Khusus**:
  - Validasi stok mencukupi
  - Perhitungan total otomatis (harga × jumlah)
  - Manajemen stok otomatis

### 5. Sistem Laporan
- 📊 **Dashboard Laporan**: Statistik lengkap dengan filter
- 🔍 **Filter Lanjutan**:
  - Pencarian berdasarkan nama pembeli/barang
  - Filter berdasarkan tanggal (dari-sampai)
  - Filter berdasarkan pembeli tertentu
- 📈 **Analisis Data**:
  - Top 5 barang terlaris
  - Top 5 pembeli terbaik
  - Statistik penjualan (total, rata-rata, tertinggi)
- 🖨️ **Export & Cetak**:
  - Cetak laporan langsung dari browser
  - Export ke format PDF sederhana

## 🏗️ Struktur Database

### Tabel `barang`
```sql
- id_barang (Primary Key, Auto Increment)
- nama_barang (VARCHAR, HURUF KAPITAL)
- harga (DECIMAL, Harga per unit)
- stok (INT, ≥ 0)
- created_at, updated_at (TIMESTAMP)
```

### Tabel `pembeli`
```sql
- id_pembeli (Primary Key, Auto Increment)
- nama_pembeli (VARCHAR, Nama lengkap)
- alamat (TEXT, Alamat lengkap)
- created_at, updated_at (TIMESTAMP)
```

### Tabel `transaksi`
```sql
- id_transaksi (Primary Key, Auto Increment)
- id_pembeli (Foreign Key → pembeli)
- id_barang (Foreign Key → barang)
- jumlah (INT, Jumlah barang dibeli)
- total_harga (DECIMAL, Otomatis: harga × jumlah)
- tanggal (DATE, Tanggal transaksi)
- created_at (TIMESTAMP)
```

## 📁 Struktur File

```
Toko-Savana/
├── 📄 index.php                    # Dashboard utama
├── 📄 database.sql                 # Script SQL untuk setup database
├── 📄 README.md                    # Dokumentasi ini
├── 📁 config/
│   └── 📄 database.php             # Konfigurasi koneksi database
├── 📁 barang/
│   ├── 📄 index.php                # Daftar barang
│   ├── 📄 tambah.php               # Form tambah barang
│   └── 📄 edit.php                 # Form edit barang
├── 📁 pembeli/
│   ├── 📄 index.php                # Daftar pembeli
│   ├── 📄 tambah.php               # Form tambah pembeli
│   └── 📄 edit.php                 # Form edit pembeli
├── 📁 transaksi/
│   ├── 📄 index.php                # Daftar transaksi
│   ├── 📄 tambah.php               # Form transaksi baru
│   └── 📄 edit.php                 # Form edit transaksi
└── 📁 laporan/
    ├── 📄 laporan.php              # Dashboard laporan
    └── 📄 cetak_laporan.php        # Export PDF/Print
```

## 🛠️ Instalasi & Setup

### 1. Persyaratan Sistem
- **Web Server**: Apache/Nginx dengan PHP support
- **PHP**: Versi 7.4 atau lebih baru
- **Database**: MySQL 5.7+ atau MariaDB 10.3+
- **Browser**: Modern browser (Chrome, Firefox, Safari, Edge)

### 2. Langkah Instalasi

#### A. Setup Database
1. Buka phpMyAdmin atau MySQL command line
2. Import file `database.sql`:
   ```sql
   mysql -u root -p < database.sql
   ```
   Atau copy-paste isi file ke phpMyAdmin

#### B. Konfigurasi Database
1. Edit file `config/database.php`
2. Sesuaikan pengaturan koneksi:
   ```php
   $host = 'localhost';        # Host database
   $username = 'root';         # Username database
   $password = '';             # Password database  
   $database = 'toko_savana';  # Nama database
   ```

#### C. Setup Web Server
1. **XAMPP/WAMP/LARAGON**:
   - Copy folder `Toko-Savana` ke `htdocs/www`
   - Akses via: `http://localhost/Toko-Savana`

2. **Server Live**:
   - Upload semua file ke direktori web
   - Pastikan file permissions correct (755 untuk folder, 644 untuk file)

### 3. Data Sample
Database sudah dilengkapi dengan data sample:
- **10 Barang** dengan variasi harga dan stok
- **5 Pembeli** dengan alamat lengkap
- **10 Transaksi** untuk testing fitur laporan

## 🎯 Cara Penggunaan

### 1. Akses Dashboard
- Buka `http://localhost/Toko-Savana`
- Dashboard menampilkan ringkasan data dan aksi cepat

### 2. Kelola Data Barang
- **Tambah**: Dashboard → Tambah Barang / Menu Barang → Tambah
- **Edit**: Klik tombol edit (🖊️) pada daftar barang
- **Hapus**: Klik tombol hapus (🗑️) - akan ada konfirmasi

### 3. Kelola Data Pembeli
- Mirip dengan barang, akses melalui menu Pembeli
- Fitur pencarian tersedia untuk nama dan alamat

### 4. Buat Transaksi
- **Baru**: Menu Transaksi → Tambah / Dashboard → Buat Transaksi
- **Pilih pembeli dan barang** dari dropdown
- **Masukkan jumlah** - sistem akan validasi stok
- **Kalkulator real-time** menampilkan total harga
- **Edit/Hapus** tersedia dengan update stok otomatis

### 5. Lihat Laporan
- **Menu Laporan** untuk akses lengkap
- **Filter berdasarkan**:
  - Tanggal (dari-sampai)
  - Nama pembeli/barang
  - Pembeli tertentu
- **Export**: Tombol "Cetak" atau "Export PDF"

## 🔒 Validasi & Keamanan

### Validasi Data
- **Barang**: Nama huruf kapital, harga > 0, stok ≥ 0
- **Pembeli**: Nama min 3 karakter, alamat min 10 karakter
- **Transaksi**: Stok mencukupi, tanggal tidak masa depan

### Keamanan
- **SQL Injection**: Menggunakan `real_escape_string()`
- **XSS Protection**: `htmlspecialchars()` pada output
- **Data Validation**: Server-side dan client-side validation
- **Foreign Key**: Mencegah penghapusan data yang berelasi

## 💡 Fitur Tambahan

### 1. User Experience
- **Responsive Design** dengan Bootstrap 5
- **Interactive Elements**: Hover effects, modals, alerts
- **Real-time Calculator** pada form transaksi
- **Auto-formatting**: Nama barang ke huruf kapital, nama pembeli proper case

### 2. Business Logic
- **Stok Management**: Otomatis update saat transaksi
- **Data Integrity**: Validasi relasi antar tabel
- **Analytics**: Top products, top customers, sales statistics

### 3. Reporting
- **Multi-filter System**: Kombinasi berbagai filter
- **Print-friendly**: CSS khusus untuk print
- **Quick Date Filters**: 7 hari, 30 hari, 3 bulan terakhir

## 🐛 Troubleshooting

### Masalah Umum

1. **Error Database Connection**
   - Periksa konfigurasi di `config/database.php`
   - Pastikan MySQL service running
   - Pastikan database `toko_savana` sudah dibuat

2. **Halaman Blank/Error 500**
   - Check PHP error log
   - Pastikan PHP versi ≥ 7.4
   - Periksa file permissions

3. **Data Sample Tidak Muncul**
   - Pastikan file `database.sql` sudah diimport dengan benar
   - Check apakah ada error saat import

4. **Styling Tidak Muncul**
   - Pastikan koneksi internet untuk CDN Bootstrap & FontAwesome
   - Atau download dan simpan lokal

## 🔮 Pengembangan Lanjutan

Fitur yang bisa ditambahkan:
- **User Authentication** (Login/Register)
- **Multi-user dengan Role** (Admin, Kasir)
- **Kategori Barang** dengan gambar
- **Barcode Scanner** untuk input
- **Email Notifications** untuk stok menipis
- **Advanced Analytics** dengan chart.js
- **API Integration** untuk payment gateway
- **Mobile App** dengan PWA

## 📄 Lisensi

Project ini dibuat untuk keperluan edukasi (Tugas Pemrograman Basis Data). 
Free to use and modify for educational purposes.

## 👨‍💻 Developer

**Project**: Toko Savana - Aplikasi Penjualan Sederhana  
**Course**: Pemrograman Basis Data  
**Tech Stack**: PHP Native, MySQL, Bootstrap 5, JavaScript  
**Year**: 2025

---

### 📞 Support

Jika ada pertanyaan atau issue:
1. Check dokumentasi ini terlebih dahulu
2. Periksa konfigurasi database dan web server
3. Lihat PHP error log untuk debugging

**Happy Coding! 🚀**