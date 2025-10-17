<?php
require_once '../config/database.php';

// Get filter parameters
$search = isset($_GET['search']) ? escape_string($_GET['search']) : '';
$filter_tanggal_dari = isset($_GET['tanggal_dari']) ? $_GET['tanggal_dari'] : '';
$filter_tanggal_sampai = isset($_GET['tanggal_sampai']) ? $_GET['tanggal_sampai'] : '';
$filter_pembeli = isset($_GET['filter_pembeli']) ? (int)$_GET['filter_pembeli'] : '';

// Build WHERE conditions
$where_conditions = [];

if ($search) {
    $where_conditions[] = "(p.nama_pembeli LIKE '%$search%' OR b.nama_barang LIKE '%$search%')";
}

if ($filter_tanggal_dari && $filter_tanggal_sampai) {
    $where_conditions[] = "t.tanggal BETWEEN '" . escape_string($filter_tanggal_dari) . "' AND '" . escape_string($filter_tanggal_sampai) . "'";
} elseif ($filter_tanggal_dari) {
    $where_conditions[] = "t.tanggal >= '" . escape_string($filter_tanggal_dari) . "'";
} elseif ($filter_tanggal_sampai) {
    $where_conditions[] = "t.tanggal <= '" . escape_string($filter_tanggal_sampai) . "'";
}

