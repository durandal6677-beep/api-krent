<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

include 'koneksi.php';

$nama = $_POST['nama'];
$email = $_POST['email'];
$password = $_POST['password'];
$no_hp = $_POST['no_hp'];
$alamat = $_POST['alamat']; // Tambahkan ini

$query = "INSERT INTO pelanggan (nama, email, password, no_hp, alamat) VALUES ('$nama', '$email', '$password', '$no_hp', '$alamat')";

if (mysqli_query($conn, $query)) {
    echo json_encode(["status" => "success", "message" => "Berhasil Daftar"]);
} else {
    echo json_encode(["status" => "error", "message" => "Gagal Daftar: " . mysqli_error($conn)]);
}
?>