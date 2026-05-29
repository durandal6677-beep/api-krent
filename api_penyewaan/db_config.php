<?php
$host = "trolley.proxy.rlwy.net";
$user = "root";
$pass = "QerLGuWHBhIQUmdenSbljbmekEwRjriV";
$db   = "railway";
$port = 39090;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
