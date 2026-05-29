<?php
$host = "trolley.proxy.rlwy.net";
$user = "root";
$pass = "QerLGuWHBhIQUmdenSbljbmekEwRjriV";
$db   = "railway";

$conn = mysqli_connect("trolley.proxy.rlwy.net", "root", "QerLGuWHBhIQUmdenSbljbmekEwRjriV", "railway", 39090);

if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Koneksi Gagal"]);
    exit;
}
?>
