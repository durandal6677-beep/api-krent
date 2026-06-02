<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit(); }
include 'koneksi.php';

$email = $_POST['email'];
$new_pass = $_POST['new_password'];

// Cek apakah email terdaftar di tabel pelanggan
$query = mysqli_query($conn, "SELECT id_pelanggan FROM pelanggan WHERE email='$email'");

if (mysqli_num_rows($query) > 0) {
    // Jika ada, buat hash password baru
    $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
    
    // Update password di database
    mysqli_query($conn, "UPDATE pelanggan SET password='$new_hash' WHERE email='$email'");
    
    echo json_encode(["status" => "success", "message" => "Password berhasil direset"]);
} else {
    // Jika tidak ditemukan di pelanggan, cek di tabel admin
    $queryAdmin = mysqli_query($conn, "SELECT id_admin FROM admin WHERE email='$email'");
    if (mysqli_num_rows($queryAdmin) > 0) {
        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE admin SET password='$new_hash' WHERE email='$email'");
        echo json_encode(["status" => "success", "message" => "Password admin berhasil direset"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Email tidak terdaftar!"]);
    }
}
?>
