<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Jika ada permintaan OPTIONS (preflight), langsung sukseskan
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}
include 'koneksi.php';

$email = $_POST['email'];
$password_ketikan = $_POST['password'];


$queryPelanggan = "SELECT * FROM pelanggan WHERE email='$email'";
$resPelanggan = mysqli_query($conn, $queryPelanggan);

if (mysqli_num_rows($resPelanggan) > 0) {
    $data = mysqli_fetch_assoc($resPelanggan);
    $password_database = $data['password']; 

    
    if (password_verify($password_ketikan, $password_database) || $password_ketikan == $password_database) {
        echo json_encode(["status" => "success", "role" => "pelanggan", "data" => $data]);
        exit();
    }
} 

$queryAdmin = "SELECT * FROM admin WHERE email='$email'";
$resAdmin = mysqli_query($conn, $queryAdmin);

if (mysqli_num_rows($resAdmin) > 0) {
    $data = mysqli_fetch_assoc($resAdmin);
    $password_database = $data['password'];

    if (password_verify($password_ketikan, $password_database) || $password_ketikan == $password_database) {
        echo json_encode(["status" => "success", "role" => "admin", "data" => $data]);
        exit();
    }
}

echo json_encode(["status" => "error", "message" => "Email atau Password Salah"]);
?>
