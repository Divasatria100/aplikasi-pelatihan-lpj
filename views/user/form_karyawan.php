<?php
// Konfigurasi koneksi database
$host = "localhost";          // Host database (biasanya localhost untuk pengembangan lokal)
$username = "root";           // Username default untuk XAMPP
$password = "";               // Password default kosong untuk XAMPP
$database = "db_ajukan";      // Nama database yang akan digunakan

// Membuat koneksi baru ke database MySQL menggunakan mysqli dengan error handling
try {
    $conn = new mysqli($host, $username, $password, $database);
    
    // Memeriksa apakah koneksi ke database berhasil
    if ($conn->connect_error) {
        throw new Exception("Koneksi gagal: " . $conn->connect_error);
    }
} catch (Exception $e) {
    // Log error dan tampilkan pesan yang user-friendly
    error_log($e->getMessage());
    die("Maaf, terjadi kesalahan dalam koneksi database. Silakan coba beberapa saat lagi.");
}

// Memulai session PHP untuk manajemen status login
session_start();

// Memeriksa apakah user sudah login dengan mengecek session_username
if (!isset($_SESSION['session_username'])) {
    header("Location: /views/auth/login.php");  // Redirect ke halaman login
    exit();  // Menghentikan eksekusi script
}

// Mengambil nik login user dari session yang aktif dengan validasi
$nik = isset($_SESSION['session_nik']) ? $_SESSION['session_nik'] : '';
if (empty($nik)) {
    error_log("NIK tidak ditemukan dalam session");
    header("Location: /views/auth/login.php");
    exit();
}

// Query SQL untuk mengambil nama pengguna berdasarkan nik dengan prepared statement
$sqlUser = $conn->prepare("SELECT nama FROM user WHERE nik = ?");
$sqlUser->bind_param("s", $nik);
$sqlUser->execute();
$resultUser = $sqlUser->get_result();

// Memeriksa hasil query dan mengambil nama pengguna
if ($resultUser->num_rows > 0) {
    $user = $resultUser->fetch_assoc();
    $userName = htmlspecialchars($user['nama']);  // Mencegah XSS
} else {
    $userName = 'Nama Pengguna Tidak Ditemukan';
}

// Query SQL untuk mengambil daftar manajer dari database dengan prepared statement
$sqlManajer = $conn->prepare("SELECT user_id, nama FROM user WHERE role = 'manajer'");
$sqlManajer->execute();
$resultManajer = $sqlManajer->get_result();
$manajers = [];

if ($resultManajer->num_rows > 0) {
    while ($row = $resultManajer->fetch_assoc()) {
        $manajers[] = [
            'user_id' => $row['user_id'],
            'nama' => htmlspecialchars($row['nama'])  // Mencegah XSS
        ];
    }
}

// Mengambil nama profile dari tabel user dengan prepared statement
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$nama_query = $conn->prepare("SELECT nama FROM user WHERE user_id = ?");
$nama_query->bind_param("i", $user_id);
$nama_query->execute();
$nama_result = $nama_query->get_result();
$nama_profile = '';

if ($nama_row = $nama_result->fetch_assoc()) {
    $nama_profile = htmlspecialchars($nama_row['nama']);
}

