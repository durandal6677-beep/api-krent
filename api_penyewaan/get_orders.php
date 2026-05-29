<?php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'koneksi.php';

$id_pelanggan = isset($_GET['id_pelanggan']) ? $_GET['id_pelanggan'] : null;

// Kita JOIN dengan tabel pelanggan agar dapat NAMA dan FOTO
$sql = "
    SELECT p.id_penyewaan, p.id_pelanggan, p.tanggal_sewa, p.tanggal_kembali, p.total_harga, p.status_penyewaan, 
           pel.nama as nama_pelanggan, pel.foto as foto_pelanggan,
           GROUP_CONCAT(CONCAT(k.nama_kostum, ' x ', dp.jumlah) SEPARATOR ', ') as items_summary
    FROM penyewaan p
    JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
    JOIN detail_penyewaan dp ON p.id_penyewaan = dp.id_penyewaan
    JOIN kostum k ON dp.id_kostum = k.id_kostum
";

if ($id_pelanggan) {
    $sql .= " WHERE p.id_pelanggan = '$id_pelanggan'";
}
$sql .= " GROUP BY p.id_penyewaan ORDER BY p.id_penyewaan DESC";

$result = mysqli_query($conn, $sql);
$orders = [];
if($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
}

ob_end_clean();
echo json_encode(["status" => "success", "data" => $orders]);
?>