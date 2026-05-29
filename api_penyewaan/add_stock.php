<?php
error_reporting(0); // Mencegah error PHP merusak format JSON
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include 'db_config.php'; 

$nama = $_POST['nama_kostum'] ?? '';
$id_kategori = $_POST['id_kategori'] ?? 0;
$stok = $_POST['stok'] ?? 0;
$harga = $_POST['harga_sewa'] ?? 0;
$ukuran = $_POST['ukuran'] ?? '';
$deskripsi = $_POST['deskripsi'] ?? '';

$foto_val = null;

if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['foto']['tmp_name'];
    $file_name = $_FILES['foto']['name'];
    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
    if(empty($ext)) $ext = "jpg"; 
    
    $new_foto_name = time() . "_" . uniqid() . "." . $ext;
    
    if(move_uploaded_file($tmp_name, "uploads/" . $new_foto_name)) {
        $foto_val = $new_foto_name; 
    }
}

if (!empty($nama)) {
    // Menggunakan Prepared Statement untuk menghindari Error Syntax SQL
    $stmt = $conn->prepare("INSERT INTO kostum (nama_kostum, id_kategori, stok, harga_sewa, ukuran, deskripsi, foto) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siiisss", $nama, $id_kategori, $stok, $harga, $ukuran, $deskripsi, $foto_val);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Berhasil simpan"]);
    } else {
        echo json_encode(["success" => false, "message" => "Query Error: " . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Nama Kostum tidak boleh kosong"]);
}
?>