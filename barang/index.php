<?php
require_once '../config/database.php';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Check if barang is used in transaksi
    $check_query = "SELECT COUNT(*) as count FROM transaksi WHERE id_barang = $id";
    $check_result = $conn->query($check_query);
    $check = $check_result->fetch_assoc();
    
    if ($check['count'] > 0) {
        $error_message = "Barang tidak dapat dihapus karena sudah digunakan dalam transaksi!";
    } else {
        $delete_query = "DELETE FROM barang WHERE id_barang = $id";
        if ($conn->query($delete_query)) {
            $success_message = "Barang berhasil dihapus!";
        } else {
            $error_message = "Gagal menghapus barang: " . $conn->error;
        }
    }
}

// Get all barang with search functionality
$search = isset($_GET['search']) ? escape_string($_GET['search']) : '';
$where_clause = $search ? "WHERE nama_barang LIKE '%$search%'" : '';

$query = "SELECT * FROM barang $where_clause ORDER BY nama_barang ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang - Toko Savana</title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#7A7A99">
    <meta name="apple-mobile-web-app-capable" content="yes">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../assets/css/global-theme.css" rel="stylesheet">
    <link href="../assets/css/enhanced-style.css" rel="stylesheet">
    <link href="../assets/css/advanced-effects.css" rel="stylesheet">
    <link href="../assets/css/animations.css" rel="stylesheet">
</head>
<body>
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
                        <a class="nav-link active" href="index.php"><i class="fas fa-box"></i> Barang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pembeli/index.php"><i class="fas fa-users"></i> Pembeli</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../transaksi/index.php"><i class="fas fa-shopping-cart"></i> Transaksi</a>
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
                    <i class="fas fa-box"></i>
                    Data Barang
                </h1>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-box me-2"></i> Manajemen Barang</h4>
                    </div>
                    <div class="card-body">
                        <!-- Alert Messages -->
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle me-2"></i><?= $success_message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-circle me-2"></i><?= $error_message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Search and Add Button -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <form method="GET" class="d-flex">
                                    <input type="text" class="form-control me-2" name="search" 
                                           placeholder="Cari nama barang..." value="<?= htmlspecialchars($search) ?>">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <?php if ($search): ?>
                                        <a href="index.php" class="btn btn-outline-secondary ms-2">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                </form>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="tambah.php" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Tambah Barang
                                </a>
                            </div>
                        </div>

                        <!-- Data Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Barang</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                        <th>Status Stok</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                                <td><?= format_rupiah($row['harga']) ?></td>
                                                <td><?= $row['stok'] ?></td>
                                                <td>
                                                    <?php if ($row['stok'] <= 5): ?>
                                                        <span class="badge bg-danger">Stok Habis</span>
                                                    <?php elseif ($row['stok'] <= 10): ?>
                                                        <span class="badge bg-warning">Stok Menipis</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Stok Aman</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="edit.php?id=<?= $row['id_barang'] ?>" 
                                                           class="btn btn-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-danger" 
                                                                onclick="confirmDelete(<?= $row['id_barang'] ?>, '<?= htmlspecialchars($row['nama_barang']) ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                <?= $search ? "Tidak ada barang yang sesuai dengan pencarian '$search'" : "Belum ada data barang" ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($result->num_rows > 0): ?>
                            <div class="text-muted">
                                Total: <?= $result->num_rows ?> barang
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
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus barang <strong id="barang-name"></strong>?</p>
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
    <script src="../assets/js/advanced-effects.js"></script>
    <script>
        function confirmDelete(id, name) {
            document.getElementById('barang-name').textContent = name;
            document.getElementById('delete-link').href = 'index.php?action=delete&id=' + id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>