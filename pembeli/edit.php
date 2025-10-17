<?php
require_once '../config/database.php';

$errors = [];
$success_message = '';
$pembeli = null;

// Get pembeli ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

// Get pembeli data
$query = "SELECT * FROM pembeli WHERE id_pembeli = $id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    header('Location: index.php');
    exit;
}

$pembeli = $result->fetch_assoc();

// Get transaksi count
$transaksi_query = "SELECT COUNT(*) as total FROM transaksi WHERE id_pembeli = $id";
$transaksi_result = $conn->query($transaksi_query);
$total_transaksi = $transaksi_result->fetch_assoc()['total'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_pembeli = trim($_POST['nama_pembeli']);
    $alamat = trim($_POST['alamat']);
    
    // Validasi
    if (empty($nama_pembeli)) {
        $errors[] = "Nama pembeli harus diisi!";
    } elseif (strlen($nama_pembeli) < 3) {
        $errors[] = "Nama pembeli minimal 3 karakter!";
    }
    
    if (empty($alamat)) {
        $errors[] = "Alamat harus diisi!";
    } elseif (strlen($alamat) < 10) {
        $errors[] = "Alamat minimal 10 karakter!";
    }
    
    // Check if pembeli name already exists (exclude current pembeli)
    $check_query = "SELECT id_pembeli FROM pembeli WHERE nama_pembeli = '" . escape_string($nama_pembeli) . "' AND id_pembeli != $id";
    $check_result = $conn->query($check_query);
    if ($check_result->num_rows > 0) {
        $errors[] = "Pembeli dengan nama tersebut sudah ada!";
    }
    
    if (empty($errors)) {
        $update_query = "UPDATE pembeli SET 
                        nama_pembeli = '" . escape_string($nama_pembeli) . "',
                        alamat = '" . escape_string($alamat) . "'
                        WHERE id_pembeli = $id";
        
        if ($conn->query($update_query)) {
            $success_message = "Pembeli berhasil diperbarui!";
            // Refresh pembeli data
            $pembeli['nama_pembeli'] = $nama_pembeli;
            $pembeli['alamat'] = $alamat;
        } else {
            $errors[] = "Gagal memperbarui pembeli: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pembeli - Toko Savana</title>
    
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bg-savana {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
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
                    <i class="fas fa-user-edit"></i>
                    Edit Pembeli
                </h1>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-user-edit me-2"></i> Form Edit Pembeli
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
                                <li class="breadcrumb-item"><a href="index.php">Data Pembeli</a></li>
                                <li class="breadcrumb-item active">Edit Pembeli</li>
                            </ol>
                        </nav>

                        <!-- Current Data Info -->
                        <div class="card bg-light mb-3">
                            <div class="card-header">
                                <small class="text-muted"><i class="fas fa-info-circle"></i> Data Saat Ini</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>ID:</strong> <?= $pembeli['id_pembeli'] ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Total Transaksi:</strong> 
                                        <span class="badge bg-info"><?= $total_transaksi ?></span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Dibuat:</strong> <?= format_tanggal(date('Y-m-d', strtotime($pembeli['created_at']))) ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Terakhir Update:</strong> <?= format_tanggal(date('Y-m-d', strtotime($pembeli['updated_at']))) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form -->
                        <form method="POST" id="edit-pembeli-form">
                            <div class="mb-3">
                                <label for="nama_pembeli" class="form-label">
                                    <i class="fas fa-user"></i> Nama Pembeli <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="nama_pembeli" name="nama_pembeli" 
                                       value="<?= htmlspecialchars($pembeli['nama_pembeli']) ?>" 
                                       placeholder="Masukkan nama lengkap pembeli" required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i> Minimal 3 karakter
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label">
                                    <i class="fas fa-map-marker-alt"></i> Alamat <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="4" 
                                          placeholder="Masukkan alamat lengkap pembeli" required><?= htmlspecialchars($pembeli['alamat']) ?></textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i> Minimal 10 karakter - Masukkan alamat selengkap mungkin
                                </div>
                            </div>

                            <!-- Preview -->
                            <div class="card bg-light mb-3" id="preview-card">
                                <div class="card-header">
                                    <small class="text-muted"><i class="fas fa-eye"></i> Preview Perubahan</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong><i class="fas fa-user"></i> Nama:</strong><br>
                                            <span id="preview-nama" class="text-primary"></span>
                                        </div>
                                        <div class="col-md-6">
                                            <strong><i class="fas fa-map-marker-alt"></i> Alamat:</strong><br>
                                            <span id="preview-alamat" class="text-info"></span>
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
                                        <i class="fas fa-save"></i> Update Pembeli
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
        // Preview functionality
        function updatePreview() {
            const nama = document.getElementById('nama_pembeli').value.trim();
            const alamat = document.getElementById('alamat').value.trim();
            
            document.getElementById('preview-nama').textContent = nama || '-';
            document.getElementById('preview-alamat').textContent = alamat || '-';
        }

        // Add event listeners
        document.getElementById('nama_pembeli').addEventListener('input', updatePreview);
        document.getElementById('alamat').addEventListener('input', updatePreview);

        // Auto capitalize first letter of each word for nama
        document.getElementById('nama_pembeli').addEventListener('input', function() {
            this.value = this.value.replace(/\b\w/g, function(char) {
                return char.toUpperCase();
            });
        });

        // Initial preview update
        updatePreview();

        // Form validation
        document.getElementById('edit-pembeli-form').addEventListener('submit', function(e) {
            const nama = document.getElementById('nama_pembeli').value.trim();
            const alamat = document.getElementById('alamat').value.trim();
            
            if (!nama || nama.length < 3) {
                e.preventDefault();
                alert('Nama pembeli harus diisi minimal 3 karakter!');
                return;
            }
            
            if (!alamat || alamat.length < 10) {
                e.preventDefault();
                alert('Alamat harus diisi minimal 10 karakter!');
                return;
            }
        });

        // Character count for alamat
        document.getElementById('alamat').addEventListener('input', function() {
            const count = this.value.length;
            const formText = this.nextElementSibling;
            if (count < 10) {
                formText.innerHTML = '<i class="fas fa-info-circle"></i> Minimal 10 karakter - Sisa ' + (10 - count) + ' karakter lagi';
                formText.className = 'form-text text-warning';
            } else {
                formText.innerHTML = '<i class="fas fa-check-circle"></i> Alamat sudah memenuhi kriteria (' + count + ' karakter)';
                formText.className = 'form-text text-success';
            }
        });
    </script>
    <script src="../assets/js/advanced-effects.js"></script>
</body>
</html>