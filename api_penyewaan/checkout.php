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

$dataRaw = isset($_POST['data']) ? $_POST['data'] : file_get_contents("php://input");
$data = json_decode($dataRaw, true);
$response = [];

if ($data) {
    $id_pelanggan = $data['id_pelanggan'];
    $tanggal_sewa = $data['tanggal_sewa'];
    $tanggal_kembali = $data['tanggal_kembali'];
    $total_harga = $data['total_harga'];
    $metode_pembayaran = isset($data['metode_pembayaran']) ? $data['metode_pembayaran'] : '';
    $items = $data['items'];

    if ($metode_pembayaran == "Bayar Tunai di Tempat") {
        $status = "Menunggu Pembayaran"; 
    } else {
        $status = "Menunggu Konfirmasi"; 
    }

    $bukti_name = "";
    if (isset($_FILES['bukti']['name']) && !empty($_FILES['bukti']['name'])) {
        $bukti_name = "bukti_" . time() . "_" . basename($_FILES['bukti']['name']);
        $target_dir = "uploads/" . $bukti_name;
        move_uploaded_file($_FILES['bukti']['tmp_name'], $target_dir);
    }

    mysqli_begin_transaction($conn);

    try {
        $query_penyewaan = "INSERT INTO penyewaan (id_pelanggan, tanggal_sewa, tanggal_kembali, total_harga, metode_pembayaran, status_penyewaan, bukti_pembayaran) 
                            VALUES ('$id_pelanggan', '$tanggal_sewa', '$tanggal_kembali', '$total_harga', '$metode_pembayaran', '$status', '$bukti_name')";
        
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
