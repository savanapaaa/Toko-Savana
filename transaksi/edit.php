<?php
require_once '../config/database.php';

$errors = [];
$success_message = '';
$transaksi = null;

// Get transaksi ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

// Get transaksi data
$query = "SELECT t.*, b.nama_barang, b.harga as harga_satuan, p.nama_pembeli 
          FROM transaksi t
          JOIN barang b ON t.id_barang = b.id_barang
          JOIN pembeli p ON t.id_pembeli = p.id_pembeli
          WHERE t.id_transaksi = $id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    header('Location: index.php');
    exit;
}

$transaksi = $result->fetch_assoc();

// Get pembeli options
$pembeli_query = "SELECT * FROM pembeli ORDER BY nama_pembeli ASC";
$pembeli_result = $conn->query($pembeli_query);

// Get barang options with stock (including current stock + current transaction amount)
$barang_query = "SELECT *, 
                 CASE WHEN id_barang = {$transaksi['id_barang']} 
                      THEN stok + {$transaksi['jumlah']} 
                      ELSE stok 
                 END as available_stock
                 FROM barang 
                 WHERE stok > 0 OR id_barang = {$transaksi['id_barang']}
                 ORDER BY nama_barang ASC";
$barang_result = $conn->query($barang_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pembeli = (int)$_POST['id_pembeli'];
    $id_barang = (int)$_POST['id_barang'];
    $jumlah = (int)$_POST['jumlah'];
    $tanggal = $_POST['tanggal'];
    
    // Validasi
    if (empty($id_pembeli)) {
        $errors[] = "Pembeli harus dipilih!";
    }
    
    if (empty($id_barang)) {
        $errors[] = "Barang harus dipilih!";
    }
    
    if (empty($jumlah) || $jumlah <= 0) {
        $errors[] = "Jumlah harus berupa angka positif!";
    }
    
    if (empty($tanggal)) {
        $errors[] = "Tanggal transaksi harus diisi!";
    }
    
    // Validasi tanggal tidak boleh masa depan
    if ($tanggal > date('Y-m-d')) {
        $errors[] = "Tanggal transaksi tidak boleh di masa depan!";
    }
    
    // Check if pembeli exists
    if ($id_pembeli > 0) {
        $check_pembeli = $conn->query("SELECT id_pembeli FROM pembeli WHERE id_pembeli = $id_pembeli");
        if ($check_pembeli->num_rows == 0) {
            $errors[] = "Pembeli tidak ditemukan!";
        }
    }
    
    // Check stock availability and get barang data
    if ($id_barang > 0) {
        $check_barang = $conn->query("SELECT * FROM barang WHERE id_barang = $id_barang");
        if ($check_barang->num_rows == 0) {
            $errors[] = "Barang tidak ditemukan!";
        } else {
            $barang_data = $check_barang->fetch_assoc();
            
            // Calculate available stock
            $available_stock = $barang_data['stok'];
            if ($id_barang == $transaksi['id_barang']) {
                $available_stock += $transaksi['jumlah']; // Add back current transaction amount
            }
            
            if ($available_stock < $jumlah) {
                $errors[] = "Stok tidak mencukupi! Stok tersedia: " . $available_stock . " unit";
            }
        }
    }
    
    if (empty($errors)) {
        $harga = $barang_data['harga'];
        $total_harga = $harga * $jumlah;
        
        // Start transaction
        $conn->autocommit(FALSE);
        
        try {
            // Restore old stock
            $restore_stock_query = "UPDATE barang SET stok = stok + {$transaksi['jumlah']} WHERE id_barang = {$transaksi['id_barang']}";
            $conn->query($restore_stock_query);
            
            // Update transaksi
            $update_query = "UPDATE transaksi SET 
                            id_pembeli = $id_pembeli,
                            id_barang = $id_barang,
                            jumlah = $jumlah,
                            total_harga = $total_harga,
                            tanggal = '$tanggal'
                            WHERE id_transaksi = $id";
            $conn->query($update_query);
            
            // Update new stock
            $update_stock_query = "UPDATE barang SET stok = stok - $jumlah WHERE id_barang = $id_barang";
            $conn->query($update_stock_query);
            
            $conn->commit();
            $success_message = "Transaksi berhasil diperbarui!";
            
            // Refresh transaksi data
            $refresh_query = "SELECT t.*, b.nama_barang, b.harga as harga_satuan, p.nama_pembeli 
                             FROM transaksi t
                             JOIN barang b ON t.id_barang = b.id_barang
                             JOIN pembeli p ON t.id_pembeli = p.id_pembeli
                             WHERE t.id_transaksi = $id";
            $refresh_result = $conn->query($refresh_query);
            $transaksi = $refresh_result->fetch_assoc();
            
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Gagal memperbarui transaksi: " . $e->getMessage();
        }
        
        $conn->autocommit(TRUE);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaksi - Toko Savana</title>
    
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
                    <i class="fas fa-edit"></i>
                    Edit Transaksi
                </h1>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-edit me-2"></i> Form Edit Transaksi
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
                                <li class="breadcrumb-item"><a href="index.php">Data Transaksi</a></li>
                                <li class="breadcrumb-item active">Edit Transaksi</li>
                            </ol>
                        </nav>

                        <!-- Current Data Info -->
                        <div class="card bg-light mb-3">
                            <div class="card-header">
                                <small class="text-muted"><i class="fas fa-info-circle"></i> Data Transaksi Saat Ini</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>ID:</strong> <?= $transaksi['id_transaksi'] ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Dibuat:</strong> <?= format_tanggal(date('Y-m-d', strtotime($transaksi['created_at']))) ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Total Lama:</strong> <?= format_rupiah($transaksi['total_harga']) ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Jumlah Lama:</strong> <?= $transaksi['jumlah'] ?> unit
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Form Section -->
                            <div class="col-md-8">
                                <!-- Form -->
                                <form method="POST" id="edit-transaksi-form">
                                    <div class="mb-3">
                                        <label for="id_pembeli" class="form-label">
                                            <i class="fas fa-user"></i> Pembeli <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="id_pembeli" name="id_pembeli" required>
                                            <option value="">-- Pilih Pembeli --</option>
                                            <?php while ($pembeli = $pembeli_result->fetch_assoc()): ?>
                                                <option value="<?= $pembeli['id_pembeli'] ?>" 
                                                        <?= ($transaksi['id_pembeli'] == $pembeli['id_pembeli']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($pembeli['nama_pembeli']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="id_barang" class="form-label">
                                            <i class="fas fa-box"></i> Barang <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="id_barang" name="id_barang" required>
                                            <option value="">-- Pilih Barang --</option>
                                            <?php while ($barang = $barang_result->fetch_assoc()): ?>
                                                <option value="<?= $barang['id_barang'] ?>" 
                                                        data-harga="<?= $barang['harga'] ?>"
                                                        data-stok="<?= $barang['available_stock'] ?>"
                                                        <?= ($transaksi['id_barang'] == $barang['id_barang']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($barang['nama_barang']) ?> 
                                                    (Stok: <?= $barang['available_stock'] ?> | <?= format_rupiah($barang['harga']) ?>)
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="jumlah" class="form-label">
                                                    <i class="fas fa-sort-numeric-up"></i> Jumlah <span class="text-danger">*</span>
                                                </label>
                                                <input type="number" class="form-control" id="jumlah" name="jumlah" 
                                                       value="<?= $transaksi['jumlah'] ?>" 
                                                       placeholder="0" min="1" step="1" required>
                                                <div class="form-text" id="stok-info">
                                                    <i class="fas fa-info-circle"></i> Loading...
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tanggal" class="form-label">
                                                    <i class="fas fa-calendar"></i> Tanggal Transaksi <span class="text-danger">*</span>
                                                </label>
                                                <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                                       value="<?= $transaksi['tanggal'] ?>" 
                                                       max="<?= date('Y-m-d') ?>" required>
                                                <div class="form-text">Tidak boleh di masa depan</div>
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
                                            <button type="submit" class="btn btn-warning w-100" id="submit-btn">
                                                <i class="fas fa-save"></i> Update Transaksi
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Calculator Section -->
                            <div class="col-md-4">
                                <div class="card calculator-card">
                                    <div class="card-header text-center">
                                        <h5 class="mb-0"><i class="fas fa-calculator"></i> Kalkulator</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Harga Satuan:</label>
                                            <div class="h4" id="harga-satuan">-</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Jumlah:</label>
                                            <div class="h4" id="jumlah-display">0</div>
                                        </div>
                                        <hr class="border-light">
                                        <div class="mb-3">
                                            <label class="form-label">Total Harga:</label>
                                            <div class="h3 text-warning" id="total-harga">Rp 0</div>
                                        </div>
                                        <div class="alert alert-light" id="stok-warning" style="display: none;">
                                            <small><i class="fas fa-exclamation-triangle"></i> Stok tidak mencukupi!</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stock Info -->
                                <div class="card mt-3" id="stock-card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-boxes me-2"></i> Info Stok</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="h5" id="stok-tersedia">0</div>
                                                <small>Tersedia</small>
                                            </div>
                                            <div class="col-6">
                                                <div class="h5" id="stok-sisa">0</div>
                                                <small>Sisa</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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

        let currentHarga = 0;
        let currentStok = 0;

        // Update calculator and validation
        function updateCalculator() {
            const jumlah = parseInt(document.getElementById('jumlah').value) || 0;
            const totalHarga = currentHarga * jumlah;

            document.getElementById('jumlah-display').textContent = jumlah;
            document.getElementById('total-harga').textContent = formatRupiah(totalHarga);
            
            // Update stock info
            const stokSisa = currentStok - jumlah;
            document.getElementById('stok-sisa').textContent = stokSisa;
            
            // Show/hide stock warning
            const stokWarning = document.getElementById('stok-warning');
            const submitBtn = document.getElementById('submit-btn');
            
            if (jumlah > currentStok) {
                stokWarning.style.display = 'block';
                submitBtn.disabled = true;
                document.getElementById('stok-sisa').className = 'h5 text-danger';
            } else {
                stokWarning.style.display = 'none';
                submitBtn.disabled = false;
                document.getElementById('stok-sisa').className = 'h5 text-success';
            }
        }

        // Barang selection change
        document.getElementById('id_barang').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption.value) {
                currentHarga = parseInt(selectedOption.dataset.harga);
                currentStok = parseInt(selectedOption.dataset.stok);
                
                document.getElementById('harga-satuan').textContent = formatRupiah(currentHarga);
                document.getElementById('stok-tersedia').textContent = currentStok;
                document.getElementById('stok-info').innerHTML = 
                    '<i class="fas fa-info-circle"></i> Stok tersedia: ' + currentStok + ' unit';
                document.getElementById('stok-info').className = 'form-text text-success';
                
                document.getElementById('jumlah').max = currentStok;
                
                updateCalculator();
            } else {
                currentHarga = 0;
                currentStok = 0;
                document.getElementById('harga-satuan').textContent = '-';
                document.getElementById('stok-info').innerHTML = 
                    '<i class="fas fa-info-circle"></i> Pilih barang terlebih dahulu';
                document.getElementById('stok-info').className = 'form-text';
                document.getElementById('jumlah').removeAttribute('max');
                updateCalculator();
            }
        });

        // Jumlah input change
        document.getElementById('jumlah').addEventListener('input', updateCalculator);

        // Initialize with current values
        document.getElementById('id_barang').dispatchEvent(new Event('change'));

        // Form validation
        document.getElementById('edit-transaksi-form').addEventListener('submit', function(e) {
            const pembeli = document.getElementById('id_pembeli').value;
            const barang = document.getElementById('id_barang').value;
            const jumlah = parseInt(document.getElementById('jumlah').value) || 0;
            const tanggal = document.getElementById('tanggal').value;
            
            if (!pembeli) {
                e.preventDefault();
                alert('Pembeli harus dipilih!');
                return;
            }
            
            if (!barang) {
                e.preventDefault();
                alert('Barang harus dipilih!');
                return;
            }
            
            if (jumlah <= 0) {
                e.preventDefault();
                alert('Jumlah harus berupa angka positif!');
                return;
            }
            
            if (jumlah > currentStok) {
                e.preventDefault();
                alert('Jumlah melebihi stok yang tersedia!');
                return;
            }
            
            if (!tanggal) {
                e.preventDefault();
                alert('Tanggal transaksi harus diisi!');
                return;
            }
        });
    </script>
    <script src="../assets/js/advanced-effects.js"></script>
</body>
</html>