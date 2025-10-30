<?php
// File: config/koneksi_wilayah.php
//--- Konfigurasi Database Wilayah---
$dbhost_wilayah = 'localhost';
// Kredensial paling umum untuk XAMPP/WAMPP
$dbuser_wilayah = 'root'; 
$dbpass_wilayah = ''; 
// Nama Database berdasarkan file wilayah.sql yang Anda berikan
$dbname_wilayah = 'u215075621_docwilayah'; 
$dbdsn_wilayah = "mysql:dbname={$dbname_wilayah};host={$dbhost_wilayah}";

try {
    // Buat objek koneksi PDO
    $db_wilayah = new PDO($dbdsn_wilayah, $dbuser_wilayah, $dbpass_wilayah);
    // Atur mode error untuk menampilkan exception jika terjadi kesalahan
    $db_wilayah->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Mengembalikan pesan error agar JS tahu ada kegagalan koneksi
    die("<option value=''>Error Database: Koneksi Gagal. Periksa Server MySQL atau Nama Database. [DEBUG: " . htmlspecialchars($e->getMessage()) . "]</option>");
}
?>