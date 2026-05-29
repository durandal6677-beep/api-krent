<?php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'koneksi.php';

$response = [];

try {
    // Ambil 30 pesanan terbaru untuk dijadikan notifikasi
    // Kita ambil nama 1 kostum saja sebagai perwakilan di teks notifikasi
    $query = mysqli_query($conn, "
        SELECT 
            p.id_penyewaan, 
            p.status_penyewaan, 
            p.tanggal_sewa,
            (SELECT k.nama_kostum 
             FROM detail_penyewaan dp 
             JOIN kostum k ON dp.id_kostum = k.id_kostum 
             WHERE dp.id_penyewaan = p.id_penyewaan LIMIT 1) as nama_kostum
        FROM penyewaan p
        ORDER BY p.id_penyewaan DESC
        LIMIT 30
    ");

    $data = [];
    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $status = $row['status_penyewaan'];
            
            // Anggap pesanan sebagai "Belum Dibaca" jika statusnya masih baru
            $is_unread = in_array($status, ['Menunggu Deposit', 'Menunggu Pembayaran', 'Diproses']);
            
            $title = $is_unread ? "Pesanan Baru Masuk!" : "Update Pesanan";
            $sub = "Menyewa kostum '" . ($row['nama_kostum'] ?? 'Kostum') . "'";
            
            $data[] = [
                "id" => $row['id_penyewaan'],
                "title" => $title,
                "sub" => $sub,
                "time" => $row['tanggal_sewa'], 
                "code" => $row['id_penyewaan'],
                "is_unread" => $is_unread
            ];
        }
    }
    
    $response['status'] = 'success';
    $response['data'] = $data;

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

ob_end_clean();
echo json_encode($response);
?>