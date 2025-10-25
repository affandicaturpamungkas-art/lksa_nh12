<?php
session_start();
include '../config/database.php';
// Set $sidebar_stats agar tidak ada error di header
$sidebar_stats = ''; 
include '../includes/header.php';

// Authorization check: Pimpinan, Kepala LKSA, dan Petugas Kotak Amal
if (!in_array($_SESSION['jabatan'] ?? '', ['Pimpinan', 'Kepala LKSA', 'Petugas Kotak Amal'])) {
    die("Akses ditolak.");
}

$id_kotak_amal = $_GET['id'] ?? '';
if (empty($id_kotak_amal)) {
    die("ID Kotak Amal tidak ditemukan.");
}

// Ambil data Kotak Amal
$sql = "SELECT * FROM KotakAmal WHERE ID_KotakAmal = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id_kotak_amal);
$stmt->execute();
$result = $stmt->get_result();
$data_ka = $result->fetch_assoc();
$stmt->close();

if (!$data_ka) {
    die("Data Kotak Amal tidak ditemukan.");
}

$latitude = $data_ka['Latitude'] ?? -7.5583; // Default Solo
$longitude = $data_ka['Longitude'] ?? 110.8252; // Default Solo
$map_link = "https://maps.google.com/maps?q={$latitude},{$longitude}&z=15&output=embed";

$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/lksa_nh/";
$foto_ka = $data_ka['Foto'] ?? '';
// Menggunakan gambar default yang sudah ada
$foto_path = $foto_ka ? $base_url . 'assets/img/' . $foto_ka : $base_url . 'assets/img/kotak_amal_makmur_deb0a.jpg'; 
?>
<style>
    .detail-card {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
        margin-top: 20px;
    }
    .profile-info, .location-info {
        flex: 1 1 45%;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        background-color: #f8f9fa;
        border-left: 5px solid #F97316; /* Orange accent */
    }
    .profile-info h2, .location-info h2 {
        color: #1F2937;
        font-size: 1.4em;
        margin-top: 0;
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 10px;
    }
    .map-frame {
        width: 100%;
        height: 350px;
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-top: 15px;
    }
    .data-row {
        padding: 8px 0;
        border-bottom: 1px dotted #eee;
        display: flex;
    }
    .data-label {
        font-weight: 600;
        color: #555;
        width: 150px;
    }
</style>

<h1 class="dashboard-title"><i class="fas fa-search-location"></i> Profil & Lokasi Kotak Amal</h1>

<div class="detail-card">
    
    <div class="profile-info">
        <h2>Data Toko & Kontak</h2>
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="<?php echo htmlspecialchars($foto_path); ?>" alt="Foto Kotak Amal" style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 4px solid #F97316; margin-bottom: 10px;">
            <p style="font-weight: 700; font-size: 1.2em; margin: 0;"><?php echo htmlspecialchars($data_ka['Nama_Toko']); ?></p>
            <small style="color: #6B7280;"><?php echo htmlspecialchars($data_ka['ID_KotakAmal']); ?></small>
        </div>
        
        <div class="data-row">
            <span class="data-label">Nama Pemilik:</span>
            <span><?php echo htmlspecialchars($data_ka['Nama_Pemilik'] ?? '-'); ?></span>
        </div>
        <div class="data-row">
            <span class="data-label">WA Pemilik:</span>
            <span><?php echo htmlspecialchars($data_ka['WA_Pemilik'] ?? '-'); ?></span>
        </div>
        <div class="data-row">
            <span class="data-label">Email:</span>
            <span><?php echo htmlspecialchars($data_ka['Email'] ?? '-'); ?></span>
        </div>
        <div class="data-row">
            <span class="data-label">Jadwal Ambil:</span>
            <span><?php echo htmlspecialchars($data_ka['Jadwal_Pengambilan'] ?? '-'); ?></span>
        </div>
        <div class="data-row">
            <span class="data-label">Keterangan:</span>
            <span><?php echo htmlspecialchars($data_ka['Ket'] ?? '-'); ?></span>
        </div>
    </div>
    
    <div class="location-info">
        <h2>Lokasi & Peta</h2>
        <div class="data-row">
            <span class="data-label">Alamat Lengkap:</span>
            <span><?php echo htmlspecialchars($data_ka['Alamat_Toko'] ?? 'Koordinat Belum Dicatat'); ?></span>
        </div>
        <div class="data-row">
            <span class="data-label">Latitude:</span>
            <span><?php echo htmlspecialchars($latitude); ?></span>
        </div>
        <div class="data-row" style="border-bottom: none;">
            <span class="data-label">Longitude:</span>
            <span><?php echo htmlspecialchars($longitude); ?></span>
        </div>
        
        <p style="margin-top: 25px; font-weight: 600;">Tampilan Peta:</p>
        <iframe src="<?php echo $map_link; ?>" class="map-frame" allowfullscreen="" loading="lazy"></iframe>
        
        <a href="kotak-amal.php" class="btn btn-cancel" style="margin-top: 15px; width: 100%;"><i class="fas fa-arrow-left"></i> Kembali ke Manajemen Kotak Amal</a>
    </div>
</div>

<?php
include '../includes/footer.php';
$conn->close();
?>