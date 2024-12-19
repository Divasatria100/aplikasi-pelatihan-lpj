<?php
// Konfigurasi koneksi database
$host = "localhost";          // Host database (biasanya localhost untuk pengembangan lokal)
$username = "root";           // Username default untuk XAMPP
$password = "";               // Password default kosong untuk XAMPP
$database = "db_ajukan";      // Nama database yang akan digunakan

// Membuat koneksi baru ke database MySQL menggunakan mysqli
$conn = new mysqli($host, $username, $password, $database);

// Memeriksa apakah koneksi ke database berhasil
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);  // Menghentikan program dan menampilkan pesan error jika koneksi gagal
}

// Memulai session PHP untuk manajemen status login
session_start();

// Memeriksa apakah user sudah login dengan mengecek session session_nik
if (!isset($_SESSION['session_username'])) {
    header("Location: /views/auth/login.php");  // Pastikan path dimulai dengan /
    exit();  // Menghentikan eksekusi script
}

// Mengambil nik login user dari session yang aktif
$nik = $_SESSION['session_nik'];  // Mengambil nilai session_nik dari session

// Query SQL untuk mengambil nama pengguna berdasarkan nik
$sqlUser = "
    SELECT u.nama 
    FROM user u 
    WHERE u.nik = '$nik'
";
$resultUser = $conn->query($sqlUser);  // Mengeksekusi query

// Memeriksa hasil query dan mengambil nama pengguna
if ($resultUser->num_rows > 0) {
    $user = $resultUser->fetch_assoc();  // Mengambil data dalam bentuk array associative
    $userName = $user['nama'];           // Menyimpan nama user ke variabel
} else {
    $userName = 'Nama Pengguna Tidak Ditemukan';  // Nilai default jika data tidak ditemukan
}

// Query SQL untuk mengambil daftar manajer dari database
$sqlManajer = "SELECT u.user_id, u.nama FROM user u WHERE u.role = 'manajer'";
$resultManajer = $conn->query($sqlManajer);  // Mengeksekusi query manajer
$manajers = [];  // Inisialisasi array kosong untuk menyimpan data manajer
if ($resultManajer->num_rows > 0) {
    while ($row = $resultManajer->fetch_assoc()) {
        $manajers[] = $row;  // Menambahkan setiap data manajer ke dalam array
    }
}

// Mengambil nama profile dari tabel user
$user_id = $_SESSION['user_id'];
$nama_query = "SELECT nama FROM user WHERE user_id = $user_id";
$nama_result = mysqli_query($conn, $nama_query);
$nama_row = mysqli_fetch_assoc($nama_result);
$nama_profile = $nama_row['nama'];

