<?php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'koneksi.php';

$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    $q_order = mysqli_query($conn, "SELECT p.*, pel.nama as nama_pelanggan, pel.no_hp, pel.alamat, pel.foto as foto_pelanggan 
                                    FROM penyewaan p JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan 
                                    WHERE p.id_penyewaan = '$id'");
    $order = mysqli_fetch_assoc($q_order);

    if ($order) {
        // AMBIL k.harga_sewa UNTUK RINCIAN HARGA
        // Ambil detail kostum, TERMASUK FOTO DAN HARGA SEWA
        $q_items = mysqli_query($conn, "SELECT dp.*, k.nama_kostum, k.ukuran, k.foto as foto_kostum, k.harga_sewa 
                                        FROM detail_penyewaan dp JOIN kostum k ON dp.id_kostum = k.id_kostum 
                                        WHERE dp.id_penyewaan = '$id'");
        $items = [];
        while ($row = mysqli_fetch_assoc($q_items)) {
            $items[] = $row;
        }
        $response = ["status" => "success", "data" => $order, "items" => $items];
    } else {
        $response = ["status" => "error", "message" => "Gagal"];
    }
}
ob_end_clean();
echo json_encode($response);
?>