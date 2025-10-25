<?php
session_start();
include '../config/database.php';
include '../includes/header.php';

// Authorization check
if ($_SESSION['jabatan'] != 'Pimpinan' && $_SESSION['jabatan'] != 'Kepala LKSA' && $_SESSION['jabatan'] != 'Petugas Kotak Amal') {
    die("Akses ditolak.");
}

$jabatan = $_SESSION['jabatan'];
$id_lksa = $_SESSION['id_lksa'];

// PERUBAHAN: Mengambil data yang Status = 'Archived'
$sql = "SELECT ka.*
        FROM KotakAmal ka
        WHERE ka.Status = 'Archived'";

$params = [];
$types = "";

// FIX: Hanya Pimpinan Pusat yang tidak difilter
if ($jabatan != 'Pimpinan' || $id_lksa != 'Pimpinan_Pusat') {
    // Perbaikan SQLI: Menggunakan placeholder
    $sql .= " AND ka.Id_lksa = ?";
    $params[] = $id_lksa;
    $types = "s";
}

// Eksekusi Kueri
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<h1 class="dashboard-title">Arsip Kotak Amal</h1>
<p>Daftar kotak amal yang telah diarsipkan (soft delete). Anda dapat memulihkan atau menghapus permanen dari sini.</p>
<a href="kotak-amal.php" class="btn btn-primary">Kembali ke Manajemen Kotak Amal Aktif</a>

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
                    <a href="proses_restore_kotak_amal.php?id=<?php echo $row['ID_KotakAmal']; ?>" class="btn btn-success" title="Pulihkan" onclick="return confirm('Apakah Anda yakin ingin memulihkan Kotak Amal ini?');"><i class="fas fa-undo"></i> Pulihkan</a>
                    <a href="#" class="btn btn-danger" title="Hapus Permanen" onclick="alert('Fitur Hapus Permanen dinonaktifkan untuk Kotak Amal. Silakan kontak administrator database.');"><i class="fas fa-trash"></i> Hapus Permanen</a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php
include '../includes/footer.php';
$conn->close();
?>