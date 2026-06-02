<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit(); }
include 'koneksi.php';

$id_user = $_POST['id_user'];
$old_pass = $_POST['old_password'];
$new_pass = $_POST['new_password'];

// Cek password lama di database
$query = mysqli_query($conn, "SELECT password FROM pelanggan WHERE id_pelanggan='$id_user'");

if (mysqli_num_rows($query) > 0) {
    $row = mysqli_fetch_assoc($query);
    $db_password = $row['password'];

    // Verifikasi password lama
    if (password_verify($old_pass, $db_password) || $old_pass == $db_password) {
        
        // Jika cocok, buat hash (enkripsi) untuk password baru
        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        
        // Simpan ke database
        mysqli_query($conn, "UPDATE pelanggan SET password='$new_hash' WHERE id_pelanggan='$id_user'");
        
        echo json_encode(["status" => "success", "message" => "Password berhasil diubah"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Password Lama Salah!"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Akun tidak ditemukan"]);
}
?>
