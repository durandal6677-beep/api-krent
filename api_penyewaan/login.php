<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Jika ada permintaan OPTIONS (preflight), langsung sukseskan
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}
include 'koneksi.php';

$email = $_POST['email'];
$password = $_POST['password'];

// Cek di tabel pelanggan
$queryPelanggan = "SELECT * FROM pelanggan WHERE email='$email' AND password='$password'";
$resPelanggan = mysqli_query($conn, $queryPelanggan);

if (mysqli_num_rows($resPelanggan) > 0) {
    $data = mysqli_fetch_assoc($resPelanggan);
    echo json_encode(["status" => "success", "role" => "pelanggan", "data" => $data]);
} else {
    // Jika bukan pelanggan, cek di tabel admin
    $queryAdmin = "SELECT * FROM admin WHERE email='$email' AND password='$password'";
    $resAdmin = mysqli_query($conn, $queryAdmin);

    if (mysqli_num_rows($resAdmin) > 0) {
        $data = mysqli_fetch_assoc($resAdmin);
        echo json_encode(["status" => "success", "role" => "admin", "data" => $data]);
    } else {
        echo json_encode(["status" => "error", "message" => "Email atau Password Salah"]);
    }
}
?>