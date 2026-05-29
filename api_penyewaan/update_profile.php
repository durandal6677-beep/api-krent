<?php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

include 'koneksi.php';
$response = [];

$id_pelanggan = isset($_POST['id_pelanggan']) ? $_POST['id_pelanggan'] : null;
$aksi = isset($_POST['aksi']) ? $_POST['aksi'] : null;

if (!$id_pelanggan) {
    echo json_encode(["success" => false, "message" => "ID Pelanggan kosong"]);
    exit();
}

if ($aksi == 'update_foto') {
    if (isset($_FILES['foto'])) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        if(empty($ext)) $ext = "jpg";
        $nama_file = "profile_" . $id_pelanggan . "_" . time() . "." . $ext;
        $folder_tujuan = "uploads/profiles/";
        if (!is_dir($folder_tujuan)) mkdir($folder_tujuan, 0777, true);
        
        // Hapus foto lama jika ada
        $cek = mysqli_query($conn, "SELECT foto FROM pelanggan WHERE id_pelanggan = '$id_pelanggan'");
        $row = mysqli_fetch_assoc($cek);
        if ($row && !empty($row['foto'])) {
            $file_lama = $folder_tujuan . $row['foto'];
            if (file_exists($file_lama)) unlink($file_lama);
        }

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $folder_tujuan . $nama_file)) {
            $sql = "UPDATE pelanggan SET foto = '$nama_file' WHERE id_pelanggan = '$id_pelanggan'";
            if (mysqli_query($conn, $sql)) {
                $response = ["success" => true, "foto_url" => $nama_file];
            } else {
                $response = ["success" => false, "message" => "Gagal update database."];
            }
        } else {
            $response = ["success" => false, "message" => "Gagal memindahkan file."];
        }
    }
} elseif ($aksi == 'delete_foto') {
    // Fitur Hapus Foto Profil
    $folder_tujuan = "uploads/profiles/";
    $cek = mysqli_query($conn, "SELECT foto FROM pelanggan WHERE id_pelanggan = '$id_pelanggan'");
    $row = mysqli_fetch_assoc($cek);
    if ($row && !empty($row['foto'])) {
        $file_lama = $folder_tujuan . $row['foto'];
        if (file_exists($file_lama)) unlink($file_lama);
    }
    
    $sql = "UPDATE pelanggan SET foto = NULL WHERE id_pelanggan = '$id_pelanggan'";
    if (mysqli_query($conn, $sql)) {
        $response = ["success" => true, "message" => "Foto berhasil dihapus"];
    } else {
        $response = ["success" => false, "message" => "Gagal menghapus foto."];
    }
} elseif ($aksi == 'update_data') {
    // Fitur Edit Data Profil
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];

    $sql = "UPDATE pelanggan SET nama = '$nama', email = '$email', no_hp = '$no_hp', alamat = '$alamat' WHERE id_pelanggan = '$id_pelanggan'";
    if (mysqli_query($conn, $sql)) {
        $response = ["success" => true, "message" => "Data berhasil diperbarui"];
    } else {
        $response = ["success" => false, "message" => "Gagal memperbarui data."];
    }
} else {
    $response = ["success" => false, "message" => "Aksi tidak valid"];
}

ob_end_clean();
header('Content-Type: application/json');
echo json_encode($response);
?>