<?php
session_start();
include '../config/database.php';
// include '../includes/header.php'; // Pindahkan ke bawah

// Authorization check: Hanya Pimpinan, Kepala LKSA, dan Petugas Kotak Amal yang bisa mengakses
if ($_SESSION['jabatan'] != 'Pimpinan' && $_SESSION['jabatan'] != 'Kepala LKSA' && $_SESSION['jabatan'] != 'Petugas Kotak Amal') {
    die("Akses ditolak.");
}

// Ambil ID pengguna dan LKSA dari sesi
$id_user = $_SESSION['id_user'];
$id_lksa = $_SESSION['id_lksa'];

$sidebar_stats = ''; // Pastikan sidebar tampil

include '../includes/header.php'; // LOKASI BARU
?>
<div class="form-container">
    <h1>Tambah Kotak Amal Baru</h1>
    <form action="proses_kotak_amal.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($id_user); ?>">
        <input type="hidden" name="id_lksa" value="<?php echo htmlspecialchars($id_lksa); ?>">

        <div class="form-section">
            <h2>Informasi Toko</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nama Toko:</label>
                    <input type="text" name="nama_toko" required>
                </div>
                <div class="form-group">
                    <label>Alamat Toko:</label>
                    <input type="text" name="alamat_toko" required>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h2>Dapatkan Lokasi Sekarang</h2>
            <div class="form-group">
                <p>Klik tombol di bawah ini untuk mengambil Latitude dan Longitude otomatis dari perangkat Anda.</p>
                
                <button type="button" id="getLocationButton" class="btn btn-primary" style="background-color: #F97316; margin-bottom: 15px;">
                    <i class="fas fa-location-arrow"></i> Simpan Lokasi Sekarang
                </button>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Latitude:</label>
                    <input type="text" id="latitude" name="latitude" readonly required placeholder="Otomatis terisi setelah tombol diklik.">
                </div>
                <div class="form-group">
                    <label>Longitude:</label>
                    <input type="text" id="longitude" name="longitude" readonly required placeholder="Otomatis terisi setelah tombol diklik.">
                </div>
            </div>
            <small>Koordinat ini akan tersimpan saat Anda menekan tombol "Simpan Kotak Amal".</small>
        </div>

        <div class="form-section">
            <h2>Informasi Pemilik</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nama Pemilik:</label>
                    <input type="text" name="nama_pemilik">
                </div>
                <div class="form-group">
                    <label>Nomor WA Pemilik:</label>
                    <input type="text" name="wa_pemilik">
                </div>
            </div>
            <div class="form-group">
                <label>Email Pemilik:</label>
                <input type="email" name="email_pemilik">
            </div>
        </div>
        
        <div class="form-section">
            <h2>Informasi Lainnya</h2>
            <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                <div class="form-group">
                    <label>Jadwal Pengambilan:</label>
                    <select name="jadwal_pengambilan">
                        <option value="">-- Pilih Hari --</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                        <option value="Minggu">Minggu</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Unggah Foto:</label>
                    <input type="file" name="foto" accept="image/*">
                </div>
            </div>
            <div class="form-group">
                <label>Keterangan:</label>
                <textarea name="keterangan" rows="4" cols="50"></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan Kotak Amal</button>
            <a href="kotak-amal.php" class="btn btn-cancel"><i class="fas fa-times-circle"></i> Batal</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const getLocationButton = document.getElementById('getLocationButton');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');

    function getLocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error("Browser tidak mendukung geolocation."));
            }
            // Menggunakan opsi untuk akurasi tinggi dan timeout
            const options = {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            };
            navigator.geolocation.getCurrentPosition(
                pos => resolve(pos.coords),
                err => reject(err),
                options
            );
        });
    }

    getLocationButton.addEventListener('click', async () => {
        try {
            Swal.fire({
                title: 'Mengambil Lokasi...',
                text: 'Mohon tunggu sebentar. Pastikan izin lokasi diaktifkan.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const coords = await getLocation();
            const { latitude, longitude } = coords;

            // Mengisi koordinat ke dalam field form dengan presisi 8 desimal
            latitudeInput.value = latitude.toFixed(8);
            longitudeInput.value = longitude.toFixed(8);

            Swal.fire({
                icon: 'success',
                title: 'Lokasi Berhasil Diambil!',
                text: `Lat: ${latitude.toFixed(6)}, Lng: ${longitude.toFixed(6)}. Data siap disimpan.`,
                confirmButtonColor: '#10B981',
            });

        } catch (err) {
            Swal.close();
            let errorMessage = 'Tidak bisa mendapatkan lokasi. Pastikan izin lokasi diaktifkan di browser Anda.';
            if (err.code === 1) { 
                errorMessage = 'Anda menolak izin untuk mengakses lokasi.';
            } else if (err.code === 2) { 
                errorMessage = 'Lokasi tidak tersedia atau gagal mendapatkan lokasi.';
            } else if (err.code === 3) { 
                errorMessage = 'Waktu pengambilan lokasi habis. Coba lagi.';
            } else {
                errorMessage = `Terjadi kesalahan saat mengambil lokasi.`;
            }
            Swal.fire('Error!', errorMessage, 'error');
        }
    });
});
</script>

<?php
include '../includes/footer.php';
$conn->close();
?>