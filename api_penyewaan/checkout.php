<?php
ob_start(); // Mulai menampung output
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Tangani preflight dari Flutter Web
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'koneksi.php';

// Menangkap data JSON yang dikirim dari OrderService Flutter
$data = json_decode(file_get_contents("php://input"), true);
$response = []; // Siapkan wadah respon

if ($data) {
    $id_pelanggan = $data['id_pelanggan'];
    $tanggal_sewa = $data['tanggal_sewa'];
    $tanggal_kembali = $data['tanggal_kembali'];
    $total_harga = $data['total_harga'];
    
    // Tangkap metode pembayaran
    $metode_pembayaran = isset($data['metode_pembayaran']) ? $data['metode_pembayaran'] : '';
    $items = $data['items'];

    // --- LOGIKA STATUS PEMBAYARAN OTOMATIS ---
    if ($metode_pembayaran == "Tunai / Cash") {
        $status = "Menunggu Deposit";
    } else {
        $status = "Diproses"; // Jika pilih Bank/E-Wallet
    }

    // Mulai proses memasukkan data ke database
    mysqli_begin_transaction($conn);

    try {
        // 1. Masukkan ke tabel penyewaan
        // (Catatan: Jika nama tabel/kolom Anda berbeda, Anda bisa menyesuaikan bagian ini nanti dari pesan error yang muncul)
        $query_penyewaan = "INSERT INTO penyewaan (id_pelanggan, tanggal_sewa, tanggal_kembali, total_harga, metode_pembayaran, status_penyewaan) 
                            VALUES ('$id_pelanggan', '$tanggal_sewa', '$tanggal_kembali', '$total_harga', '$metode_pembayaran', '$status')";
        
        if (mysqli_query($conn, $query_penyewaan)) {
            // Ambil ID penyewaan yang baru saja dibuat
            $id_penyewaan = mysqli_insert_id($conn);

            // 2. Masukkan kostum-kostumnya ke tabel detail_penyewaan
            
           // Di dalam foreach ($items as $item) pada file checkout.php
foreach ($items as $item) {
    $id_kostum = $item['id_kostum'];
    $jumlah = $item['jumlah'];
    $subtotal = $item['subtotal'];
    $ukuran = $item['ukuran']; // 🔻 Tangkap data ukuran dari Flutter

    // Tambahkan kolom ukuran ke dalam query
    $query_detail = "INSERT INTO detail_penyewaan (id_penyewaan, id_kostum, ukuran, jumlah, subtotal) 
                     VALUES ('$id_penyewaan', '$id_kostum', '$ukuran', '$jumlah', '$subtotal')";
    mysqli_query($conn, $query_detail);
}

            // Jika semua berhasil, simpan permanen
            mysqli_commit($conn);
            $response = ["status" => "success", "message" => "Pesanan berhasil dibuat"];
        } else {
            throw new Exception("Gagal query: " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        // Jika ada yang gagal, batalkan semua (rollback)
        mysqli_rollback($conn);
        $response = ["status" => "error", "message" => $e->getMessage()];
    }
} else {
    $response = ["status" => "error", "message" => "Data tidak valid atau kosong"];
}

// BERSIHKAN OUTPUT TERSEMBUNYI, LALU CETAK JSON
ob_end_clean();
header('Content-Type: application/json');
echo json_encode($response);
?>