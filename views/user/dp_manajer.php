<?php
// Konfigurasi koneksi database
$host = "localhost";                // Host database (biasanya localhost)
$username = "root";                 // Username database (default XAMPP)
$password = "";                     // Password database (kosong untuk XAMPP)
$database = "db_ajukan";            // Nama database yang digunakan

// Membuat koneksi ke database menggunakan mysqli
$conn = new mysqli($host, $username, $password, $database);

// Memeriksa apakah koneksi berhasil
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);    // Tampilkan pesan error jika koneksi gagal
}

// Memulai session untuk manajemen login
session_start();

// Redirect ke halaman login jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: /views/auth/login.php");     // Arahkan ke halaman login
    exit();                                         // Hentikan eksekusi script
}

// Mengambil user_id dari session
$user_id = $_SESSION['user_id'];                   // Mengambil ID pengguna dari session

// Query untuk mengambil nama user dari database
$sqlUser  = "SELECT nama FROM user WHERE user_id = $user_id";
$resultUser = $conn->query($sqlUser);      // Eksekusi query

// Memeriksa dan mengambil nama user
if ($resultUser->num_rows > 0) {
    $user = $resultUser->fetch_assoc();     // Ambil data user
    $userName = $user['nama'];              // Simpan nama user
} else {
    $userName = 'Nama Pengguna Tidak Ditemukan';  // Set default jika tidak ditemukan
}

// Mengambil ID usulan dari parameter URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0; // Mengambil ID usulan dari URL

// Query untuk mengambil detail usulan pelatihan
$sql = "SELECT * FROM usulan_pelatihan WHERE id = $id";
$result = $conn->query($sql);

// Memeriksa dan mengambil data usulan pelatihan
if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();                     // Ambil data usulan
    $judulPelatihan = $data['judul_pelatihan'];        // Judul pelatihan
    $jenisPelatihan = $data['jenis_pelatihan'];        // Jenis pelatihan
    $namaPeserta = explode(',', $data['nama_peserta']); // Array nama peserta
    $lembaga = $data['lembaga'];                       // Nama lembaga
    $jurusan_id = $data['jurusan_id'];                       // Jurusan
    $program_studi_id = $data['program_studi_id'];            // Program studi
    $tanggalMulai = $data['tanggal_mulai'];           // Tanggal mulai
    $tanggalSelesai = $data['tanggal_selesai'];       // Tanggal selesai
    $tempat = $data['tempat'];                         // Tempat pelatihan
    $sumberDana = $data['sumber_dana'];                // Sumber dana
    $namaManajer = $data['manajer_pembimbing'];       // Nama manajer
    $target = $data['target'];                         // Target pelatihan
    $status = $data['status'];                         // Status usulan
    $lpjStatus = $data['lpj_status'];                  // Status LPJ
} else {
    // Set semua variabel ke string kosong jika data tidak ditemukan
    $judulPelatihan = $jenisPelatihan = $namaPeserta = $lembaga = $jurusan_id = $program_studi_id = 
    $tanggalMulai = $tanggalSelesai = $tempat = $sumberDana = $namaManajer = 
    $target = $status = $lpjStatus = '';
}

// Query to get jurusan name
$sql_jurusan = "SELECT nama_jurusan FROM jurusan WHERE jurusan_id = '$jurusan_id'";
$result_jurusan = $conn->query($sql_jurusan);
$jurusan_name = ($result_jurusan && $result_jurusan->num_rows > 0) 
    ? $result_jurusan->fetch_assoc()['nama_jurusan'] 
    : 'Jurusan Tidak Ditemukan';

// Query to get program studi name with correct column names
$sql_prodi = "SELECT nama_program_studi FROM program_studi WHERE program_studi_id = '$program_studi_id'";
$result_prodi = $conn->query($sql_prodi);
$prodi_name = ($result_prodi && $result_prodi->num_rows > 0) 
    ? $result_prodi->fetch_assoc()['nama_program_studi'] 
    : 'Program Studi Tidak Ditemukan';

