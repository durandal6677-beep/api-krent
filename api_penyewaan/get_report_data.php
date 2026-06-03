<?php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'koneksi.php';

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'Bulanan';
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

$response = [];

// 🔻 OPSI MINGGUAN DIHAPUS 🔻
$where_date = "";
if ($filter == 'Harian') {
    $where_date = "DATE(tanggal_sewa) = '$selected_date'";
} elseif ($filter == 'Tahunan') {
    $where_date = "YEAR(tanggal_sewa) = $year";
} else { 
    $where_date = "MONTH(tanggal_sewa) = $month AND YEAR(tanggal_sewa) = $year";
}

try {
    $q_income = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM penyewaan WHERE $where_date AND status_penyewaan NOT IN ('Dibatalkan', 'Menunggu Pembayaran')");
    $response['income_total'] = $q_income ? (mysqli_fetch_assoc($q_income)['total'] ?? 0) : 0;

    $q_orders = mysqli_query($conn, "SELECT COUNT(*) as total FROM penyewaan WHERE $where_date AND status_penyewaan != 'Dibatalkan'");
    $response['total_orders'] = $q_orders ? (mysqli_fetch_assoc($q_orders)['total'] ?? 0) : 0;

    $q_active = mysqli_query($conn, "SELECT COUNT(*) as total FROM penyewaan WHERE status_penyewaan = 'Disewa'");
    $response['active_orders'] = $q_active ? (mysqli_fetch_assoc($q_active)['total'] ?? 0) : 0;

    $chart_data = [];
    if ($filter == 'Harian') {
        $hari_indo = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
        $monday = strtotime('monday this week', strtotime($selected_date));
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime("+$i days", $monday));
            $day_num = date('N', strtotime($date));
            $q_chart = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM penyewaan WHERE DATE(tanggal_sewa) = '$date' AND status_penyewaan NOT IN ('Dibatalkan', 'Menunggu Pembayaran')");
            $total = $q_chart ? (mysqli_fetch_assoc($q_chart)['total'] ?? 0) : 0;
            $chart_data[] = ["day" => $hari_indo[$day_num], "total" => (float)$total];
        }
    } else { 
        $bln_indo = [1=>'Jan', 2=>'Feb', 3=>'Mar', 4=>'Apr', 5=>'Mei', 6=>'Jun', 7=>'Jul', 8=>'Ags', 9=>'Sep', 10=>'Okt', 11=>'Nov', 12=>'Des'];
        for ($m = 1; $m <= 12; $m++) {
            $q_chart = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM penyewaan WHERE MONTH(tanggal_sewa) = '$m' AND YEAR(tanggal_sewa) = '$year' AND status_penyewaan NOT IN ('Dibatalkan', 'Menunggu Pembayaran')");
            $total = $q_chart ? (mysqli_fetch_assoc($q_chart)['total'] ?? 0) : 0;
            $chart_data[] = ["day" => $bln_indo[$m], "total" => (float)$total];
        }
    }
    $response['chart_data'] = $chart_data;

    // 🔻 PERBAIKAN: Menambahkan kolom ukuran untuk membedakan duplikat 🔻
    $q_popular = mysqli_query($conn, "
        SELECT k.nama_kostum, dp.ukuran, SUM(dp.jumlah) as total_disewa, k.foto_kostum, k.kategori
        FROM detail_penyewaan dp
        JOIN kostum k ON dp.id_kostum = k.id_kostum
        JOIN penyewaan p ON dp.id_penyewaan = p.id_penyewaan
        WHERE $where_date AND p.status_penyewaan NOT IN ('Dibatalkan', 'Menunggu Pembayaran')
        GROUP BY k.nama_kostum, dp.ukuran, k.foto_kostum, k.kategori 
        ORDER BY total_disewa DESC
        LIMIT 5
    ");
    
    $popular_data = [];
    if ($q_popular) {
        while ($row = mysqli_fetch_assoc($q_popular)) {
            // Gabungkan Nama dan Ukuran agar terlihat rapi di grafik & tabel
            $ukuran_teks = !empty($row['ukuran']) ? " (Size " . $row['ukuran'] . ")" : "";
            
            $popular_data[] = [
                "nama" => $row['nama_kostum'] . $ukuran_teks,
                "total" => (int)$row['total_disewa'],
                "foto_kostum" => $row['foto_kostum'],
                "kategori" => $row['kategori']
            ];
        }
    }
    $response['popular_chart'] = $popular_data;
    $response['top_costume_summary'] = empty($popular_data) ? null : $popular_data[0];

    $q_trans = mysqli_query($conn, "
        SELECT p.id_penyewaan, p.tanggal_sewa, p.status_penyewaan, p.total_harga, pel.nama as nama_pelanggan,
               GROUP_CONCAT(CONCAT(k.nama_kostum, ' (', dp.jumlah, ')') SEPARATOR ', ') as kostum_disewa
        FROM penyewaan p
        JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
        JOIN detail_penyewaan dp ON p.id_penyewaan = dp.id_penyewaan
        JOIN kostum k ON dp.id_kostum = k.id_kostum
        WHERE $where_date
        GROUP BY p.id_penyewaan, p.tanggal_sewa, p.status_penyewaan, p.total_harga, pel.nama
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
