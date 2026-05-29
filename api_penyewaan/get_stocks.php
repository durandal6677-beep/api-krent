<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
include 'db_config.php';

// 1. Tangkap id_kategori yang dikirim dari StockService.dart
$id_kategori = isset($_GET['id_kategori']) ? $_GET['id_kategori'] : null;

// 2. Modifikasi query untuk menyaring berdasarkan id_kategori
if ($id_kategori !== null) {
    // Gunakan prepared statement atau minimal casting ke (int) untuk keamanan SQL Injection
    $id_kategori = (int)$id_kategori;
    $query = "SELECT * FROM kostum WHERE id_kategori = $id_kategori ORDER BY id_kostum DESC";
} else {
    // Fallback jika id_kategori tidak terkirim (menampilkan semua)
    $query = "SELECT * FROM kostum ORDER BY id_kostum DESC";
}

$result = $conn->query($query);

$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>