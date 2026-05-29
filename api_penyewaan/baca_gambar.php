<?php
// Berikan izin CORS agar CanvasKit Flutter tidak diblokir
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");

// Ambil nama file dari URL
if (isset($_GET['nama'])) {
    // basename() digunakan untuk keamanan agar tidak bisa akses folder lain
    $nama_file = basename($_GET['nama']); 
    $path = "uploads/" . $nama_file;

    // Cek apakah gambar ada di folder uploads
    if (file_exists($path)) {
        $mime = mime_content_type($path);
        header("Content-Type: " . $mime);
        readfile($path); // Tampilkan gambarnya
        exit;
    }
}

// Jika file tidak ada
http_response_code(404);
echo "Gambar tidak ditemukan";
?>
