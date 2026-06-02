<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'koneksi.php';

$id_penyewaan = isset($_POST['id_penyewaan']) ? $_POST['id_penyewaan'] : '';

if (empty($id_penyewaan)) {
    echo json_encode(["status" => "error", "message" => "ID Penyewaan tidak ditemukan"]);
    exit();
}

mysqli_begin_transaction($conn);

try {
    // 1. Ubah status pesanan menjadi Dibatalkan
    $query_update = "UPDATE penyewaan SET status_penyewaan = 'Dibatalkan' WHERE id_penyewaan = '$id_penyewaan'";
    if (!mysqli_query($conn, $query_update)) {
        throw new Exception("Gagal update status: " . mysqli_error($conn));
    }

    // 2. Cari detail kostum apa saja yang disewa di pesanan ini
    $query_detail = "SELECT id_kostum, jumlah FROM detail_penyewaan WHERE id_penyewaan = '$id_penyewaan'";
    $result_detail = mysqli_query($conn, $query_detail);

    // 3. Kembalikan stoknya
    while ($row = mysqli_fetch_assoc($result_detail)) {
        $id_kostum = $row['id_kostum'];
        $jumlah_kembali = $row['jumlah'];

        $query_kembalikan_stok = "UPDATE kostum SET stok = stok + $jumlah_kembali WHERE id_kostum = '$id_kostum'";
        
        if (!mysqli_query($conn, $query_kembalikan_stok)) {
             throw new Exception("Gagal mengembalikan stok: " . mysqli_error($conn));
        }
    }

    mysqli_commit($conn);
    echo json_encode(["status" => "success", "message" => "Pesanan dibatalkan dan stok dikembalikan"]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
