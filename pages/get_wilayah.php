<?php
// File: pages/get_wilayah.php
header("Content-Type: text/html");

// JALUR PALING STABIL MENGGUNAKAN __DIR__
require_once __DIR__ . '/../config/koneksi_wilayah.php'; 

if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $id = $_GET['id'];
    $n = strlen($id);
    
    $placeholder = '';
    $m = 0; 

    // Tentukan level dan panjang kode anak
    if ($n == 2) {
        $m = 5; $placeholder = 'Kota/Kabupaten';
    } elseif ($n == 5) {
        $m = 8; $placeholder = 'Kecamatan';
    } elseif ($n == 8) {
        $m = 13; $placeholder = 'Kelurahan/Desa';
    } else {
        echo "<option value=''>ID Induk tidak valid</option>";
        exit;
    }

    try {
        // PERBAIKAN KRITIS: Menggunakan LIKE dan CHAR_LENGTH untuk memfilter dengan aman
        // Pola LIKE: ex: '11.%', '11.01.%'
        $query_sql = "SELECT kode, nama FROM wilayah WHERE kode LIKE :like_pattern AND CHAR_LENGTH(kode) = :m ORDER BY nama";
        $query = $db_wilayah->prepare($query_sql);
        
        // Pola LIKE: Tambahkan pola LIKE: ex: '11.%', '11.01.%'
        $like_pattern = $id . '.%';
        
        // Binding parameter
        $query->execute([':like_pattern' => $like_pattern, ':m' => $m]);
        
        echo "<option value=''>Pilih {$placeholder}</option>";
        if ($query->rowCount() == 0) {
            echo "<option value=''>Data {$placeholder} tidak ditemukan</option>";
        } else {
            while ($d = $query->fetch(PDO::FETCH_OBJ)) {
                echo "<option value='{$d->kode}'>{$d->nama}</option>";
            }
        }
    } catch (PDOException $e) {
        echo "<option value=''>Error Database: " . htmlspecialchars($e->getMessage()) . "</option>";
    }
} else {
    // Mengambil daftar Provinsi (Awal)
    try {
        $query_prov = $db_wilayah->prepare("SELECT kode, nama FROM wilayah WHERE CHAR_LENGTH(kode)=2 ORDER BY nama");
        $query_prov->execute();
        echo "<option value=''>Pilih Provinsi</option>";
        while ($data_prov = $query_prov->fetch(PDO::FETCH_OBJ)) {
            echo '<option value="' . $data_prov->kode . '">' . $data_prov->nama . '</option>';
        }
    } catch (PDOException $e) {
        echo "<option value=''>Error Database: " . htmlspecialchars($e->getMessage()) . "</option>";
    }
}
?>