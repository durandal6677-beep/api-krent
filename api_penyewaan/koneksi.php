<?php
$host = "trolley.proxy.rlwy.net";
$user = "root";
$pass = "QerLGuWHBhIQUmdenSbljbmekEwRjriV";
$db   = "railway";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Koneksi Gagal"]);
    exit;
}
?>
