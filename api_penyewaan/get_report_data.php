<?php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'koneksi.php';

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'Bulanan';
$response = [];

// Menentukan kondisi rentang waktu
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
    $q_income = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM penyewaan WHERE $where_date AND status_penyewaan NOT IN ('Dibatalkan', 'Menunggu Pembayaran')");
    $response['income_total'] = $q_income ? (mysqli_fetch_assoc($q_income)['total'] ?? 0) : 0;

    $q_orders = mysqli_query($conn, "SELECT COUNT(*) as total FROM penyewaan WHERE $where_date AND status_penyewaan != 'Dibatalkan'");
    $response['total_orders'] = $q_orders ? (mysqli_fetch_assoc($q_orders)['total'] ?? 0) : 0;

    // 2. Data Grafik Pendapatan (Line Chart)
    $chart_data = [];
    if ($filter == 'Harian') {
        $hari_indo = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $monday = strtotime('monday this week');
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime("+$i days", $monday));
            $day_num = date('N', strtotime($date));
            $q_chart = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM penyewaan WHERE DATE(tanggal_sewa) = '$date' AND status_penyewaan NOT IN ('Dibatalkan', 'Menunggu Pembayaran')");
            $total = $q_chart ? (mysqli_fetch_assoc($q_chart)['total'] ?? 0) : 0;
            $chart_data[] = ["day" => $hari_indo[$day_num], "total" => (float)$total];
        }
    } else { 
        $bln_indo = [1=>'Jan', 2=>'Feb', 3=>'Mar', 4=>'Apr', 5=>'Mei', 6=>'Jun', 7=>'Jul', 8=>'Ags', 9=>'Sep', 10=>'Okt', 11=>'Nov', 12=>'Des'];
        $year = date('Y');
        for ($m = 1; $m <= 12; $m++) {
            $q_chart = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM penyewaan WHERE MONTH(tanggal_sewa) = '$m' AND YEAR(tanggal_sewa) = '$year' AND status_penyewaan NOT IN ('Dibatalkan', 'Menunggu Pembayaran')");
            $total = $q_chart ? (mysqli_fetch_assoc($q_chart)['total'] ?? 0) : 0;
            $chart_data[] = ["day" => $bln_indo[$m], "total" => (float)$total];
        }
    }
    $response['chart_data'] = $chart_data;

    // 3. Kostum Populer (BAR CHART)
    $q_popular = mysqli_query($conn, "
        SELECT k.nama_kostum, SUM(dp.jumlah) as total_disewa
        FROM detail_penyewaan dp
        JOIN kostum k ON dp.id_kostum = k.id_kostum
        JOIN penyewaan p ON dp.id_penyewaan = p.id_penyewaan
        WHERE $where_date AND p.status_penyewaan NOT IN ('Dibatalkan', 'Menunggu Pembayaran')
        GROUP BY k.id_kostum
        ORDER BY total_disewa DESC
        LIMIT 5
    ");
    $popular_data = [];
    if ($q_popular) {
        while ($row = mysqli_fetch_assoc($q_popular)) {
            $popular_data[] = [
                "nama" => $row['nama_kostum'],
                "total" => (int)$row['total_disewa']
            ];
        }
    }
    $response['popular_chart'] = $popular_data;

    // 4. Rincian Seluruh Transaksi (Tabel Laporan)
    $q_trans = mysqli_query($conn, "
        SELECT p.tanggal_sewa, p.status_penyewaan, p.total_harga, pel.nama as nama_pelanggan,
               GROUP_CONCAT(CONCAT(k.nama_kostum, ' (', dp.jumlah, ')') SEPARATOR ', ') as kostum_disewa
        FROM penyewaan p
        JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
        JOIN detail_penyewaan dp ON p.id_penyewaan = dp.id_penyewaan
        JOIN kostum k ON dp.id_kostum = k.id_kostum
        WHERE $where_date
        GROUP BY p.id_penyewaan
        ORDER BY p.tanggal_sewa DESC
    ");
    $transactions = [];
    if ($q_trans) {
        while ($row = mysqli_fetch_assoc($q_trans)) {
            $transactions[] = $row;
        }
    }
    $response['transactions'] = $transactions;

} catch (Exception $e) {
    $response['error'] = "Terjadi kesalahan: " . $e->getMessage();
}

ob_end_clean();
echo json_encode($response);
?>