// Query to get manager's name from user table
$sql_manager = "SELECT nama FROM user WHERE user_id = '$namaManajer'";
$result_manager = $conn->query($sql_manager);
$manager_name = ($result_manager && $result_manager->num_rows > 0) 
    ? $result_manager->fetch_assoc()['nama'] 
    : 'Manajer Tidak Ditemukan';

// Proses submit status usulan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newStatus = $_POST['status'];                     // Ambil status baru dari form
    $updateSql = "UPDATE usulan_pelatihan SET status = '$newStatus' WHERE id = $id";
    
    // Eksekusi query update
    if ($conn->query($updateSql) === TRUE) {
        // Tampilkan alert dan redirect jika berhasil
        echo "<script>
                alert('Status usulan pelatihan berhasil diperbarui!');
                window.location.href = '/views/dashboard/manajer/dashboard_manajer.php';
            </script>";
    } else {
        echo "Error: " . $conn->error;                 // Tampilkan error jika gagal
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">                                                                         <!-- Pengaturan karakter encoding -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">                        <!-- Pengaturan viewport untuk responsive design -->
    <link rel="stylesheet" href="/assets/css/style.css">                                          <!-- Import file CSS utama -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap">    <!-- Import font Poppins dari Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">      <!-- Import icon Font Awesome -->
    <title>Detail Usulan Pelatihan</title>                                                        <!-- Judul halaman web -->
    <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">         <!-- Favicon untuk tab browser -->
    <style>
        textarea {
            resize: vertical;      /* Membatasi resize textarea hanya secara vertikal */
            height: 100px;        /* Mengatur tinggi default textarea */
            overflow-y: auto;     /* Menambahkan scrollbar otomatis jika konten melebihi tinggi */
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <img class="logo" src="/assets/images/Logo_Ajukan.png" alt="Ajukan" style="width: fit-content;">    <!-- Logo aplikasi -->
        <div class="profile">
            <img class="profile-image" src="https://web.rupa.ai/wp-content/uploads/2023/08/aruna3619_Elegant_black_and_white_profesional_photo_of_a_young__46c0ef4a-ba0a-4e23-af98-c824d9b7127d.png" alt="Profile Image">    <!-- Gambar profil pengguna -->
            <p class="profile-name"><?php echo $userName; ?></p>                                   <!-- Menampilkan nama pengguna -->
        </div>
    </nav>

    <!-- Sidebar Toggle Button -->
    <input type="checkbox" id="toggle">                                                           <!-- Checkbox untuk toggle sidebar -->
    <label for="toggle" class="side-toggle">&#9776;</label>                                      <!-- Label/tombol untuk toggle sidebar -->

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-menu">
            <a href="/views/dashboard/manajer/dashboard_manajer.php" class="switch-button active">
                <span class="fas fa-home"></span>                                                 <!-- Icon menu dashboard -->
                <span class="label"> Dashboard</span>                                             <!-- Label menu dashboard -->
            </a>
        </div>
        <div class="sidebar-menu">
            <a href="/views/user/identitas.php" class="switch-button">    
                <span class="fas fa-user"></span>    
                <span class="label"> Identitas</span>    
            </a>
        </div>
        <div class="sidebar-menu">
            <a href="/views/user/history.php" class="switch-button">
                <span class="fas fa-clock"></span>                                                <!-- Icon menu history -->
                <span class="label"> History</span>                                               <!-- Label menu history -->
            </a>
        </div>
        <br>
        <br>
        <div class="sidebar-menu">
            <a href="/views/auth/logout.php" class="switch-button">
                <span class="fas fa-sign-out-alt"></span>                                         <!-- Icon menu logout -->
                <span class="label"> Logout</span>                                                <!-- Label menu logout -->
            </a>
        </div>
    </div>

    <!-- Main Content Area -->
    <main class="main">
        <div class="section-title">Detail Usulan Pelatihan</div>                                 <!-- Judul section -->
        <div class="form-container">
            <form id="trainingForm" method="POST" action="">                                      <!-- Form untuk detail pelatihan -->
                <div class="form-group">
                    <label> Judul Pelatihan</label>                                                <!-- Label input judul pelatihan -->
                    <input type="text" class="form-control" name="judulPelatihan" value="<?php echo $judulPelatihan; ?>" disabled>    <!-- Input judul pelatihan -->
                </div>
                <div class="form-group">
                    <label>Jenis Pelatihan</label> <input type="text" class="form-control" name="jenisPelatihan" value="<?php echo $jenisPelatihan; ?>" disabled>    <!-- Input jenis pelatihan -->
                </div>
                <div class="form-group">
                    <label>Nama Peserta</label>                                                   <!-- Label input nama peserta -->
                    <input type="text" class="form-control" name="namaPeserta" value="<?php echo implode(', ', array_map('trim', $namaPeserta)); ?>" disabled>    <!-- Input nama peserta -->
                </div>
                <div class="form-group">
                    <label>Lembaga / Institusi</label>                                            <!-- Label input lembaga -->
                    <input type="text" class="form-control" name="lembaga" value="<?php echo $lembaga; ?>" disabled>    <!-- Input lembaga -->
                </div>
                <div class="form-group">
                    <label>Jurusan</label>                                                        <!-- Label input jurusan -->
                    <input type="text" class="form-control" name="jurusan" value="<?php echo $jurusan_name; ?>" disabled>    <!-- Input jurusan -->
                </div>
                <div class="form-group">
                    <label>Program Studi</label>                                                  <!-- Label input program studi -->
                    <input type="text" class="form-control" name="programStudi" value="<?php echo $prodi_name; ?>" disabled>    <!-- Input program studi -->
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Tanggal Mulai</label>                                             <!-- Label input tanggal mulai -->
                        <input type="text" class="form-control" name="tanggalMulai" value="<?php echo $tanggalMulai; ?>" disabled>    <!-- Input tanggal mulai -->
                    </div>
                    <div class="form-group col-md-6">
                        <label>Tanggal Selesai</label>                                           <!-- Label input tanggal selesai -->
                        <input type="text" class="form-control" name="tanggalSelesai" value="<?php echo $tanggalSelesai; ?>" disabled>    <!-- Input tanggal selesai -->
                    </div>
                </div>
                <div class="form-group">
                    <label>Tempat / Alamat</label>                                               <!-- Label input tempat -->
                    <textarea class="form-control" name="tempat" disabled><?php echo $tempat; ?></textarea>    <!-- Input tempat -->
                </div>
                <div class="form-group">
                    <label>Sumber Dana (Kode Account)</label>                                     <!-- Label input sumber dana -->
                    <input type="text" class="form-control" name="sumberDana" value="<?php echo $sumberDana; ?>" disabled>    <!-- Input sumber dana -->
                </div>
                <div class="form-group">
                    <label>Manajer</label>                                                        <!-- Label input manajer -->
                    <input type="text" class="form-control" name="namaManajer" value="<?php echo $manager_name; ?>" disabled>    <!-- Input nama manajer -->
                </div>
                <div class="form-group">
                    <label>Target</label>                                                         <!-- Label input target -->
                    <textarea class="form-control" name="target" disabled><?php echo $target; ?></textarea>    <!-- Input target -->
                </div>
                <div class="form-group">
                    <label>Status Persetujuan</label>                                            <!-- Label input status -->
                    <select name="status" class="form-control" <?php echo ($status == 'Disetujui' || $status == 'Ditolak') ? 'disabled' : ''; ?>>                         <!-- Dropdown untuk status persetujuan -->
                        <option value="" disabled selected>Pilih Status</option>                 <!-- Opsi default -->
                        <option value="Disetujui" <?php echo $status == 'Disetujui' ? ' selected' : ''; ?>>Disetujui</option> <!-- Opsi disetujui -->
                        <option value="Ditolak" <?php echo $status == 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option> <!-- Opsi ditolak -->
                    </select>
                </div>
                            <!-- Tombol aksi -->
                            <div class="form-actions">
                    <?php if ($lpjStatus !== 'Disetujui' && $status !== 'Disetujui') { ?> <!-- Cek status LPJ dan status usulan -->
                        <button type="submit" class="training-button">Submit</button>                <!-- Tombol submit form -->
                    <?php } ?>
                    <a href="/views/dashboard/manajer/dashboard_manajer.php" class="training-button">Kembali</a>    <!-- Tombol kembali ke dashboard -->
            </form>
        </div>
    </main>
    </body>
</html>