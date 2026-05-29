<?php
error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include 'db_config.php';

$id = $_POST['id_kostum'] ?? '';
$nama = $_POST['nama_kostum'] ?? '';
$id_kategori = $_POST['id_kategori'] ?? 0;
$stok = $_POST['stok'] ?? 0;
$harga = $_POST['harga_sewa'] ?? 0;
$ukuran = $_POST['ukuran'] ?? '';
$deskripsi = $_POST['deskripsi'] ?? '';
$status = $_POST['status'] ?? 'Tersedia';

if (!empty($id)) {
    // Jika ada foto baru di-upload
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['foto']['tmp_name'];
        $file_name = $_FILES['foto']['name'];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        if(empty($ext)) $ext = "jpg";
        
        $new_foto_name = time() . "_" . uniqid() . "." . $ext;
        
        if(move_uploaded_file($tmp_name, "uploads/" . $new_foto_name)) {
            $stmt = $conn->prepare("UPDATE kostum SET nama_kostum=?, id_kategori=?, stok=?, harga_sewa=?, ukuran=?, deskripsi=?, status=?, foto=? WHERE id_kostum=?");
            $stmt->bind_param("siiissssi", $nama, $id_kategori, $stok, $harga, $ukuran, $deskripsi, $status, $new_foto_name, $id);
        } else {
            // Jika gagal move file, skip update foto
            $stmt = $conn->prepare("UPDATE kostum SET nama_kostum=?, id_kategori=?, stok=?, harga_sewa=?, ukuran=?, deskripsi=?, status=? WHERE id_kostum=?");
            $stmt->bind_param("siiisssi", $nama, $id_kategori, $stok, $harga, $ukuran, $deskripsi, $status, $id);
        }
    } else {
        // Jika tidak upload foto baru, jangan timpa kolom foto
        $stmt = $conn->prepare("UPDATE kostum SET nama_kostum=?, id_kategori=?, stok=?, harga_sewa=?, ukuran=?, deskripsi=?, status=? WHERE id_kostum=?");
        $stmt->bind_param("siiisssi", $nama, $id_kategori, $stok, $harga, $ukuran, $deskripsi, $status, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Berhasil update"]);
    } else {
        echo json_encode(["success" => false, "message" => "Query Error: " . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "ID tidak ditemukan"]);
}
?>