<?php
require_once '../config/database.php';

// Get filter parameters (same as laporan.php)
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

// Set headers for better display
header('Content-Type: text/html; charset=utf-8');
// Remove any caching
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Get filter description for header
$filter_desc = "Semua Data";
if ($search || $filter_tanggal_dari || $filter_tanggal_sampai || $filter_pembeli) {
    $filter_parts = [];
    if ($search) $filter_parts[] = "Pencarian: '$search'";
    if ($filter_tanggal_dari && $filter_tanggal_sampai) {
        $filter_parts[] = "Periode: " . format_tanggal($filter_tanggal_dari) . " - " . format_tanggal($filter_tanggal_sampai);
    } elseif ($filter_tanggal_dari) {
        $filter_parts[] = "Dari: " . format_tanggal($filter_tanggal_dari);
    } elseif ($filter_tanggal_sampai) {
        $filter_parts[] = "Sampai: " . format_tanggal($filter_tanggal_sampai);
    }
    if ($filter_pembeli) {
        $pembeli_query = "SELECT nama_pembeli FROM pembeli WHERE id_pembeli = $filter_pembeli";
        $pembeli_result = $conn->query($pembeli_query);
        if ($pembeli_result->num_rows > 0) {
            $pembeli_data = $pembeli_result->fetch_assoc();
            $filter_parts[] = "Pembeli: " . $pembeli_data['nama_pembeli'];
        }
    }
    $filter_desc = implode(", ", $filter_parts);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Toko Savana</title>
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            color: #3E3E50;
            background: white;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #7A7A99;
            padding-bottom: 20px;
            margin-bottom: 30px;
            background: white;
        }
        .company-name {
            font-size: 28px;
            font-weight: 700;
            color: #7A7A99;
            margin: 0;
        }
        .report-title {
            font-size: 20px;
            color: #3E3E50;
            margin: 10px 0;
            font-weight: 600;
        }
        .report-info {
            font-size: 12px;
            color: #666;
            margin: 5px 0;
        }
        .stats-section {
            margin: 20px 0;
            background: white;
            padding: 15px;
            border-left: 4px solid #7A7A99;
            border-radius: 8px;
            border: 1px solid #E6E6F0;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 10px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value {
            font-size: 18px;
            font-weight: 700;
            color: #7A7A99;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        th, td {
            border: 1px solid #E6E6F0;
            padding: 12px 8px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background-color: #7A7A99;
            color: white;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background-color: #F8F8FC;
        }
        .total-row {
            background-color: #E6E6F0 !important;
            font-weight: 600;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 40px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1 class="company-name">TOKO SAVANA</h1>
        <h2 class="report-title">LAPORAN PENJUALAN</h2>
        <div class="report-info">
            <strong>Filter:</strong> <?= htmlspecialchars($filter_desc) ?>
        </div>
        <div class="report-info">
            <strong>Dicetak pada:</strong> <?= date('d F Y, H:i:s') ?>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-section">
        <h3 style="margin-top: 0; color: #7A7A99;">RINGKASAN STATISTIK</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value"><?= number_format($stats['total_transaksi']) ?></div>
                <div class="stat-label">Total Transaksi</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= format_rupiah($stats['total_pendapatan'] ?? 0) ?></div>
                <div class="stat-label">Total Pendapatan</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= format_rupiah($stats['rata_rata_transaksi'] ?? 0) ?></div>
                <div class="stat-label">Rata-rata Transaksi</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= format_rupiah($stats['transaksi_terbesar'] ?? 0) ?></div>
                <div class="stat-label">Transaksi Terbesar</div>
            </div>
        </div>
    </div>

    <!-- Transaction Table -->
    <h3 style="color: #2ecc71; margin-top: 30px;">DETAIL TRANSAKSI</h3>
    
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 12%;">Tanggal</th>
                    <th style="width: 20%;">Pembeli</th>
                    <th style="width: 20%;">Barang</th>
                    <th style="width: 13%;">Harga Satuan</th>
                    <th style="width: 10%;">Jumlah</th>
                    <th style="width: 15%;">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1; 
                $grand_total = 0; 
                while ($row = $result->fetch_assoc()): 
                    $grand_total += $row['total_harga'];
                ?>
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
                <tr class="total-row">
                    <td colspan="6" style="text-align: right;"><strong>TOTAL KESELURUHAN:</strong></td>
                    <td><strong><?= format_rupiah($grand_total) ?></strong></td>
                </tr>
            </tbody>
        </table>
        
        <div style="margin-top: 20px; font-size: 12px; color: #666;">
            <strong>Total <?= $result->num_rows ?> transaksi ditampilkan</strong>
        </div>
    <?php else: ?>
        <div class="no-data">
            <strong>TIDAK ADA DATA TRANSAKSI</strong><br>
            <?php if ($search || $filter_tanggal_dari || $filter_tanggal_sampai || $filter_pembeli): ?>
                Tidak ada transaksi yang sesuai dengan filter yang dipilih.
            <?php else: ?>
                Belum ada transaksi yang tercatat dalam sistem.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        <hr style="margin: 20px 0;">
        <div>
            <strong>TOKO SAVANA</strong> - Sistem Manajemen Penjualan<br>
            Laporan ini digenerate secara otomatis oleh sistem pada <?= date('d F Y, H:i:s') ?>
        </div>
    </div>

    <!-- Auto print script -->
    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>