<?php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);
$response = [];

if ($data) {
    $id_pelanggan = $data['id_pelanggan'];
    $tanggal_sewa = $data['tanggal_sewa'];
    $tanggal_kembali = $data['tanggal_kembali'];
    $total_harga = $data['total_harga'];
    $metode_pembayaran = isset($data['metode_pembayaran']) ? $data['metode_pembayaran'] : '';
    $items = $data['items'];

    $status = "Menunggu Pembayaran"; 

    mysqli_begin_transaction($conn);

    try {
        $query_penyewaan = "INSERT INTO penyewaan (id_pelanggan, tanggal_sewa, tanggal_kembali, total_harga, metode_pembayaran, status_penyewaan) 
                            VALUES ('$id_pelanggan', '$tanggal_sewa', '$tanggal_kembali', '$total_harga', '$metode_pembayaran', '$status')";
        
        if (mysqli_query($conn, $query_penyewaan)) {
            $id_penyewaan = mysqli_insert_id($conn);

            foreach ($items as $item) {
                $id_kostum = $item['id_kostum'];
                $jumlah = $item['jumlah'];
                $subtotal = $item['subtotal'];
                $ukuran = $item['ukuran']; 

                $query_detail = "INSERT INTO detail_penyewaan (id_penyewaan, id_kostum, ukuran, jumlah, subtotal) 
                                 VALUES ('$id_penyewaan', '$id_kostum', '$ukuran', '$jumlah', '$subtotal')";
                mysqli_query($conn, $query_detail);
            }

            mysqli_commit($conn);
            $response = ["status" => "success", "message" => "Pesanan berhasil dibuat"];
        } else {
            throw new Exception("Gagal query: " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $response = ["status" => "error", "message" => $e->getMessage()];
    }
} else {
    $response = ["status" => "error", "message" => "Data tidak valid atau kosong"];
}

ob_end_clean();
header('Content-Type: application/json');
echo json_encode($response);
?>
