<?php
$host = "trolley.proxy.rlwy.net";
$user = "root";
$pass = "QerLGuWHBhIQUmdenSbljbmekEwRjriV";
$db   = "railway";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