if ($filter_pembeli) {
    $where_conditions[] = "t.id_pembeli = $filter_pembeli";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : '';

// Main query for transactions
$query = "SELECT t.*, b.nama_barang, b.harga as harga_satuan, p.nama_pembeli, p.alamat
          FROM transaksi t
          JOIN barang b ON t.id_barang = b.id_barang
          JOIN pembeli p ON t.id_pembeli = p.id_pembeli
          $where_clause 
          ORDER BY t.tanggal DESC, t.created_at DESC";
$result = $conn->query($query);

// Calculate statistics
$stats_query = "SELECT 
                COUNT(*) as total_transaksi,
                SUM(total_harga) as total_pendapatan,
                AVG(total_harga) as rata_rata_transaksi,
                MIN(total_harga) as transaksi_terkecil,
                MAX(total_harga) as transaksi_terbesar
                FROM transaksi t
                JOIN barang b ON t.id_barang = b.id_barang
                JOIN pembeli p ON t.id_pembeli = p.id_pembeli
                $where_clause";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Top 5 barang terlaris
$top_barang_query = "SELECT b.nama_barang, COUNT(t.id_transaksi) as jumlah_transaksi, SUM(t.jumlah) as total_terjual, SUM(t.total_harga) as total_pendapatan
                     FROM transaksi t
                     JOIN barang b ON t.id_barang = b.id_barang
                     JOIN pembeli p ON t.id_pembeli = p.id_pembeli
                     $where_clause
                     GROUP BY t.id_barang
                     ORDER BY total_terjual DESC
                     LIMIT 5";
$top_barang_result = $conn->query($top_barang_query);

// Top 5 pembeli
$top_pembeli_query = "SELECT p.nama_pembeli, COUNT(t.id_transaksi) as jumlah_transaksi, SUM(t.total_harga) as total_belanja
                      FROM transaksi t
                      JOIN barang b ON t.id_barang = b.id_barang
                      JOIN pembeli p ON t.id_pembeli = p.id_pembeli
                      $where_clause
                      GROUP BY t.id_pembeli
                      ORDER BY total_belanja DESC
                      LIMIT 5";
$top_pembeli_result = $conn->query($top_pembeli_query);

// Get all pembeli for filter dropdown
$all_pembeli_query = "SELECT * FROM pembeli ORDER BY nama_pembeli ASC";
$all_pembeli_result = $conn->query($all_pembeli_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Toko Savana</title>
    
    <!-- PWA Meta Tags -->
    <meta name="description" content="Toko Savana - Sistem Manajemen Toko Modern">
    <meta name="theme-color" content="#7A7A99">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Toko Savana">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Theme CSS -->
    <link href="../assets/css/global-theme.css" rel="stylesheet">
    <link href="../assets/css/enhanced-style.css" rel="stylesheet">
    <link href="../assets/css/advanced-effects.css" rel="stylesheet">
    <link href="../assets/css/animations.css" rel="stylesheet">
    
    <style>
        .print-hide {
            display: block;
        }
        @media print {
            .print-hide {
                display: none !important;
            }
            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
        }
    </style>
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-savana print-hide">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-store"></i> Toko Savana
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../barang/index.php"><i class="fas fa-box"></i> Barang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pembeli/index.php"><i class="fas fa-users"></i> Pembeli</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../transaksi/index.php"><i class="fas fa-shopping-cart"></i> Transaksi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="page-title">
                    <i class="fas fa-chart-line"></i>
                    Laporan Penjualan
                </h1>
            </div>
        </div>

        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="mb-0"><i class="fas fa-chart-line me-2"></i> Laporan Penjualan</h4>
                            </div>
                            <div class="col-auto print-hide">
                                <button onclick="window.print()" class="btn btn-outline-primary btn-sm me-2">
                                    <i class="fas fa-print me-1"></i> Cetak
                                </button>
                                <a href="cetak_laporan.php<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>" 
                                   target="_blank" class="btn btn-primary btn-sm">
                                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="row mb-4 print-hide">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i> Filter Laporan</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" id="filter-form">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Cari Pembeli/Barang</label>
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Nama pembeli atau barang..." value="<?= htmlspecialchars($search) ?>">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Dari Tanggal</label>
                                    <input type="date" class="form-control" name="tanggal_dari" 
                                           value="<?= htmlspecialchars($filter_tanggal_dari) ?>">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Sampai Tanggal</label>
                                    <input type="date" class="form-control" name="tanggal_sampai" 
                                           value="<?= htmlspecialchars($filter_tanggal_sampai) ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Pembeli Tertentu</label>
                                    <select class="form-select" name="filter_pembeli">
                                        <option value="">-- Semua Pembeli --</option>
                                        <?php while ($pembeli = $all_pembeli_result->fetch_assoc()): ?>
                                            <option value="<?= $pembeli['id_pembeli'] ?>" 
                                                    <?= ($filter_pembeli == $pembeli['id_pembeli']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($pembeli['nama_pembeli']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <?php if ($search || $filter_tanggal_dari || $filter_tanggal_sampai || $filter_pembeli): ?>
                                        <a href="laporan.php" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-times"></i> Reset Filter
                                        </a>
                                        <span class="text-muted ms-3">
                                            <i class="fas fa-info-circle"></i> 
                                            Menampilkan data yang difilter
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?= number_format($stats['total_transaksi']) ?></h3>
                            <small class="text-muted">Total Transaksi</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?= format_rupiah($stats['total_pendapatan'] ?? 0) ?></h3>
                            <small class="text-muted">Total Pendapatan</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?= format_rupiah($stats['rata_rata_transaksi'] ?? 0) ?></h3>
                            <small class="text-muted">Rata-rata Transaksi</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?= format_rupiah($stats['transaksi_terbesar'] ?? 0) ?></h3>
                            <small class="text-muted">Transaksi Terbesar</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Top Barang -->
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i> Top 5 Barang Terlaris</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($top_barang_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Barang</th>
                                            <th>Terjual</th>
                                            <th>Pendapatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $rank = 1; while ($row = $top_barang_result->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($rank == 1): ?>
                                                        <i class="fas fa-trophy text-warning"></i>
                                                    <?php elseif ($rank == 2): ?>
                                                        <i class="fas fa-medal text-secondary"></i>
                                                    <?php elseif ($rank == 3): ?>
                                                        <i class="fas fa-award text-warning"></i>
                                                    <?php else: ?>
                                                        <?= $rank ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                                <td><?= $row['total_terjual'] ?> unit</td>
                                                <td><?= format_rupiah($row['total_pendapatan']) ?></td>
                                            </tr>
                                            <?php $rank++; ?>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">Tidak ada data</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Top Pembeli -->
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i> Top 5 Pembeli</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($top_pembeli_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Pembeli</th>
                                            <th>Transaksi</th>
                                            <th>Total Belanja</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $rank = 1; while ($row = $top_pembeli_result->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($rank == 1): ?>
                                                        <i class="fas fa-crown text-warning"></i>
                                                    <?php elseif ($rank == 2): ?>
                                                        <i class="fas fa-star text-info"></i>
                                                    <?php elseif ($rank == 3): ?>
                                                        <i class="fas fa-gem text-primary"></i>
                                                    <?php else: ?>
                                                        <?= $rank ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($row['nama_pembeli']) ?></td>
                                                <td><?= $row['jumlah_transaksi'] ?>x</td>
                                                <td><?= format_rupiah($row['total_belanja']) ?></td>
                                            </tr>
                                            <?php $rank++; ?>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">Tidak ada data</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Transaction List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i> Detail Transaksi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Pembeli</th>
                                        <th>Barang</th>
                                        <th>Harga Satuan</th>
                                        <th>Jumlah</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php $no = 1; $grand_total = 0; while ($row = $result->fetch_assoc()): ?>
                                            <?php $grand_total += $row['total_harga']; ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= format_tanggal($row['tanggal']) ?></td>
                                                <td><?= htmlspecialchars($row['nama_pembeli']) ?></td>
                                                <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                                <td><?= format_rupiah($row['harga_satuan']) ?></td>
                                                <td><?= $row['jumlah'] ?> unit</td>
                                                <td><?= format_rupiah($row['total_harga']) ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <tr class="table-info">
                                            <td colspan="6"><strong>TOTAL KESELURUHAN</strong></td>
                                            <td><strong><?= format_rupiah($grand_total) ?></strong></td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">
                                                <?php if ($search || $filter_tanggal_dari || $filter_tanggal_sampai || $filter_pembeli): ?>
                                                    Tidak ada transaksi yang sesuai dengan filter
                                                <?php else: ?>
                                                    Belum ada data transaksi
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($result->num_rows > 0): ?>
                            <div class="text-muted mt-3">
                                <i class="fas fa-info-circle"></i> 
                                Menampilkan <?= $result->num_rows ?> transaksi
                                <?php if ($search || $filter_tanggal_dari || $filter_tanggal_sampai || $filter_pembeli): ?>
                                    (hasil filter)
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer untuk print -->
        <div class="row mt-4" style="display: none;">
            <div class="col-12 text-center">
                <hr>
                <small class="text-muted">
                    Laporan dicetak pada: <?= date('d F Y H:i:s') ?> | 
                    Toko Savana - Sistem Penjualan
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show footer when printing
        window.addEventListener('beforeprint', function() {
            document.querySelector('.row.mt-4').style.display = 'block';
        });

        window.addEventListener('afterprint', function() {
            document.querySelector('.row.mt-4').style.display = 'none';
        });

        // Quick date filters
        function setDateFilter(days) {
            const today = new Date();
            const fromDate = new Date();
            fromDate.setDate(today.getDate() - days);
            
            document.querySelector('input[name="tanggal_dari"]').value = fromDate.toISOString().split('T')[0];
            document.querySelector('input[name="tanggal_sampai"]').value = today.toISOString().split('T')[0];
        }

        // Add quick filter buttons
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filter-form');
            const quickFilters = document.createElement('div');
            quickFilters.className = 'mt-2';
            quickFilters.innerHTML = `
                <small class="text-muted">Filter Cepat: </small>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateFilter(7)">7 Hari Terakhir</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateFilter(30)">30 Hari Terakhir</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateFilter(90)">3 Bulan Terakhir</button>
            `;
            filterForm.appendChild(quickFilters);
        });
    </script>
    <script src="../assets/js/advanced-effects.js"></script>
</body>
</html>