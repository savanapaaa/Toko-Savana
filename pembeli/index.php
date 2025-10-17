<?php
require_once '../config/database.php';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Check if pembeli is used in transaksi
    $check_query = "SELECT COUNT(*) as count FROM transaksi WHERE id_pembeli = $id";
    $check_result = $conn->query($check_query);
    $check = $check_result->fetch_assoc();
    
    if ($check['count'] > 0) {
        $error_message = "Pembeli tidak dapat dihapus karena sudah memiliki transaksi!";
    } else {
        $delete_query = "DELETE FROM pembeli WHERE id_pembeli = $id";
        if ($conn->query($delete_query)) {
            $success_message = "Pembeli berhasil dihapus!";
        } else {
            $error_message = "Gagal menghapus pembeli: " . $conn->error;
        }
    }
}

// Get all pembeli with search functionality
$search = isset($_GET['search']) ? escape_string($_GET['search']) : '';
$where_clause = $search ? "WHERE nama_pembeli LIKE '%$search%' OR alamat LIKE '%$search%'" : '';

$query = "SELECT p.*, COUNT(t.id_transaksi) as total_transaksi 
          FROM pembeli p 
          LEFT JOIN transaksi t ON p.id_pembeli = t.id_pembeli 
          $where_clause 
          GROUP BY p.id_pembeli 
          ORDER BY p.nama_pembeli ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pembeli - Toko Savana</title>
    
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
                        <a class="nav-link" href="../barang/index.php"><i class="fas fa-box"></i> Barang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="fas fa-users"></i> Pembeli</a>
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
                    <i class="fas fa-users"></i>
                    Data Pembeli
                </h1>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-users me-2"></i> Manajemen Pembeli</h4>
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
                        <div class="d-flex gap-2 mb-3">
                            <a href="tambah.php" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Tambah Pembeli
                            </a>
                            <form method="GET" class="d-flex">
                                <input type="text" name="search" class="form-control me-2" placeholder="Cari pembeli atau alamat..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>

                        <!-- Data Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Pembeli</th>
                                        <th>Alamat</th>
                                        <th>Total Transaksi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td>
                                                    <i class="fas fa-user text-primary"></i>
                                                    <?= htmlspecialchars($row['nama_pembeli']) ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                                    <?= htmlspecialchars($row['alamat']) ?>
                                                </td>
                                                <td>
                                                    <?php if ($row['total_transaksi'] > 0): ?>
                                                        <span class="badge bg-success">
                                                            <?= $row['total_transaksi'] ?> transaksi
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Belum ada transaksi</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="edit.php?id=<?= $row['id_pembeli'] ?>" 
                                                           class="btn btn-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-danger" 
                                                                onclick="confirmDelete(<?= $row['id_pembeli'] ?>, '<?= htmlspecialchars($row['nama_pembeli']) ?>', <?= $row['total_transaksi'] ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                <?= $search ? "Tidak ada pembeli yang sesuai dengan pencarian '$search'" : "Belum ada data pembeli" ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($result->num_rows > 0): ?>
                            <div class="text-muted">
                                Total: <?= $result->num_rows ?> pembeli
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
                    <p>Apakah Anda yakin ingin menghapus pembeli <strong id="pembeli-name"></strong>?</p>
                    <div id="warning-transaksi" class="alert alert-warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Pembeli ini memiliki <span id="total-transaksi"></span> transaksi dan tidak dapat dihapus!
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
        function confirmDelete(id, name, totalTransaksi) {
            document.getElementById('pembeli-name').textContent = name;
            document.getElementById('total-transaksi').textContent = totalTransaksi;
            
            if (totalTransaksi > 0) {
                document.getElementById('warning-transaksi').style.display = 'block';
                document.getElementById('delete-link').style.display = 'none';
            } else {
                document.getElementById('warning-transaksi').style.display = 'none';
                document.getElementById('delete-link').style.display = 'inline-block';
                document.getElementById('delete-link').href = 'index.php?action=delete&id=' + id;
            }
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
    <script src="../assets/js/advanced-effects.js"></script>
</body>
</html>