<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "penyewaan";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Koneksi Gagal"]);
    exit;
}
?>