// Memproses data form ketika ada request POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengamankan input dari form menggunakan mysqli_real_escape_string untuk mencegah SQL injection
    $judul_pelatihan = mysqli_real_escape_string($conn, $_POST['judulPelatihan']);
    $jenis_pelatihan = mysqli_real_escape_string($conn, $_POST['jenisPelatihan']);
    $nama_peserta = isset($_POST['namaPeserta']) ? implode(", ", $_POST['namaPeserta']) : '';
    $lembaga = mysqli_real_escape_string($conn, $_POST['lembaga']);
    $jurusan = mysqli_real_escape_string($conn, $_POST['jurusan']);
    $program_studi = mysqli_real_escape_string($conn, $_POST['programStudi']);
    $tanggal_mulai = mysqli_real_escape_string($conn, $_POST['tanggalMulai']);
    $tanggal_selesai = mysqli_real_escape_string($conn, $_POST['tanggalSelesai']);
    $tempat = mysqli_real_escape_string($conn, $_POST['tempat']);
    $sumber_dana = mysqli_real_escape_string($conn, $_POST['sumberDana']);
    $manajer_pembimbing = mysqli_real_escape_string($conn, $_POST['manajer_pembimbing']);
    $target = mysqli_real_escape_string($conn, $_POST['target']);

    // Log input data
    error_log("Form Data: " . print_r($_POST, true));

    // Query SQL untuk menyimpan data usulan pelatihan ke database
    $nik = $_SESSION['session_nik'];  // Retrieve nik from session

    // Query to get user_id from user table based on nik
    $sqlUserId = "SELECT user_id FROM user WHERE nik = '$nik'";
    $resultUserId = $conn->query($sqlUserId);
    if ($resultUserId->num_rows > 0) {
        $row = $resultUserId->fetch_assoc();
        $user_id = $row['user_id'];
    } else {
        die("User ID not found for NIK: $nik");
    }

    $sql = "INSERT INTO usulan_pelatihan (user_id, judul_pelatihan, jenis_pelatihan, nama_peserta, lembaga, jurusan, program_studi, tanggal_mulai, tanggal_selesai, tempat, sumber_dana, manajer_pembimbing, target, status, lpj_status)
            VALUES ('$user_id', '$judul_pelatihan', '$jenis_pelatihan', '$nama_peserta', '$lembaga', '$jurusan', '$program_studi', '$tanggal_mulai', '$tanggal_selesai', '$tempat', '$sumber_dana', '$manajer_pembimbing', '$target', 'On Progress', 'Belum Diajukan')";

    // Log SQL query
    error_log("SQL Query: " . $sql);

    // Mengeksekusi query dan menangani hasilnya
    if ($conn->query($sql) === TRUE) {
        // Return a JSON response
        echo json_encode(['success' => true, 'message' => 'Form usulan pelatihan berhasil diajukan!']);
        exit(); // Stop further execution
    } else {
        error_log("Error: " . $sql . " - " . $conn->error);  // Mencatat error ke log sistem
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);  // Return error message as JSON
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
    <script src="/assets/js/form-usulan.js"></script>                                            <!-- Import file JavaScript untuk form -->
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
            <p class="profile-name"><?php echo $nama_profile; ?></p>                       <!-- Menampilkan nama pengguna -->
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
                    <label for="jurusan">Jurusan</label>                                       <!-- Label untuk dropdown jurusan -->
                    <select id="jurusan" name="jurusan" required>                             <!-- Dropdown untuk memilih jurusan -->
                        <option value="" disabled selected>Pilih Jurusan</option>              <!-- Opsi default/placeholder -->
                        <option value="TI">Teknik Informatika</option>                         <!-- Opsi jurusan TI -->
                        <option value="TM">Teknik Mesin</option>                              <!-- Opsi jurusan TM -->
                        <option value="TE">Teknik Elektro</option>                            <!-- Opsi jurusan TE -->
                        <option value="MB">Manajemen Bisnis</option>                          <!-- Opsi jurusan MB -->
                    </select>
                </div>

                <!-- Dropdown program studi -->
                <div class="form-group">
                    <label for="programStudi">Program Studi</label>                            <!-- Label untuk dropdown program studi -->
                    <select id="programStudi" name="programStudi" required>                    <!-- Dropdown untuk memilih program studi -->
                        <option value="" disabled selected>Pilih Program Studi</option>         <!-- Opsi default/placeholder -->
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
                    // Objek yang berisi daftar program studi untuk setiap jurusan
                    const programStudiOptions = {
                        TI: [                                                                      // Program studi untuk jurusan Teknik Informatika
                            { value: "Teknologi Rekayasa Perangkat Lunak", text: "Teknologi Rekayasa Perangkat Lunak" },
                            { value: "Teknologi Rekayasa Multimedia", text: "Teknologi Rekayasa Multimedia" },
                            { value: "Cybersecurity", text: "Rekayasa Keamanan Siber" },
                            { value: "Animasi", text: "Animasi" },
                            { value: "Teknik Informatika", text: "Teknik Informatika" },
                            { value: "Teknologi Geomatika", text: "Teknologi Geomatika" },
                            { value: "Teknologi Permainan", text: "Teknologi Permainan" },
                        ],
                        TM: [                                                                      // Program studi untuk jurusan Teknik Mesin
                            { value: "Teknik Perawatan Pesawat Udara", text: "Teknik Perawatan Pesawat Udara" },
                            { value: "Teknik Mesin", text: "Teknik Mesin" },
                            { value: "Teknologi Rekayasa Konstruksi Perkapalan", text: "Teknologi Rekayasa Konstruksi Perkapalan" },
                            { value: "Teknologi Rekayasa Pengelasan dan Fabrikasi", text: "Teknologi Rekayasa Pengelasan dan Fabrikasi" },
                            { value: "Program Profesi Insinyur", text: "Program Profesi Insinyur" },
                            { value: "Teknologi Rekayasa Metalurgi", text: "Teknologi Rekayasa Metalurgi" },
                        ],
                        TE: [                                                                      // Program studi untuk jurusan Teknik Elektro
                            { value: "Teknik Elektronika Manufaktur", text: "Teknik Elektronika Manufaktur" },
                            { value: "Teknik Instrumentasi", text: "Teknik Instrumentasi" },
                            { value: "Teknologi Rekayasa Pembangkit Energi", text: "Teknologi Rekayasa Pembangkit Energi" },
                            { value: "Teknologi Rekayasa Elektronika", text: "Teknologi Rekayasa Elektronika" },
                            { value: "Teknik Mekatronika", text: "Teknik Mekatronika" },
                            { value: "Teknologi Rekayasa Robotika", text: "Teknologi Rekayasa Robotika" },
                        ],
                        MB: [                                                                      // Program studi untuk jurusan Manajemen Bisnis
                            { value: "Akuntansi", text: "Akuntansi" },
                            { value: "Administrasi Bisnis Terapan", text: "Administrasi Bisnis Terapan" },
                            { value: "Administrasi Bisnis Terapan International Class", text: "Administrasi Bisnis Terapan International Class" },
                            { value: "Akuntansi Manajerial", text: "Akuntansi Manajerial" },
                            { value: "Logistik Perdagangan Internasional", text: "Logistik Perdagangan Internasional" },
                            { value: "Distribusi Barang", text: "Distribusi Barang" },
                        ],
                    };

                    // Mengambil referensi elemen select untuk jurusan dan program studi dari DOM
                    const jurusanSelect = document.getElementById("jurusan");                      // Mengambil elemen select jurusan
                    const programStudiSelect = document.getElementById("programStudi");            // Mengambil elemen select program studi

                    // Menambahkan event listener untuk menangani perubahan pada pilihan jurusan
                    jurusanSelect.addEventListener("change", function () {                         // Ketika jurusan dipilih
                        const selectedJurusan = this.value;                                        // Mengambil nilai jurusan yang dipilih

                        // Mengosongkan dan mereset pilihan program studi
                        programStudiSelect.innerHTML = '<option value="" disabled selected>Pilih Program Studi</option>';

                        // Menambahkan opsi program studi berdasarkan jurusan yang dipilih
                        if (programStudiOptions[selectedJurusan]) {                                // Jika ada program studi untuk jurusan yang dipilih
                            programStudiOptions[selectedJurusan].forEach((prodi) => {              // Loop setiap program studi
                                const option = document.createElement("option");                    // Membuat elemen option baru
                                option.value = prodi.value;                                        // Mengatur nilai option
                                option.textContent = prodi.text;                                   // Mengatur teks yang ditampilkan
                                programStudiSelect.appendChild(option);                            // Menambahkan option ke dalam select
                            });
                        }
                    });

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
            </form>
        </div>
    </main>
</body>
</html>