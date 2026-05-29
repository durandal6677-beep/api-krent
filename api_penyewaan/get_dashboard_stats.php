<?php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'koneksi.php';

$response = [];

try {
    // 1. Hitung Pendapatan
    $q_today = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM penyewaan WHERE DATE(tanggal_sewa) = CURDATE() AND status_penyewaan != 'Menunggu Deposit'");
    $response['income_today'] = $q_today ? (mysqli_fetch_assoc($q_today)['total'] ?? 0) : 0;

    $q_month = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM penyewaan WHERE MONTH(tanggal_sewa) = MONTH(CURDATE()) AND YEAR(tanggal_sewa) = YEAR(CURDATE()) AND status_penyewaan != 'Menunggu Deposit'");
    $response['income_monthly'] = $q_month ? (mysqli_fetch_assoc($q_month)['total'] ?? 0) : 0;
    
    // 🔻 PERBAIKAN: Menghitung Total UNIT Kostum, mengecualikan yang Selesai/Batal/Menunggu Deposit
    $q_rented = mysqli_query($conn, "
        SELECT SUM(dp.jumlah) as total 
        FROM detail_penyewaan dp
        JOIN penyewaan p ON dp.id_penyewaan = p.id_penyewaan
        WHERE p.status_penyewaan NOT IN ('Selesai/Kembali', 'Dibatalkan', 'Dikembalikan', 'Menunggu Deposit')
    ");
    $response['rented_count'] = $q_rented ? (mysqli_fetch_assoc($q_rented)['total'] ?? 0) : 0;

    // TAMBAHAN UNTUK LAPORAN: Total Transaksi Bulan Ini
    $q_total_orders = mysqli_query($conn, "SELECT COUNT(*) as total FROM penyewaan WHERE MONTH(tanggal_sewa) = MONTH(CURDATE()) AND YEAR(tanggal_sewa) = YEAR(CURDATE()) AND status_penyewaan != 'Dibatalkan'");
// KODE ANDA SEBELUMNYA ...
    $response['total_orders_month'] = $q_total_orders ? (mysqli_fetch_assoc($q_total_orders)['total'] ?? 0) : 0;

    // 🔻 TAMBAHKAN 2 BARIS INI (Untuk mendeteksi notifikasi baru) 🔻
    $q_unread = mysqli_query($conn, "SELECT COUNT(*) as total FROM penyewaan WHERE status_penyewaan IN ('Menunggu Deposit', 'Menunggu Pembayaran', 'Diproses')");
    $response['unread_notif'] = $q_unread ? (mysqli_fetch_assoc($q_unread)['total'] ?? 0) : 0;
    // 2. Data Grafik (DIKUNCI SENIN - MINGGU MINGGU INI)
    $hari_indo = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    $chart_data = [];
    
    $dayOfWeek = date('N') - 1; // 0 = Senin, 6 = Minggu
    $monday = date('Y-m-d', strtotime("-$dayOfWeek days"));

    for ($i = 0; $i < 7; $i++) {
        $date = date('Y-m-d', strtotime("$monday +$i days"));
        $q_chart = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM penyewaan WHERE DATE(tanggal_sewa) = '$date' AND status_penyewaan != 'Menunggu Deposit' AND status_penyewaan != 'Dibatalkan'");
        $total = $q_chart ? (mysqli_fetch_assoc($q_chart)['total'] ?? 0) : 0;
        
        $chart_data[] = [
            "day" => $hari_indo[$i],
            "total" => (float)$total
        ];
    }
    $response['chart_data'] = $chart_data;

    // 3. TOP 3 Sering Disewa
    $q_top = mysqli_query($conn, "
        SELECT k.nama_kostum, kat.nama_kategori as kategori, k.foto as foto_kostum, SUM(dp.jumlah) as total_disewa
        FROM detail_penyewaan dp
        JOIN kostum k ON dp.id_kostum = k.id_kostum
        JOIN kategori kat ON k.id_kategori = kat.id_kategori
        JOIN penyewaan p ON dp.id_penyewaan = p.id_penyewaan
        WHERE p.status_penyewaan != 'Dibatalkan'
        GROUP BY k.id_kostum
        ORDER BY total_disewa DESC
        LIMIT 3
    ");

    $top_costumes = [];
    if ($q_top) {
        while ($row = mysqli_fetch_assoc($q_top)) {
            $top_costumes[] = $row;
        }
    }
    $response['top_costumes'] = $top_costumes;

} catch (Exception $e) {
    $response['error'] = "Terjadi kesalahan";
}

ob_end_clean();
echo json_encode($response);
?>