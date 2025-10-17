<?php
// Database Configuration Template
// Copy this file to database.php and update with your actual database credentials

$host = "localhost";        // Database host
$username = "root";         // Database username
$password = "";             // Database password  
$database = "toko_savana";  // Database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8");

// Function to escape string for security
function escape_string($string) {
    global $conn;
    return $conn->real_escape_string($string);
}

// Function to format rupiah
function format_rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Function to format tanggal
function format_tanggal($tanggal) {
    $bulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
        '04' => 'April', '05' => 'Mei', '06' => 'Juni',
        '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[$split[1]] . ' ' . $split[0];
}
?>