<?php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'koneksi.php';

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'Bulanan';
$response = [];

// Menentukan kondisi rentang waktu UNTUK KOTAK RINGKASAN
$where_date = "";
if ($filter == 'Harian') {
    $where_date = "DATE(tanggal_sewa) = CURDATE()";
} elseif ($filter == 'Tahunan') {
    $where_date = "YEAR(tanggal_sewa) = YEAR(CURDATE())";
} else { // Default Bulanan
    $where_date = "MONTH(tanggal_sewa) = MONTH(CURDATE()) AND YEAR(tanggal_sewa) = YEAR(CURDATE())";
}

try {
    // 1. Ringkasan Pendapatan & Pesanan
    $q_income = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM penyewaan WHERE $where_date AND status_penyewaan != 'Menunggu Deposit' AND status_penyewaan != 'Dibatalkan'");
    $response['income_total'] = $q_income ? (mysqli_fetch_assoc($q_income)['total'] ?? 0) : 0;

    $q_orders = mysqli_query($conn, "SELECT COUNT(*) as total FROM penyewaan WHERE $where_date AND status_penyewaan != 'Dibatalkan'");
    $response['total_orders'] = $q_orders ? (mysqli_fetch_assoc($q_orders)['total'] ?? 0) : 0;

    $q_active = mysqli_query($conn, "
        SELECT SUM(dp.jumlah) as total 
        FROM detail_penyewaan dp
        JOIN penyewaan p ON dp.id_penyewaan = p.id_penyewaan
        WHERE p.status_penyewaan NOT IN ('Selesai/Kembali', 'Dibatalkan', 'Dikembalikan', 'Menunggu Deposit')
    ");
    $response['active_orders'] = $q_active ? (mysqli_fetch_assoc($q_active)['total'] ?? 0) : 0;

    // 2. Data Grafik (DIURUTKAN SECARA KRONOLOGIS)
    $chart_data = [];
    if ($filter == 'Harian') {
        // Tampilkan 1 minggu penuh urut dari Senin s/d Minggu
        $hari_indo = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $monday = strtotime('monday this week'); // Ambil tanggal hari senin di minggu ini
        
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime("+$i days", $monday));
            $day_num = date('N', strtotime($date)); // 1 (Senin) - 7 (Minggu)
            
            $q_chart = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM penyewaan WHERE DATE(tanggal_sewa) = '$date' AND status_penyewaan != 'Menunggu Deposit' AND status_penyewaan != 'Dibatalkan'");
            $total = $q_chart ? (mysqli_fetch_assoc($q_chart)['total'] ?? 0) : 0;
            $chart_data[] = ["day" => $hari_indo[$day_num], "total" => (float)$total];
        }
    } elseif ($filter == 'Tahunan') {
        // Tampilkan 5 tahun terakhir secara berurutan
        for ($i = 4; $i >= 0; $i--) {
            $year = date('Y', strtotime("-$i years"));
            $q_chart = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM penyewaan WHERE YEAR(tanggal_sewa) = '$year' AND status_penyewaan != 'Menunggu Deposit' AND status_penyewaan != 'Dibatalkan'");
            $total = $q_chart ? (mysqli_fetch_assoc($q_chart)['total'] ?? 0) : 0;
            $chart_data[] = ["day" => $year, "total" => (float)$total];
        }
    } else { 
        // Bulanan (Tampilkan urut Januari - Desember tahun ini)
        $bln_indo = [1=>'Jan', 2=>'Feb', 3=>'Mar', 4=>'Apr', 5=>'Mei', 6=>'Jun', 7=>'Jul', 8=>'Ags', 9=>'Sep', 10=>'Okt', 11=>'Nov', 12=>'Des'];
        $year = date('Y');
        
        for ($m = 1; $m <= 12; $m++) {
            $q_chart = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM penyewaan WHERE MONTH(tanggal_sewa) = '$m' AND YEAR(tanggal_sewa) = '$year' AND status_penyewaan != 'Menunggu Deposit' AND status_penyewaan != 'Dibatalkan'");
            $total = $q_chart ? (mysqli_fetch_assoc($q_chart)['total'] ?? 0) : 0;
            $chart_data[] = ["day" => $bln_indo[$m], "total" => (float)$total];
        }
    }
    $response['chart_data'] = $chart_data;

    // 3. TOP 3 Sering Disewa
    $q_top = mysqli_query($conn, "
        SELECT k.nama_kostum, kat.nama_kategori as kategori, k.foto as foto_kostum, SUM(dp.jumlah) as total_disewa
        FROM detail_penyewaan dp
        JOIN kostum k ON dp.id_kostum = k.id_kostum
        JOIN kategori kat ON k.id_kategori = kat.id_kategori
        JOIN penyewaan p ON dp.id_penyewaan = p.id_penyewaan
        WHERE $where_date AND p.status_penyewaan != 'Dibatalkan'
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
