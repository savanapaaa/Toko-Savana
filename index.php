<?php
require_once 'config/database.php';

// Query untuk mendapatkan statistik dashboard
$total_transaksi_query = "SELECT COUNT(*) as total FROM transaksi";
$total_transaksi_result = $conn->query($total_transaksi_query);
$total_transaksi = $total_transaksi_result->fetch_assoc()['total'];

$total_pendapatan_query = "SELECT SUM(total_harga) as total FROM transaksi";
$total_pendapatan_result = $conn->query($total_pendapatan_query);
$total_pendapatan = $total_pendapatan_result->fetch_assoc()['total'] ?? 0;

// Barang terlaris (berdasarkan jumlah transaksi)
$barang_terlaris_query = "
    SELECT b.nama_barang, COUNT(t.id_transaksi) as jumlah_transaksi, SUM(t.jumlah) as total_terjual
    FROM transaksi t 
    JOIN barang b ON t.id_barang = b.id_barang 
    GROUP BY t.id_barang 
    ORDER BY jumlah_transaksi DESC 
    LIMIT 1
";
$barang_terlaris_result = $conn->query($barang_terlaris_query);
$barang_terlaris = $barang_terlaris_result->fetch_assoc();

// Transaksi terbaru
$transaksi_terbaru_query = "
    SELECT t.*, b.nama_barang, p.nama_pembeli 
    FROM transaksi t
    JOIN barang b ON t.id_barang = b.id_barang
    JOIN pembeli p ON t.id_pembeli = p.id_pembeli
    ORDER BY t.created_at DESC
    LIMIT 5
";
$transaksi_terbaru_result = $conn->query($transaksi_terbaru_query);

