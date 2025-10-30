<?php
session_start();
include '../config/database.php';
// Note: $base_url is defined in '../includes/header.php'

// Authorization check: Hanya Pimpinan, Kepala LKSA, dan Petugas Kotak Amal yang bisa mengakses
if ($_SESSION['jabatan'] != 'Pimpinan' && $_SESSION['jabatan'] != 'Kepala LKSA' && $_SESSION['jabatan'] != 'Petugas Kotak Amal') {
    die("Akses ditolak.");
}

// Ambil ID pengguna dan LKSA dari sesi
$id_user = $_SESSION['id_user'];
$id_lksa = $_SESSION['id_lksa'];

$sidebar_stats = ''; // Pastikan sidebar tampil

include '../includes/header.php'; 

// --- KONSTRUKSI URL PHP UNTUK JALUR ABSOLUT TERTINGGI (FIX 404) ---
// Memastikan $base_url sudah didefinisikan di header.php (misal: http://localhost/lksa_nh/)
// URL final yang dicari: http://localhost/lksa_nh/pages/get_wilayah.php
$api_full_url = $base_url . 'pages/get_wilayah.php';
// --------------------------------------------------------
?>
<div class="form-container">
    <h1>Tambah Kotak Amal Baru</h1>
    <form action="proses_kotak_amal.php" method="POST" enctype="multipart/form-data" id="kotakAmalForm">
        <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($id_user); ?>">
        <input type="hidden" name="id_lksa" value="<?php echo htmlspecialchars($id_lksa); ?>">
        
        <input type="hidden" name="alamat_toko" id="alamat_toko_hidden_final" required>
        
        <input type="hidden" name="provinsi_name" id="provinsi_name_hidden">
        <input type="hidden" name="kabupaten_name" id="kabupaten_name_hidden">
        <input type="hidden" name="kecamatan_name" id="kecamatan_name_hidden">
        <input type="hidden" name="kelurahan_name" id="kelurahan_name_hidden">

        <div class="form-section">
            <h2>Informasi Tempat</h2>
            <div class="form-group">
                <label>Nama Tempat:</label>
                <input type="text" name="nama_toko" required>
            </div>
        </div>

        <div class="form-section">
            <h2>Alamat Tempat (Database Wilayah Lokal)</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label>Provinsi:</label>
                    <select id="provinsi" required>
                        <option value="">-- Pilih Provinsi --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Kota/Kabupaten:</label>
                    <select id="kabupaten" required disabled>
                        <option value="">-- Pilih Kota/Kabupaten --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Kecamatan:</label>
                    <select id="kecamatan" required disabled>
                        <option value="">-- Pilih Kecamatan --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Kelurahan/Desa:</label>
                    <select id="kelurahan" required disabled>
                        <option value="">-- Pilih Kelurahan/Desa --</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Alamat Detail (Jalan, Nomor, RT/RW):</label>
                <textarea name="alamat_detail" id="alamat_detail" rows="2" required></textarea>
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
                    <label>Jadwal Pengambilan (Tanggal Mulai):</label>
                    <input type="date" name="jadwal_pengambilan" required> 
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
    const form = document.getElementById('kotakAmalForm');
    const finalAddressInput = document.getElementById('alamat_toko_hidden_final');
    const detailAddressInput = document.getElementById('alamat_detail');
    
    // --- Elemen Hidden Fields untuk Nama Wilayah ---
    const provinsiNameHidden = document.getElementById('provinsi_name_hidden');
    const kabupatenNameHidden = document.getElementById('kabupaten_name_hidden');
    const kecamatanNameHidden = document.getElementById('kecamatan_name_hidden');
    const kelurahanNameHidden = document.getElementById('kelurahan_name_hidden');
    // --------------------------------------------------

    // --- Geolocation Logic (Tidak Berubah) ---
    const getLocationButton = document.getElementById('getLocationButton');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');

    function getLocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error("Browser tidak mendukung geolocation."));
            }
            const options = { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 };
            navigator.geolocation.getCurrentPosition(pos => resolve(pos.coords), err => reject(err), options);
        });
    }

    getLocationButton.addEventListener('click', async () => {
        try {
            Swal.fire({ title: 'Mengambil Lokasi...', text: 'Mohon tunggu sebentar. Pastikan izin lokasi diaktifkan.', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            const coords = await getLocation();
            const { latitude, longitude } = coords;
            latitudeInput.value = latitude.toFixed(8);
            longitudeInput.value = longitude.toFixed(8);
            Swal.fire({ icon: 'success', title: 'Lokasi Berhasil Diambil!', text: `Lat: ${latitude.toFixed(6)}, Lng: ${longitude.toFixed(6)}. Data siap disimpan.`, confirmButtonColor: '#10B981', });
        } catch (err) {
            Swal.close();
            let errorMessage = 'Tidak bisa mendapatkan lokasi. Pastikan izin lokasi diaktifkan di browser Anda.';
            if (err.code === 1) { errorMessage = 'Anda menolak izin untuk mengakses lokasi.'; } 
            else if (err.code === 2) { errorMessage = 'Lokasi tidak tersedia atau gagal mendapatkan lokasi.'; } 
            else if (err.code === 3) { errorMessage = 'Waktu pengambilan lokasi habis. Coba lagi.'; } 
            else { errorMessage = `Terjadi kesalahan saat mengambil lokasi.`; }
            Swal.fire('Error!', errorMessage, 'error');
        }
    });


    // ====================================================================
    // === API WILAYAH LOKAL (Menggunakan get_wilayah.php) - JALUR ABSOLUT FIX =
    // ====================================================================
    
    // MENGGUNAKAN VARIABEL PHP YANG SUDAH DIBUAT DENGAN JALUR LENGKAP (API_URL)
    const API_URL = '<?php echo $api_full_url; ?>'; 

    const selectProvinsi = document.getElementById('provinsi');
    const selectKabupaten = document.getElementById('kabupaten');
    const selectKecamatan = document.getElementById('kecamatan');
    const selectKelurahan = document.getElementById('kelurahan');

    /**
     * Fungsi untuk mengambil data dari proxy PHP lokal.
     * @param {string} parentId - Kode induk wilayah (kosong untuk provinsi).
     * @returns {Promise<string>} Data wilayah dalam format HTML <option> tags.
     */
    async function fetchWilayah(parentId = "") {
        try {
            // Menggunakan API_URL absolut yang sudah diverifikasi oleh PHP
            const url = `${API_URL}${parentId ? `?id=${parentId}` : ''}&sid=${Math.random()}`;
            const response = await fetch(url);
            
            if (!response.ok) {
                // Memberikan status HTTP error yang jelas jika server gagal merespon (e.g., 404, 500)
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const responseText = await response.text(); 
            
            // Cek apakah responseText mengandung pesan error database
            if (responseText.includes("Error Database")) {
                 throw new Error(responseText);
            }

            return responseText;
        } catch (error) {
            console.error("Gagal mengambil data wilayah:", error);
            // Menampilkan error SweetAlert
            Swal.fire('Error Wilayah', error.message || 'Gagal memuat data wilayah. Cek jalur file.', 'error');
            return "<option value=''>Gagal memuat</option>";
        }
    }

    // 1. Ambil data Provinsi saat halaman dimuat
    (async () => {
        const htmlOptions = await fetchWilayah();
        selectProvinsi.innerHTML = htmlOptions;
        selectProvinsi.disabled = htmlOptions.includes("Gagal memuat") || htmlOptions.includes("Error");
        if (!selectProvinsi.disabled) selectProvinsi.disabled = false;
    })();

    // 2. Event Listeners untuk Chaining (Provinsi -> Kabupaten)
    selectProvinsi.addEventListener('change', async () => {
        selectKabupaten.innerHTML = '<option value="">Pilih Kota/Kabupaten</option>'; 
        selectKabupaten.disabled = true;
        selectKecamatan.disabled = true;
        selectKelurahan.disabled = true;
        selectKecamatan.innerHTML = '<option value="">Pilih Kecamatan</option>';
        selectKelurahan.innerHTML = '<option value="">Pilih Kelurahan/Desa</option>';

        const provId = selectProvinsi.value;
        if (provId) {
            const htmlOptions = await fetchWilayah(provId);
            selectKabupaten.innerHTML = htmlOptions;
            selectKabupaten.disabled = htmlOptions.includes("Gagal memuat") || htmlOptions.includes("Pilih Kota/Kabupaten") || htmlOptions.includes("Error");
            if (!selectKabupaten.disabled) selectKabupaten.disabled = false;
        }
    });

    // 3. Event Listeners untuk Chaining (Kabupaten -> Kecamatan)
    selectKabupaten.addEventListener('change', async () => {
        selectKecamatan.innerHTML = '<option value="">Pilih Kecamatan</option>';
        selectKecamatan.disabled = true;
        selectKelurahan.disabled = true;
        selectKelurahan.innerHTML = '<option value="">Pilih Kelurahan/Desa</option>';

        const kabId = selectKabupaten.value;
        if (kabId) {
            const htmlOptions = await fetchWilayah(kabId);
            selectKecamatan.innerHTML = htmlOptions;
            selectKecamatan.disabled = htmlOptions.includes("Gagal memuat") || htmlOptions.includes("Pilih Kecamatan") || htmlOptions.includes("Error");
            if (!selectKecamatan.disabled) selectKecamatan.disabled = false;
        }
    });

    // 4. Event Listeners untuk Chaining (Kecamatan -> Kelurahan)
    selectKecamatan.addEventListener('change', async () => {
        selectKelurahan.innerHTML = '<option value="">Pilih Kelurahan/Desa</option>';
        selectKelurahan.disabled = true;

        const kecId = selectKecamatan.value;
        if (kecId) {
            const htmlOptions = await fetchWilayah(kecId);
            selectKelurahan.innerHTML = htmlOptions;
            selectKelurahan.disabled = htmlOptions.includes("Gagal memuat") || htmlOptions.includes("Pilih Kelurahan/Desa") || htmlOptions.includes("Error");
            if (!selectKelurahan.disabled) selectKelurahan.disabled = false;
        }
    });
    
    /**
     * Mengambil nilai text dari selected option (untuk disimpan sebagai nama di DB).
     */
    function getSelectedText(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        if (selectedOption && selectedOption.value) {
            return selectedOption.textContent;
        }
        return '';
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault(); 
        
        // 1. Ambil nama wilayah dari dropdown
        const provinsiName = getSelectedText(selectProvinsi);
        const kabupatenName = getSelectedText(selectKabupaten);
        const kecamatanName = getSelectedText(selectKecamatan);
        const kelurahanName = getSelectedText(selectKelurahan);
        const alamatDetail = detailAddressInput.value.trim();

        if (!provinsiName || !kabupatenName || !kecamatanName || !kelurahanName || !alamatDetail) {
            Swal.fire('Peringatan', 'Mohon lengkapi semua isian alamat (Detail, Provinsi, Kota/Kabupaten, Kecamatan, dan Kelurahan/Desa).', 'warning');
            return; 
        }

        // --- Isi Hidden Fields untuk Nama Wilayah ---
        provinsiNameHidden.value = provinsiName;
        kabupatenNameHidden.value = kabupatenName;
        kecamatanNameHidden.value = kecamatanName;
        kelurahanNameHidden.value = kelurahanName;
        
        // 2. Gabungkan alamat lengkap
        let fullAddress = alamatDetail;
        if (kelurahanName) fullAddress += `, ${kelurahanName}`;
        if (kecamatanName) fullAddress += `, ${kecamatanName}`;
        if (kabupatenName) fullAddress += `, ${kabupatenName}`;
        if (provinsiName) fullAddress += `, ${provinsiName}`;
        
        // 3. Masukkan ke hidden field 'alamat_toko'
        finalAddressInput.value = fullAddress;

        // 4. Lanjutkan submit form secara manual
        form.submit();
    });

});
</script>

<?php
include '../includes/footer.php';
$conn->close();
?>