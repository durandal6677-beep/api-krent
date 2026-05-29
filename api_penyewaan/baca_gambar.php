<?php
// Izinkan akses CORS untuk Flutter Web
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");

if (isset($_GET['nama'])) {
    // Gunakan basename() untuk keamanan (mencegah directory traversal attacks)
    $nama_file = basename($_GET['nama']);
    
    // Tentukan dua kemungkinan jalur pencarian (Folder Kostum dan Folder Profil)
    $path_kostum = "uploads/" . $nama_file;
    $path_profil = "uploads/profiles/" . $nama_file;
    
    // Variabel untuk menyimpan jalur file yang benar
    $final_path = "";

    // Logika Pencarian: Cek apakah file ada di folder Kostum ATAU folder Profil
    if (file_exists($path_kostum)) {
        $final_path = $path_kostum;
    } elseif (file_exists($path_profil)) {
        $final_path = $path_profil;
    }

    // Jika file ditemukan di salah satu folder
    if ($final_path !== "") {
        $mime = mime_content_type($final_path);
        header("Content-Type: " . $mime);
        readfile($final_path);
        exit;
    }
}

// Jika file tidak ditemukan di kedua folder tersebut
http_response_code(404);
echo "Gambar tidak ditemukan";
?>
