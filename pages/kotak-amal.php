<?php
session_start();
include '../config/database.php';
include '../includes/header.php';

// Authorization check
if ($_SESSION['jabatan'] != 'Pimpinan' && $_SESSION['jabatan'] != 'Kepala LKSA' && $_SESSION['jabatan'] != 'Petugas Kotak Amal') {
    die("Akses ditolak.");
}

$id_lksa = $_SESSION['id_lksa'];

// PERUBAHAN: Menambahkan filter untuk Status = 'Active' dan LKSA
$sql = "SELECT ka.*, MAX(dka.ID_Kwitansi_KA) AS is_collected_today
        FROM KotakAmal ka
        LEFT JOIN Dana_KotakAmal dka ON ka.ID_KotakAmal = dka.ID_KotakAmal AND dka.Tgl_Ambil = CURDATE()
        WHERE ka.Status = 'Active'";
        
$params = [];
$types = "";

// FIX: Hanya Pimpinan Pusat yang tidak difilter
if ($_SESSION['jabatan'] != 'Pimpinan' || $_SESSION['id_lksa'] != 'Pimpinan_Pusat') {
    // Mengubah string concatenation menjadi parameter binding
    $sql .= " AND ka.Id_lksa = ?";
    $params[] = $id_lksa;
    $types = "s";
}

$sql .= " GROUP BY ka.ID_KotakAmal";

// Eksekusi Kueri
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

?>
<style>
    /* Style tambahan untuk tombol ikon yang sederhana */
    .btn-action-icon {
        padding: 5px 10px;
        margin: 0 2px;
        border-radius: 5px;
        font-size: 0.9em;
    }
</style>
<h1 class="dashboard-title">Manajemen Kotak Amal</h1>
<p>Kelola data kotak amal.</p>
<a href="tambah_kotak_amal.php" class="btn btn-success">Tambah Kotak Amal</a>
<a href="arsip_kotak_amal.php" class="btn btn-cancel" style="background-color: #F97316; margin-left: 10px;">Lihat Arsip Kotak Amal</a>


<table>
    <thead>
        <tr>
            <th>ID Kotak Amal</th>
            <th>Nama Toko</th>
            <th>Nama Pemilik</th>
            <th>Alamat</th>
            <th>Jadwal Ambil</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['ID_KotakAmal']; ?></td>
                <td><?php echo $row['Nama_Toko']; ?></td>
                <td><?php echo $row['Nama_Pemilik']; ?></td>
                <td><?php echo $row['Alamat_Toko']; ?></td>
                <td><?php echo $row['Jadwal_Pengambilan']; ?></td>
                <td>
                    <a href="detail_kotak_amal.php?id=<?php echo $row['ID_KotakAmal']; ?>" class="btn btn-primary btn-action-icon" title="Lihat Profil & Lokasi"><i class="fas fa-map-marked-alt"></i></a>
                    
                    <a href="edit_kotak_amal.php?id=<?php echo $row['ID_KotakAmal']; ?>" class="btn btn-primary btn-action-icon" title="Edit"><i class="fas fa-edit"></i></a>
                    
                    <?php if ($row['is_collected_today']) { ?>
                        <span style="color: green; font-weight: bold; font-size: 0.9em; margin-left: 5px;">Sudah Diambil</span>
                    <?php } else { ?>
                        <a href="dana-kotak-amal.php?id_kotak_amal=<?php echo $row['ID_KotakAmal']; ?>" class="btn btn-success" style="padding: 5px 10px; font-size: 0.9em;">Pengambilan</a>
                    <?php } ?>
                    
                    <a href="proses_arsip_kotak_amal.php?id=<?php echo $row['ID_KotakAmal']; ?>" class="btn btn-danger btn-action-icon" title="Arsipkan" onclick="return confirm('Apakah Anda yakin ingin mengarsipkan Kotak Amal ini?');"><i class="fas fa-archive"></i></a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php
include '../includes/footer.php';
$stmt->close();
$conn->close();
?>