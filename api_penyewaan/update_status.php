<?php
error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

include 'db_config.php';

$id_penyewaan = $_POST['id_penyewaan'] ?? '';
$status = $_POST['status_penyewaan'] ?? '';

if (!empty($id_penyewaan) && !empty($status)) {
    $stmt = $conn->prepare("UPDATE penyewaan SET status_penyewaan = ? WHERE id_penyewaan = ?");
    $stmt->bind_param("si", $status, $id_penyewaan);
    
    if ($stmt->execute()) {
         echo json_encode(["status" => "success", "message" => "Status berhasil diperbarui"]);
    } else {
         echo json_encode(["status" => "error", "message" => "Gagal update: " . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
}
?>