// Barang stok menipis
$stok_menipis_query = "SELECT * FROM barang WHERE stok <= 10 ORDER BY stok ASC";
$stok_menipis_result = $conn->query($stok_menipis_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏪 Dashboard - Toko Savana</title>
    
    <!-- PWA Meta Tags -->
    <meta name="description" content="Sistem manajemen penjualan modern untuk mengelola barang, pembeli, dan transaksi">
    <meta name="theme-color" content="#7A7A99">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Toko Savana">
    <link rel="manifest" href="manifest.json">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'%3E%3Cpath fill='%237A7A99' d='M448 0H64C28.7 0 0 28.7 0 64v288c0 35.3 28.7 64 64 64h96v48c0 26.5 21.5 48 48 48h96c26.5 0 48-21.5 48-48v-48h96c35.3 0 64-28.7 64-64V64c0-35.3-28.7-64-64-64zm-6 304c0 3.3-2.7 6-6 6H76c-3.3 0-6-2.7-6-6V70c0-3.3 2.7-6 6-6h360c3.3 0 6 2.7 6 6v234z'/%3E%3C/svg%3E">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/global-theme.css" rel="stylesheet">
    <link href="assets/css/enhanced-style.css" rel="stylesheet">
    <link href="assets/css/advanced-effects.css" rel="stylesheet">
    <link href="assets/css/animations.css" rel="stylesheet">
    <style>
        /* Additional specific styles for dashboard */
        .welcome-section {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }
        
    <style>
        /* Additional specific styles for dashboard - Soft Gray-Purple Minimalist Theme */
        .bg-savana {
            background: #7A7A99 !important;
        }
        
        .welcome-section {
            background: rgba(245, 245, 250, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 40px;
            margin-bottom: 30px;
            border: 1px solid rgba(156, 156, 181, 0.1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .welcome-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(122, 122, 153, 0.03), transparent);
            animation: rotate 40s linear infinite;
        }
        
        .welcome-section .content {
            position: relative;
            z-index: 1;
        }
        
        .bg-savana {
            background: #7A7A99 !important;
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #9C9CB5;
            animation: pulse 3s infinite;
        }
        
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .quick-stat-item {
            background: rgba(230, 230, 240, 0.6);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            border: 1px solid rgba(156, 156, 181, 0.1);
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .quick-stat-item:hover {
            transform: translateY(-2px);
            background: rgba(217, 217, 232, 0.8);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #7A7A99;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #70707F;
            font-weight: 500;
        }
        
        .section-title {
            color: #3E3E50;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 2px;
        }
        
        @keyframes rotate {
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-savana">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-store"></i> Toko Savana
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="barang/index.php"><i class="fas fa-box"></i> Barang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pembeli/index.php"><i class="fas fa-users"></i> Pembeli</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transaksi/index.php"><i class="fas fa-shopping-cart"></i> Transaksi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="laporan/laporan.php"><i class="fas fa-chart-bar"></i> Laporan</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="content">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="section-title mb-3">
                            <i class="fas fa-store"></i> Selamat Datang di Toko Savana
                        </h1>
                        <p class="lead mb-3">
                            Sistem manajemen penjualan yang modern dan mudah digunakan untuk mengelola barang, pembeli, dan transaksi dengan efisien.
                        </p>
                        <div class="quick-stats">
                            <div class="quick-stat-item">
                                <div class="stat-number"><?= $total_transaksi ?></div>
                                <div class="stat-label">Total Transaksi</div>
                            </div>
                            <div class="quick-stat-item">
                                <div class="stat-number"><?= format_rupiah($total_pendapatan) ?></div>
                                <div class="stat-label">Total Pendapatan</div>
                            </div>
                            <div class="quick-stat-item">
                                <div class="stat-number"><?= $barang_terlaris['nama_barang'] ?? 'Belum Ada' ?></div>
                                <div class="stat-label">Barang Terlaris</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <p class="text-muted">Dashboard Analytics</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <h2 class="section-title">
                    <i class="fas fa-tachometer-alt"></i> Dashboard Toko Savana
                </h2>
            </div>
        </div>

        <!-- Statistik Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card stat-card advanced-hover floating pulse ripple">
                    <div class="card-body text-center">
                        <i class="fas fa-shopping-cart fa-3x mb-3 neon-glow"></i>
                        <h3 class="stat-number" data-format="number"><?= $total_transaksi ?></h3>
                        <p class="mb-0">Total Transaksi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card-2 advanced-hover floating pulse ripple">
                    <div class="card-body text-center">
                        <i class="fas fa-money-bill-wave fa-3x mb-3 neon-glow"></i>
                        <h3 class="stat-number" data-format="currency"><?= $total_pendapatan ?></h3>
                        <p class="mb-0">Total Pendapatan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stat-card-3 advanced-hover floating pulse ripple">
                    <div class="card-body text-center">
                        <i class="fas fa-star fa-3x mb-3 neon-glow"></i>
                        <h3 class="typing-text"><?= $barang_terlaris['nama_barang'] ?? 'Belum Ada' ?></h3>
                        <p class="mb-0">Barang Terlaris</p>
                        <?php if ($barang_terlaris): ?>
                            <small class="rainbow-text">(<?= $barang_terlaris['total_terjual'] ?> unit terjual)</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Transaksi Terbaru -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-clock"></i> Transaksi Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($transaksi_terbaru_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Pembeli</th>
                                            <th>Barang</th>
                                            <th>Jumlah</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $transaksi_terbaru_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= format_tanggal($row['tanggal']) ?></td>
                                                <td><?= $row['nama_pembeli'] ?></td>
                                                <td><?= $row['nama_barang'] ?></td>
                                                <td><?= $row['jumlah'] ?></td>
                                                <td><?= format_rupiah($row['total_harga']) ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center">
                                <a href="transaksi/index.php" class="btn btn-primary">Lihat Semua Transaksi</a>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-muted">Belum ada transaksi</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Peringatan Stok -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Stok Menipis</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($stok_menipis_result->num_rows > 0): ?>
                            <ul class="list-group list-group-flush">
                                <?php while ($row = $stok_menipis_result->fetch_assoc()): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= $row['nama_barang'] ?>
                                        <span class="badge bg-danger rounded-pill"><?= $row['stok'] ?></span>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                            <div class="text-center mt-3">
                                <a href="barang/index.php" class="btn btn-warning">Kelola Stok</a>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-muted">Semua stok aman</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-bolt"></i> Aksi Cepat</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <a href="barang/tambah.php" class="btn btn-outline-primary btn-lg w-100 ripple magnetic-btn zoom-in">
                                    <i class="fas fa-plus-circle"></i><br>
                                    Tambah Barang
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="pembeli/tambah.php" class="btn btn-outline-info btn-lg w-100 ripple magnetic-btn zoom-in">
                                    <i class="fas fa-user-plus"></i><br>
                                    Tambah Pembeli
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="transaksi/tambah.php" class="btn btn-outline-success btn-lg w-100 ripple magnetic-btn zoom-in">
                                    <i class="fas fa-shopping-cart"></i><br>
                                    Buat Transaksi
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="laporan/laporan.php" class="btn btn-outline-warning btn-lg w-100 ripple magnetic-btn zoom-in">
                                    <i class="fas fa-chart-line"></i><br>
                                    Lihat Laporan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/advanced-effects.js"></script>
</body>
</html>