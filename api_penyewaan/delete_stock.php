<?php
ob_start(); 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

include 'koneksi.php';

$id_kostum = isset($_POST['id_kostum']) ? $_POST['id_kostum'] : null;
$response = []; 

if ($id_kostum) {
    // --- 1. CARI NAMA FILE FOTO SEBELUM DATA DIHAPUS ---
    $stmt_foto = $conn->prepare("SELECT foto FROM kostum WHERE id_kostum = ?");
    $stmt_foto->bind_param("i", $id_kostum);
    $stmt_foto->execute();
    $result_foto = $stmt_foto->get_result();
    $nama_file = "";
    if ($row = $result_foto->fetch_assoc()) {
        $nama_file = $row['foto'];
    }
    $stmt_foto->close();
    // ---------------------------------------------------

    $stmt = $conn->prepare("DELETE FROM kostum WHERE id_kostum = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id_kostum);
        
        try {
            $stmt->execute();
            
            // --- 2. JIKA DATABASE BERHASIL DIHAPUS, HAPUS FILE FISIKNYA ---
            if (!empty($nama_file)) {
                $file_path = "uploads/" . $nama_file;
                // Cek apakah file benar-benar ada di folder uploads/
                if (file_exists($file_path)) {
                    unlink($file_path); // Lenyapkan file gambar!
                }
            }
            // --------------------------------------------------------------

            $response = ["success" => true, "message" => "Data dan gambar berhasil dihapus"];
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1451) {
                $response = ["success" => false, "message" => "Gagal! Kostum pernah/sedang disewa pelanggan. Silakan ubah stok menjadi 0 saja."];
            } else {
                $response = ["success" => false, "message" => "Gagal menghapus: " . $e->getMessage()];
            }
        }
        $stmt->close();
    } else {
        $response = ["success" => false, "message" => "Query Error: " . $conn->error];
    }
} else {
    $response = ["success" => false, "message" => "ID Kostum tidak terdeteksi"];
}

ob_end_clean(); 
header('Content-Type: application/json');
echo json_encode($response);
$conn->close();
?>