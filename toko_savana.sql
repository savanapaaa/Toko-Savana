-- Script SQL untuk membuat database dan tabel Toko Savana
-- Jalankan script ini di phpMyAdmin atau MySQL Command Line

-- Membuat database
CREATE DATABASE IF NOT EXISTS toko_savana;
USE toko_savana;

-- Tabel barang
CREATE TABLE barang (
    id_barang INT AUTO_INCREMENT PRIMARY KEY,
    nama_barang VARCHAR(100) NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel pembeli
CREATE TABLE pembeli (
    id_pembeli INT AUTO_INCREMENT PRIMARY KEY,
    nama_pembeli VARCHAR(100) NOT NULL,
    alamat TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel transaksi
CREATE TABLE transaksi (
    id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
    id_pembeli INT NOT NULL,
    id_barang INT NOT NULL,
    jumlah INT NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL,
    tanggal DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pembeli) REFERENCES pembeli(id_pembeli) ON DELETE CASCADE,
    FOREIGN KEY (id_barang) REFERENCES barang(id_barang) ON DELETE CASCADE
);

-- Insert data sample barang
INSERT INTO barang (nama_barang, harga, stok) VALUES
('BERAS PREMIUM', 15000, 100),
('MINYAK GORENG', 25000, 50),
('GULA PASIR', 12000, 75),
('TEPUNG TERIGU', 8000, 60),
('SUSU UHT', 5000, 40),
('KOPI BUBUK', 35000, 30),
('TEH CELUP', 18000, 45),
('SABUN MANDI', 3500, 80),
('SHAMPO', 15000, 25),
('PASTA GIGI', 8500, 35);

-- Insert data sample pembeli
INSERT INTO pembeli (nama_pembeli, alamat) VALUES
('Budi Santoso', 'Jl. Merdeka No. 123, Jakarta'),
('Siti Aminah', 'Jl. Sudirman No. 456, Bandung'),
('Ahmad Fauzi', 'Jl. Diponegoro No. 789, Surabaya'),
('Rina Sari', 'Jl. Gatot Subroto No. 321, Yogyakarta'),
('Dedi Kurniawan', 'Jl. Ahmad Yani No. 654, Medan');

-- Insert data sample transaksi
INSERT INTO transaksi (id_pembeli, id_barang, jumlah, total_harga, tanggal) VALUES
(1, 1, 2, 30000, '2024-10-01'),
(1, 3, 1, 12000, '2024-10-01'),
(2, 2, 1, 25000, '2024-10-02'),
(2, 5, 3, 15000, '2024-10-02'),
(3, 4, 2, 16000, '2024-10-03'),
(3, 6, 1, 35000, '2024-10-03'),
(4, 7, 1, 18000, '2024-10-04'),
(4, 8, 5, 17500, '2024-10-04'),
(5, 9, 1, 15000, '2024-10-05'),
(5, 10, 2, 17000, '2024-10-05');