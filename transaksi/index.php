<?php
require_once '../config/database.php';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get transaksi data first for stock restoration
    $get_transaksi_query = "SELECT * FROM transaksi WHERE id_transaksi = $id";
    $get_transaksi_result = $conn->query($get_transaksi_query);
    
    if ($get_transaksi_result->num_rows > 0) {
        $transaksi_data = $get_transaksi_result->fetch_assoc();
        
        // Start transaction
        $conn->autocommit(FALSE);
        
        try {
            // Restore stock
            $restore_stock_query = "UPDATE barang SET stok = stok + " . $transaksi_data['jumlah'] . " WHERE id_barang = " . $transaksi_data['id_barang'];
            $conn->query($restore_stock_query);
            
            // Delete transaksi
            $delete_query = "DELETE FROM transaksi WHERE id_transaksi = $id";
            $conn->query($delete_query);
            
            $conn->commit();
            $success_message = "Transaksi berhasil dihapus dan stok telah dikembalikan!";
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Gagal menghapus transaksi: " . $e->getMessage();
        }
        
        $conn->autocommit(TRUE);
    } else {
        $error_message = "Transaksi tidak ditemukan!";
    }
}

// Get all transaksi with search and filter functionality
$search = isset($_GET['search']) ? escape_string($_GET['search']) : '';
$filter_tanggal = isset($_GET['filter_tanggal']) ? $_GET['filter_tanggal'] : '';

$where_conditions = [];

if ($search) {
    $where_conditions[] = "(p.nama_pembeli LIKE '%$search%' OR b.nama_barang LIKE '%$search%')";
}

if ($filter_tanggal) {
    $where_conditions[] = "t.tanggal = '" . escape_string($filter_tanggal) . "'";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : '';

$query = "SELECT t.*, b.nama_barang, b.harga as harga_satuan, p.nama_pembeli 
          FROM transaksi t
          JOIN barang b ON t.id_barang = b.id_barang
          JOIN pembeli p ON t.id_pembeli = p.id_pembeli
          $where_clause 
          ORDER BY t.tanggal DESC, t.created_at DESC";
$result = $conn->query($query);

// Calculate totals
$total_query = "SELECT COUNT(*) as total_transaksi, SUM(total_harga) as total_pendapatan 
                FROM transaksi t
                JOIN barang b ON t.id_barang = b.id_barang
                JOIN pembeli p ON t.id_pembeli = p.id_pembeli
                $where_clause";
$total_result = $conn->query($total_query);
$totals = $total_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Transaksi - Toko Savana</title>
    
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
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-savana">
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
                        <a class="nav-link active" href="index.php"><i class="fas fa-shopping-cart"></i> Transaksi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../laporan/laporan.php"><i class="fas fa-chart-bar"></i> Laporan</a>
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
                    <i class="fas fa-shopping-cart"></i>
                    Data Transaksi
                </h1>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?= $totals['total_transaksi'] ?></h3>
                            <small class="text-muted">Total Transaksi</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?= format_rupiah($totals['total_pendapatan'] ?? 0) ?></h3>
                            <small class="text-muted">Total Pendapatan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-shopping-cart me-2"></i> Manajemen Transaksi</h4>
                    </div>
                    <div class="card-body">
                        <!-- Alert Messages -->
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?= $success_message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= $error_message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Filter and Search -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <form method="GET" class="d-flex">
                                    <input type="text" class="form-control me-2" name="search" 
                                           placeholder="Cari pembeli atau barang..." value="<?= htmlspecialchars($search) ?>">
                                    <input type="hidden" name="filter_tanggal" value="<?= htmlspecialchars($filter_tanggal) ?>">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-4">
                                <form method="GET" class="d-flex">
                                    <input type="date" class="form-control me-2" name="filter_tanggal" 
                                           value="<?= htmlspecialchars($filter_tanggal) ?>">
                                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                                    <button type="submit" class="btn btn-outline-secondary">
                                        <i class="fas fa-filter"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-4 text-end">
                                <?php if ($search || $filter_tanggal): ?>
                                    <a href="index.php" class="btn btn-outline-secondary me-2">
                                        <i class="fas fa-times"></i> Reset
                                    </a>
                                <?php endif; ?>
                                <a href="tambah.php" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Buat Transaksi
                                </a>
                            </div>
                        </div>

                        <!-- Data Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Pembeli</th>
                                        <th>Barang</th>
                                        <th>Qty</th>
                                        <th>Harga Satuan</th>
                                        <th>Total Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                                            <tr class="transaction-row">
                                                <td><?= $no++ ?></td>
                                                <td>
                                                    <i class="fas fa-calendar text-primary"></i>
                                                    <?= format_tanggal($row['tanggal']) ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-user text-info"></i>
                                                    <?= htmlspecialchars($row['nama_pembeli']) ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-box text-warning"></i>
                                                    <?= htmlspecialchars($row['nama_barang']) ?>
                                                </td>
                                                <td><?= format_rupiah($row['harga_satuan']) ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?= $row['jumlah'] ?> unit</span>
                                                </td>
                                                <td>
                                                    <strong class="text-success"><?= format_rupiah($row['total_harga']) ?></strong>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="edit.php?id=<?= $row['id_transaksi'] ?>" 
                                                           class="btn btn-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-danger" 
                                                                onclick="confirmDelete(<?= $row['id_transaksi'] ?>, '<?= htmlspecialchars($row['nama_pembeli']) ?>', '<?= htmlspecialchars($row['nama_barang']) ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">
                                                <?php if ($search || $filter_tanggal): ?>
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
                            <div class="text-muted">
                                Total: <?= $result->num_rows ?> transaksi
                                <?php if ($search || $filter_tanggal): ?>
                                    (filtered)
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Konfirmasi Hapus Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus transaksi ini?</p>
                    <div class="alert alert-info">
                        <strong>Detail Transaksi:</strong><br>
                        Pembeli: <span id="pembeli-name"></span><br>
                        Barang: <span id="barang-name"></span>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Perhatian:</strong> Stok barang akan dikembalikan setelah transaksi dihapus.
                    </div>
                    <p class="text-danger"><small><i class="fas fa-exclamation-triangle"></i> Tindakan ini tidak dapat dibatalkan!</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="delete-link" class="btn btn-danger">Ya, Hapus</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id, pembeli, barang) {
            document.getElementById('pembeli-name').textContent = pembeli;
            document.getElementById('barang-name').textContent = barang;
            document.getElementById('delete-link').href = 'index.php?action=delete&id=' + id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
    <script src="../assets/js/advanced-effects.js"></script>
</body>
</html>