<?php
$host = "trolley.proxy.rlwy.net";
$user = "root";
$pass = "QerLGuWHBhIQUmdenSbljbmekEwRjriV";
$db   = "railway";
$port = 39090;

$conn = new mysqli($host, $user, $pass, $db, $port);

if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Koneksi Gagal"]);
    exit;
}
?>
