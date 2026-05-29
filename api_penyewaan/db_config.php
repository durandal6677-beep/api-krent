<?php
$host = "trolley.proxy.rlwy.net";
$user = "root";
$pass = "QerLGuWHBhIQUmdenSbljbmekEwRjriV";
$db   = "railway";

$conn = mysqli_connect("trolley.proxy.rlwy.net", "root", "QerLGuWHBhIQUmdenSbljbmekEwRjriV", "railway", 39090);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