// Memproses data form ketika ada request POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validasi input
        $required_fields = ['judulPelatihan', 'jenisPelatihan', 'lembaga', 'jurusan_id', 'program_studi_id', 
                          'tanggalMulai', 'tanggalSelesai', 'tempat', 'sumberDana', 'manajer_pembimbing', 'target'];
        
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                throw new Exception("Field $field harus diisi");
            }
        }

        // Mengamankan input dari form menggunakan prepared statement
        $stmt = $conn->prepare("INSERT INTO usulan_pelatihan (user_id, judul_pelatihan, jenis_pelatihan, nama_peserta, 
                              lembaga, jurusan_id, program_studi_id, tanggal_mulai, tanggal_selesai, tempat, sumber_dana, 
                              manajer_pembimbing, target, status, lpj_status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'On Progress', 'Belum Diajukan')");
        
        $nama_peserta = isset($_POST['namaPeserta']) ? implode(", ", array_map('htmlspecialchars', $_POST['namaPeserta'])) : '';
        
        $stmt->bind_param("issssssssssss", 
            $user_id,
            $_POST['judulPelatihan'],
            $_POST['jenisPelatihan'],
            $nama_peserta,
            $_POST['lembaga'],
            $_POST['jurusan_id'],
            $_POST['program_studi_id'],
            $_POST['tanggalMulai'],
            $_POST['tanggalSelesai'],
            $_POST['tempat'],
            $_POST['sumberDana'],
            $_POST['manajer_pembimbing'],
            $_POST['target']
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Form usulan pelatihan berhasil diajukan!']);
            exit();
        } else {
            throw new Exception("Error dalam menyimpan data: " . $stmt->error);
        }

    } catch (Exception $e) {
        error_log("Error in form submission: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">                                                                         <!-- Pengaturan karakter encoding -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">                        <!-- Pengaturan viewport untuk responsive design -->
    <link rel="stylesheet" href="/assets/css/style.css">                                          <!-- Import file CSS utama -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap">  <!-- Import font Poppins dari Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">    <!-- Import Font Awesome untuk ikon -->
    <script src="/assets/js/form_usulan.js"></script>                                            <!-- Import file JavaScript untuk form -->
    <title>Form Usulan Pelatihan</title>                                                         <!-- Judul halaman web -->
    <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">          <!-- Favicon untuk tab browser -->
    <meta name="msapplication-TileColor" content="#ffffff">                                       <!-- Warna tile untuk Windows -->
    <meta name="theme-color" content="#ffffff">                                                   <!-- Warna tema untuk browser mobile -->
    <style>
        .remove-button {
            margin-left: 10px;                                                                    /* Jarak kiri untuk tombol hapus */
        }
        .add-button {
            margin-top: 10px;                                                                     /* Jarak atas untuk tombol tambah */
        }
    </style>
</head>
<body>
    <!-- Navbar dengan logo dan profil -->
    <nav class="navbar">
        <img class="logo" src="/assets/images/Logo_Ajukan.png" alt="Ajukan" style="width: fit-content;">    <!-- Logo aplikasi -->
        <div class="profile">
            <img class="profile-image" src="https://web.rupa.ai/wp-content/uploads/2023/08/aruna3619_Elegant_black_and_white_profesional_photo_of_a_young__46c0ef4a-ba0a-4e23-af98-c824d9b7127d.png" alt="Profile Image">    <!-- Gambar profil pengguna -->
            <p class="profile-name"><?php echo htmlspecialchars($nama_profile); ?></p>                       <!-- Menampilkan nama pengguna -->
        </div>
    </nav>

    <!-- Toggle untuk sidebar mobile -->
    <input type="checkbox" id="toggle">                                                          <!-- Checkbox untuk toggle sidebar -->
    <label for="toggle" class="side-toggle">&#9776;</label>                                     <!-- Label/tombol toggle sidebar -->
    
    <!-- Sidebar menu navigasi -->
    <div class="sidebar">
        <div class="sidebar-menu">
            <a href="/views/dashboard/karyawan/dashboard_karyawan.php" class="switch-button active">    <!-- Menu Dashboard -->
                <span class="fas fa-home"></span>
                <span class="label"> Dashboard</span>
            </a>
        </div>
        <div class="sidebar-menu">
            <a href="/views/user/identitas.php" class="switch-button">                           <!-- Menu Identitas -->
                <span class="fas fa-user"></span>
                <span class="label"> Identitas</span>
            </a>
        </div>
        <div class="sidebar-menu">
            <a href="/views/user/history.php" class="switch-button">                             <!-- Menu History -->
                <span class="fas fa-clock"></span>
                <span class="label"> History</span>
            </a>
        </div>
        <br>
        <br>
        <div class="sidebar-menu">
            <a href="/views/auth/logout.php" class="switch-button">                              <!-- Menu Logout -->
                <span class="fas fa-sign-out-alt"></span>
                <span class="label"> Logout</span>
            </a>
        </div>
    </div>

    <!-- Konten utama halaman -->
    <main class="main">
        <div class="section-title">Form Usulan Pelatihan</div>                                   <!-- Judul halaman -->
        <div class="form-container">
            <form id="trainingForm" action="" method="POST">                                     <!-- Form usulan pelatihan -->
                <!-- Input judul pelatihan -->
                <div class="form-group">
                    <label>Judul Pelatihan</label>                                               <!-- Label untuk input judul pelatihan -->
                    <input type="text" id="judulPelatihan" name="judulPelatihan" class="form-control" placeholder="Detail Pelatihan" required>    <!-- Input field untuk judul pelatihan -->
                </div>


                <!-- Dropdown jenis pelatihan -->
                <div class="form-group">
                    <label for="jenisPelatihan">Jenis Pelatihan</label>                         <!-- Label untuk dropdown jenis pelatihan -->
                    <select id="jenisPelatihan" name="jenisPelatihan" required>                 <!-- Dropdown untuk memilih jenis pelatihan -->
                        <option value="" disabled selected>Pilih Jenis Pelatihan</option>        <!-- Opsi default/placeholder -->
                        <option value="Workshop">Workshop</option>                               <!-- Opsi untuk workshop -->
                        <option value="Magister">Magister</option>                              <!-- Opsi untuk magister -->
                        <option value="Seminar">Seminar</option>                                <!-- Opsi untuk seminar -->
                        <option value="Pelatihan">Pelatihan</option>                            <!-- Opsi untuk pelatihan -->
                    </select>
                </div>

                <!-- Tabel input nama peserta -->
                <div class="form-group">
                    <label>Nama Peserta</label>                                                 <!-- Label untuk tabel peserta -->
                    <table id="pesertaTable" class="table">                                    <!-- Tabel untuk daftar peserta -->
                        <tbody id="pesertaContainer">                                          <!-- Container untuk baris-baris peserta -->
                            <tr>                                                               <!-- Baris default untuk peserta pertama -->
                                <td>
                                    <input type="text" name="namaPeserta[]" class="form-control" placeholder="Nama Peserta" required>    <!-- Input field untuk nama peserta -->
                                </td>
                                <td>
                                <!-- Tombol untuk menghapus peserta dihapus -->
                                <button type="button" class="remove-button" onclick="removePeserta(this)"><i class="fas fa-minus"></i></button>    <!-- Tombol untuk menghapus peserta -->
                                <button type="button" class="add-button" onclick="addPeserta()"><i class="fas fa-plus"></i></button>    <!-- Tombol untuk menambah peserta baru -->
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div>
                    </div>
                </div>

                <!-- Input lembaga/institusi -->
                <div class="form-group">
                    <label>Lembaga / Institusi</label>                                         <!-- Label untuk input lembaga -->
                    <input type="text" id="lembaga" name="lembaga" class="form-control" placeholder="Lembaga / Institusi" required>    <!-- Input field untuk nama lembaga -->
                </div>

                <!-- Dropdown jurusan -->
                <div class="form-group">
                    <label for="jurusan_id">Jurusan</label>
                    <select id="jurusan_id" name="jurusan_id" required onchange="updateProdiOptions()">
                        <option value="">Pilih Jurusan</option>
                        <?php 
                        $sqlJurusan = "SELECT * FROM jurusan ORDER BY nama_jurusan";
                        $resultJurusan = $conn->query($sqlJurusan);
                        while ($row = $resultJurusan->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row['jurusan_id']) . '">' . htmlspecialchars($row['nama_jurusan']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <!-- Dropdown program studi -->
                <div class="form-group">
                    <label for="program_studi_id">Program Studi</label>
                    <select id="program_studi_id" name="program_studi_id" required>
                        <option value="">Pilih Program Studi</option>
                    </select>
                </div>


                <!-- Dropdown pilih manajer -->
                <div class="form-group">
                    <label>Manajer</label>                                                     <!-- Label untuk dropdown manajer -->
                    <select id="manajer" name="manajer_pembimbing" required>                  <!-- Dropdown untuk memilih manajer -->
                        <option value="" disabled selected>Pilih Manajer</option>              <!-- Opsi default/placeholder -->
                        <?php foreach ($manajers as $manajer) { ?>                            <!-- Loop untuk menampilkan daftar manajer -->
                            <option value="<?= htmlspecialchars($manajer['user_id']) ?>"><?= htmlspecialchars($manajer['nama']) ?></option>    <!-- Opsi untuk setiap manajer -->
                        <?php } ?>
                    </select>
                </div>

                <!-- Input tanggal mulai dan selesai -->
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Tanggal Mulai</label>                                           <!-- Label untuk input tanggal mulai -->
                        <input type="date" id="tanggalMulai" name="tanggalMulai" class="form-control" required>    <!-- Input field untuk tanggal mulai -->
                    </div>
                    <div class="form-group col-md-6">
                        <label>Tanggal Selesai</label>                                         <!-- Label untuk input tanggal selesai -->
                        <input type="date" id="tanggalSelesai" name="tanggalSelesai" class="form-control" required>    <!-- Input field untuk tanggal selesai -->
                    </div>
                </div>

                <!-- Input tempat/alamat -->
                <div class="form-group">
                    <label>Tempat / Alamat</label>                                             <!-- Label untuk textarea tempat -->
                    <textarea id="tempat" name="tempat" class="form-control" rows="4" placeholder="Tempat / Alamat" required></textarea>    <!-- Textarea untuk alamat tempat -->
                </div>

                <!-- Input sumber dana -->
                <div class="form-group">
                    <label>Sumber Dana (Kode Account)</label>                                   <!-- Label untuk input sumber dana -->
                    <input type="text" id="sumberDana" name="sumberDana" class="form-control" required>    <!-- Input field untuk sumber dana -->
                </div>

                <!-- Input target -->
                <div class="form-group">
                    <label>Target yang ingin dicapai</label>                                    <!-- Label untuk textarea target -->
                    <textarea id="target" name="target" class="form-control" rows="4" placeholder="Target yang ingin dicapai" required></textarea>    <!-- Textarea untuk target -->
                </div>

                <!-- Tombol aksi form -->
                <div class="form-actions">
<button type="button" class="training-button" id="submit-btn">Ajukan Pelatihan</button>    <!-- Tombol untuk submit form -->
<script>
    document.getElementById('submit-btn').addEventListener('click', function() {
        const form = document.getElementById('trainingForm');
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('notification').style.display = 'block';
                form.reset(); // Reset the form after successful submission
                setTimeout(() => {
                    window.location.href = '/views/dashboard/karyawan/dashboard_karyawan.php';
                }, 2000); // Redirect after 2 seconds
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengajukan pelatihan.');
        });
    });
</script>
                    <a href="/views/dashboard/karyawan/dashboard_karyawan.php" class="training-button">Cancel</a>    <!-- Tombol untuk membatalkan -->
                </div>

                <!-- Notifikasi sukses -->
                <div id="notification" style="display: none; background-color: #4CAF50; color: white; padding: 10px; border-radius: 5px; margin-top: 10px;">
                    Form berhasil dikirim!    <!-- Pesan notifikasi ketika form berhasil dikirim -->
                </div>
                <!-- Script JavaScript untuk dropdown dinamis dan manajemen peserta -->
                <script>
                   function updateProdiOptions() {
    // Mengambil referensi elemen select untuk jurusan dan program studi dari DOM
    const jurusanSelect = document.getElementById("jurusan_id");                      // Mengambil elemen select jurusan
    const programStudiSelect = document.getElementById("program_studi_id");            // Mengambil elemen select program studi

    // Mengosongkan dan mereset pilihan program studi
    programStudiSelect.innerHTML = '<option value="" disabled selected>Pilih Program Studi</option>';

    const selectedJurusan = jurusanSelect.value;  // Mengambil nilai jurusan yang dipilih

    // Menambahkan opsi program studi berdasarkan jurusan yang dipilih
    if (selectedJurusan) {
        fetch(`/get_program_studi.php?jurusan_id=${selectedJurusan}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(prodi => {
                    const option = document.createElement("option");
                    option.value = prodi.program_studi_id;
                    option.textContent = prodi.nama_prodi;
                    programStudiSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
}


                    // Fungsi untuk menambahkan peserta baru ke dalam form
function addPeserta() {
    const pesertaContainer = document.getElementById("pesertaContainer");       // Mengambil container peserta
    const newRow = document.createElement("tr");                               // Membuat baris baru

    // Membuat sel untuk input nama peserta
    const newCellInput = document.createElement("td");                         // Membuat sel untuk input
    const newInput = document.createElement("input");                          // Membuat elemen input
    newInput.type = "text";                                                    // Mengatur tipe input sebagai text
    newInput.name = "namaPeserta[]";                                          // Mengatur nama input
    newInput.className = "form-control";                                       // Menambahkan class CSS
    newInput.placeholder = "Nama Peserta";                                     // Mengatur placeholder
    newInput.required = true;                                                  // Membuat input wajib diisi
    newCellInput.appendChild(newInput);                                        // Menambahkan input ke dalam sel

    // Membuat sel untuk tombol
    const newCellButtons = document.createElement("td");                       // Membuat sel untuk tombol
    newCellButtons.innerHTML = `
        <button type="button" class="remove-button" onclick="removePeserta(this)">
            <i class="fas fa-minus"></i>
        </button>
        <button type="button" class="add-button" onclick="addPeserta()">
            <i class="fas fa-plus"></i>
        </button>
    `;

    // Menggabungkan semua elemen
    newRow.appendChild(newCellInput);                                          // Menambahkan sel input ke baris
    newRow.appendChild(newCellButtons);                                        // Menambahkan sel tombol ke baris
    pesertaContainer.appendChild(newRow);                                      // Menambahkan baris ke container
}

                // Fungsi untuk menghapus peserta dari form
                function removePeserta(button) {
                    const pesertaContainer = document.getElementById("pesertaContainer");       // Mengambil container peserta
                    const totalRows = pesertaContainer.getElementsByTagName("tr").length;      // Menghitung jumlah peserta

                    if (totalRows > 1) {                                                       // Jika masih ada lebih dari 1 peserta
                        const row = button.closest('tr');                                      // Ambil baris yang sesuai
                        row.remove();                                                          // Hapus baris peserta
                    } else {
                        alert("Minimal satu peserta harus ada.");                              // Tampilkan peringatan jika hanya 1 peserta
                    }
                }
                </script>

                <script>
                function updateProdiOptions() {
                const jurusanId = document.getElementById('jurusan_id').value;
                const prodiSelect = document.getElementById('program_studi_id');
                
                // Clear current options
                prodiSelect.innerHTML = '<option value="">Pilih Program Studi</option>';
                
                if (jurusanId) {
                    // Fetch program studi for selected jurusan
                    fetch(`get_prodi.php?jurusan_id=${jurusanId}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(prodi => {
                                const option = document.createElement('option');
                                option.value = prodi.program_studi_id;
                                option.textContent = prodi.nama_program_studi;
                                prodiSelect.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                    }
                }
                </script>
            </form>
        </div>
    </main>
</body>
</html>