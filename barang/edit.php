<?php
require_once '../config/database.php';

$errors = [];
$success_message = '';
$barang = null;

// Get barang ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

// Get barang data
$query = "SELECT * FROM barang WHERE id_barang = $id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    header('Location: index.php');
    exit;
}

$barang = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_barang = trim($_POST['nama_barang']);
    $harga = trim($_POST['harga']);
    $stok = trim($_POST['stok']);
    
    // Validasi
    if (empty($nama_barang)) {
        $errors[] = "Nama barang harus diisi!";
    }
    
    if (empty($harga) || !is_numeric($harga) || $harga <= 0) {
        $errors[] = "Harga harus berupa angka positif!";
    }
    
    if (empty($stok) || !is_numeric($stok) || $stok < 0) {
        $errors[] = "Stok harus berupa angka 0 atau lebih!";
    }
    
    // Convert nama barang ke huruf kapital
    $nama_barang = strtoupper($nama_barang);
    
    // Check if barang name already exists (exclude current barang)
    $check_query = "SELECT id_barang FROM barang WHERE nama_barang = '" . escape_string($nama_barang) . "' AND id_barang != $id";
    $check_result = $conn->query($check_query);
    if ($check_result->num_rows > 0) {
        $errors[] = "Barang dengan nama tersebut sudah ada!";
    }
    
    if (empty($errors)) {
        $update_query = "UPDATE barang SET 
                        nama_barang = '" . escape_string($nama_barang) . "',
                        harga = " . floatval($harga) . ",
                        stok = " . intval($stok) . "
                        WHERE id_barang = $id";
        
        if ($conn->query($update_query)) {
            $success_message = "Barang berhasil diperbarui!";
            // Refresh barang data
            $barang['nama_barang'] = $nama_barang;
            $barang['harga'] = $harga;
            $barang['stok'] = $stok;
        } else {
            $errors[] = "Gagal memperbarui barang: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Barang - Toko Savana</title>
    
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
                    <i class="fas fa-edit"></i>
                    Edit Barang
                </h1>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-edit me-2"></i> Form Edit Barang
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Alert Messages -->
                        <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle"></i> <?= $success_message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-triangle"></i> Terdapat kesalahan:
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Navigation -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="index.php">Data Barang</a></li>
                                <li class="breadcrumb-item active">Edit Barang</li>
                            </ol>
                        </nav>

                        <!-- Current Data Info -->
                        <div class="card bg-light mb-3">
                            <div class="card-header">
                                <small class="text-muted"><i class="fas fa-info-circle"></i> Data Saat Ini</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>ID:</strong> <?= $barang['id_barang'] ?>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Dibuat:</strong> <?= format_tanggal(date('Y-m-d', strtotime($barang['created_at']))) ?>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Terakhir Update:</strong> <?= format_tanggal(date('Y-m-d', strtotime($barang['updated_at']))) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form -->
                        <form method="POST" id="edit-barang-form">
                            <div class="mb-3">
                                <label for="nama_barang" class="form-label">
                                    <i class="fas fa-tag"></i> Nama Barang <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="nama_barang" name="nama_barang" 
                                       value="<?= htmlspecialchars($barang['nama_barang']) ?>" 
                                       placeholder="Masukkan nama barang" required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i> Nama barang akan otomatis diubah ke huruf kapital
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="harga" class="form-label">
                                            <i class="fas fa-money-bill-wave"></i> Harga (Rp) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control" id="harga" name="harga" 
                                               value="<?= $barang['harga'] ?>" 
                                               placeholder="0" min="1" step="1" required>
                                        <div class="form-text">Harga per unit barang</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="stok" class="form-label">
                                            <i class="fas fa-boxes"></i> Stok <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control" id="stok" name="stok" 
                                               value="<?= $barang['stok'] ?>" 
                                               placeholder="0" min="0" step="1" required>
                                        <div class="form-text">Jumlah stok yang tersedia</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview -->
                            <div class="card bg-light mb-3" id="preview-card">
                                <div class="card-header">
                                    <small class="text-muted"><i class="fas fa-eye"></i> Preview Perubahan</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Nama:</strong><br>
                                            <span id="preview-nama" class="text-primary"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Harga:</strong><br>
                                            <span id="preview-harga" class="text-success"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Stok:</strong><br>
                                            <span id="preview-stok" class="text-info"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <a href="index.php" class="btn btn-secondary w-100">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="fas fa-save"></i> Update Barang
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Format rupiah
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        }

        // Preview functionality
        function updatePreview() {
            const nama = document.getElementById('nama_barang').value.toUpperCase();
            const harga = document.getElementById('harga').value;
            const stok = document.getElementById('stok').value;
            
            document.getElementById('preview-nama').textContent = nama || '-';
            document.getElementById('preview-harga').textContent = harga ? formatRupiah(harga) : '-';
            document.getElementById('preview-stok').textContent = stok || '0';
        }

        // Add event listeners
        document.getElementById('nama_barang').addEventListener('input', updatePreview);
        document.getElementById('harga').addEventListener('input', updatePreview);
        document.getElementById('stok').addEventListener('input', updatePreview);

        // Auto convert to uppercase
        document.getElementById('nama_barang').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });

        // Initial preview update
        updatePreview();

        // Form validation
        document.getElementById('edit-barang-form').addEventListener('submit', function(e) {
            const nama = document.getElementById('nama_barang').value.trim();
            const harga = document.getElementById('harga').value;
            const stok = document.getElementById('stok').value;
            
            if (!nama) {
                e.preventDefault();
                alert('Nama barang harus diisi!');
                return;
            }
            
            if (!harga || harga <= 0) {
                e.preventDefault();
                alert('Harga harus berupa angka positif!');
                return;
            }
            
            if (stok === '' || stok < 0) {
                e.preventDefault();
                alert('Stok harus berupa angka 0 atau lebih!');
                return;
            }
        });
    </script>
    <script src="../assets/js/advanced-effects.js"></script>
</body>
</html>