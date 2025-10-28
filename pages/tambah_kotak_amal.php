<?php
session_start();
include '../config/database.php';

// Authorization check: Hanya Pimpinan, Kepala LKSA, dan Petugas Kotak Amal yang bisa mengakses
if ($_SESSION['jabatan'] != 'Pimpinan' && $_SESSION['jabatan'] != 'Kepala LKSA' && $_SESSION['jabatan'] != 'Petugas Kotak Amal') {
    die("Akses ditolak.");
}

// Ambil ID pengguna dan LKSA dari sesi
$id_user = $_SESSION['id_user'];
$id_lksa = $_SESSION['id_lksa'];

$sidebar_stats = ''; // Pastikan sidebar tampil

include '../includes/header.php'; 
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
            <h2>Alamat Tempat (API Wilayah)</h2>
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
    
    // --- Elemen Hidden Fields Baru untuk Nama Wilayah ---
    const provinsiNameHidden = document.getElementById('provinsi_name_hidden');
    const kabupatenNameHidden = document.getElementById('kabupaten_name_hidden');
    const kecamatanNameHidden = document.getElementById('kecamatan_name_hidden');
    const kelurahanNameHidden = document.getElementById('kelurahan_name_hidden');
    // --------------------------------------------------

    // ... (Kode Geolocation, tidak berubah) ...
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


    // --- API Wilayah Logic (MOCK) ---
    const selectProvinsi = document.getElementById('provinsi');
    const selectKabupaten = document.getElementById('kabupaten');
    const selectKecamatan = document.getElementById('kecamatan');
    const selectKelurahan = document.getElementById('kelurahan');

    // URL MOCK UNTUK DEMONSTRASI LOGIKA CHAINING (Ganti dengan API sungguhan)
    const API_URL = 'https://mock-api.com/api/wilayah/indonesia'; 

    async function fetchData(url) {
        // --- MOCK DATA SIMULASI API WILAYAH ---
        const mockData = {
            'provinces': [
                { id: '33', name: 'JAWA TENGAH' },
                { id: '34', name: 'DI YOGYAKARTA' }
            ],
            'regencies': {
                '33': [{ id: '3372', name: 'KOTA SURAKARTA' }, { id: '3301', name: 'KABUPATEN CILACAP' }],
                '34': [{ id: '3404', name: 'KABUPATEN SLEMAN' }, { id: '3471', name: 'KOTA YOGYAKARTA' }]
            },
            'districts': {
                '3372': [{ id: '337201', name: 'PASAR KLIWON' }, { id: '337202', name: 'SERENGAN' }],
                '3404': [{ id: '340401', name: 'GAMPING' }, { id: '340402', name: 'MLATI' }]
            },
            'villages': {
                '337201': [{ id: '3372010001', name: 'SANGKRAH' }, { id: '3372010002', name: 'SEWU' }],
                '340401': [{ id: '3404010001', name: 'BALECATUR' }, { id: '3404010002', name: 'BANYURADEN' }]
            }
        };

        return new Promise(resolve => {
            setTimeout(() => {
                if (url.includes('provinces')) {
                    resolve(mockData.provinces);
                } else if (url.includes('/regencies/')) {
                    const id = url.split('/regencies/')[1];
                    resolve(mockData.regencies[id] || []);
                } else if (url.includes('/districts/')) {
                    const id = url.split('/districts/')[1];
                    resolve(mockData.districts[id] || []);
                } else if (url.includes('/villages/')) {
                    const id = url.split('/villages/')[1];
                    resolve(mockData.villages[id] || []);
                } else {
                    resolve([]);
                }
            }, 500);
        });
    }

    function populateDropdown(dropdown, data, placeholder) {
        dropdown.innerHTML = `<option value="">${placeholder}</option>`;
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id; 
            option.textContent = item.name;
            option.setAttribute('data-name', item.name); 
            dropdown.appendChild(option);
        });
        dropdown.disabled = (data.length === 0);
    }

    // 1. Ambil data Provinsi saat halaman dimuat
    (async () => {
        const provinces = await fetchData(`${API_URL}/provinces`);
        populateDropdown(selectProvinsi, provinces, '-- Pilih Provinsi --');
    })();

    // 2. Event Listeners untuk Chaining (Provinsi -> Kabupaten)
    selectProvinsi.addEventListener('change', async () => {
        selectKabupaten.innerHTML = '<option value="">-- Memuat... --</option>';
        selectKabupaten.disabled = true;
        selectKecamatan.disabled = true;
        selectKelurahan.disabled = true;
        selectKecamatan.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
        selectKelurahan.innerHTML = '<option value="">-- Pilih Kelurahan/Desa --</option>';

        if (selectProvinsi.value) {
            const regencies = await fetchData(`${API_URL}/regencies/${selectProvinsi.value}`);
            populateDropdown(selectKabupaten, regencies, '-- Pilih Kota/Kabupaten --');
        }
    });

    // 3. Event Listeners untuk Chaining (Kabupaten -> Kecamatan)
    selectKabupaten.addEventListener('change', async () => {
        selectKecamatan.innerHTML = '<option value="">-- Memuat... --</option>';
        selectKecamatan.disabled = true;
        selectKelurahan.disabled = true;
        selectKelurahan.innerHTML = '<option value="">-- Pilih Kelurahan/Desa --</option>';

        if (selectKabupaten.value) {
            const districts = await fetchData(`${API_URL}/districts/${selectKabupaten.value}`);
            populateDropdown(selectKecamatan, districts, '-- Pilih Kecamatan --');
        }
    });

    // 4. Event Listeners untuk Chaining (Kecamatan -> Kelurahan)
    selectKecamatan.addEventListener('change', async () => {
        selectKelurahan.innerHTML = '<option value="">-- Memuat... --</option>';
        selectKelurahan.disabled = true;

        if (selectKecamatan.value) {
            const villages = await fetchData(`${API_URL}/villages/${selectKecamatan.value}`);
            populateDropdown(selectKelurahan, villages, '-- Pilih Kelurahan/Desa --');
        }
    });
    
    // --- Logika Penggabungan Alamat Final SEBELUM Submit ---
    function getSelectedText(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        return selectedOption ? selectedOption.getAttribute('data-name') : '';
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault(); 
        
        // 1. Ambil nama wilayah dan alamat detail
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
        
        // 2. Gabungkan alamat
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