<?php
session_start();
include '../config/database.php';
include '../includes/header.php';

// Verifikasi otorisasi
if ($_SESSION['jabatan'] != 'Pimpinan' && $_SESSION['jabatan'] != 'Kepala LKSA' && $_SESSION['jabatan'] != 'Pegawai') {
    die("Akses ditolak.");
}

$id_kwitansi = $_GET['id'] ?? '';
if (empty($id_kwitansi)) {
    die("ID Kwitansi tidak ditemukan.");
}

// Ambil data sumbangan, donatur, dan pengguna yang menginput
$sql = "SELECT s.*, d.Nama_Donatur, d.NO_WA, u.Nama_User 
        FROM Sumbangan s 
        LEFT JOIN Donatur d ON s.ID_donatur = d.ID_donatur 
        LEFT JOIN User u ON s.ID_user = u.Id_user 
        WHERE s.ID_Kwitansi_ZIS = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error saat menyiapkan kueri: " . $conn->error);
}
$stmt->bind_param("s", $id_kwitansi);
$stmt->execute();
$result = $stmt->get_result();
$data_sumbangan = $result->fetch_assoc();

if (!$data_sumbangan) {
    die("Data sumbangan tidak ditemukan.");
}
?>
<div class="content">
    <h1>Detail Sumbangan</h1>
    <p>Berikut adalah rincian lengkap dari transaksi sumbangan ini.</p>

    <div class="form-container">
        <table style="width: 100%;">
            <tr>
                <th>No. Kwitansi</th>
                <td><?php echo htmlspecialchars($data_sumbangan['ID_Kwitansi_ZIS']); ?></td>
            </tr>
            <tr>
                <th>Tanggal</th>
                <td><?php echo htmlspecialchars($data_sumbangan['Tgl']); ?></td>
            </tr>
            <tr>
                <th>Donatur</th>
                <td><?php echo htmlspecialchars($data_sumbangan['Nama_Donatur']); ?></td>
            </tr>
            <tr>
                <th>Nomor WA Donatur</th>
                <td><?php echo htmlspecialchars($data_sumbangan['NO_WA']); ?></td>
            </tr>
            <tr>
                <th>Dibuat Oleh</th>
                <td><?php echo htmlspecialchars($data_sumbangan['Nama_User']); ?></td>
            </tr>
        </table>

        <br>

        <h2>Rincian Dana</h2>
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th>Jenis Sumbangan</th>
                    <th>Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Zakat Profesi</td>
                    <td><?php echo number_format($data_sumbangan['Zakat_Profesi']); ?></td>
                </tr>
                <tr>
                    <td>Zakat Maal</td>
                    <td><?php echo number_format($data_sumbangan['Zakat_Maal']); ?></td>
                </tr>
                <tr>
                    <td>Infaq</td>
                    <td><?php echo number_format($data_sumbangan['Infaq']); ?></td>
                </tr>
                <tr>
                    <td>Sedekah</td>
                    <td><?php echo number_format($data_sumbangan['Sedekah']); ?></td>
                </tr>
                <tr>
                    <td>Fidyah</td>
                    <td><?php echo number_format($data_sumbangan['Fidyah']); ?></td>
                </tr>
                <?php if (!empty($data_sumbangan['Natura'])) { ?>
                    <tr>
                        <td>Natura (Barang)</td>
                        <td><?php echo htmlspecialchars($data_sumbangan['Natura']); ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <th>Total</th>
                    <th>Rp <?php echo number_format($data_sumbangan['Zakat_Profesi'] + $data_sumbangan['Zakat_Maal'] + $data_sumbangan['Infaq'] + $data_sumbangan['Sedekah'] + $data_sumbangan['Fidyah']); ?></th>
                </tr>
            </tbody>
        </table>

        <br>
        <div class="form-actions" style="justify-content: flex-start;">
            <a href="sumbangan.php" class="btn btn-cancel">Kembali ke Daftar Sumbangan</a>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
$conn->close();
?>