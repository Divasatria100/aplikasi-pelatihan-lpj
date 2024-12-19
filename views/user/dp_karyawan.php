<?php
// Konfigurasi koneksi database
$host = "localhost";          // Alamat host database
$username = "root";           // Username untuk akses database (default XAMPP)
$password = "";               // Password untuk akses database (default kosong di XAMPP) 
$database = "db_ajukan";      // Nama database yang digunakan

// Membuat koneksi baru ke database MySQL menggunakan mysqli
$conn = new mysqli($host, $username, $password, $database);

// Memeriksa apakah koneksi berhasil
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);    // Menampilkan pesan error jika koneksi gagal
}

// Manajemen Session
session_start();              // Memulai session PHP

// Validasi keberadaan session user_id
if (!isset($_SESSION['user_id'])) {
    header("Location: /views/auth/login.php");    // Redirect ke halaman login jika belum login
    exit();
}

// Mengambil user_id dari session
$user_id = $_SESSION['user_id'];     // Menyimpan user_id ke variabel

// Query untuk mengambil data nama pengguna
$sqlUser = "SELECT nama FROM user WHERE user_id = $user_id";
$resultUser = $conn->query($sqlUser);      // Eksekusi query

// Validasi dan pengambilan data pengguna
if ($resultUser->num_rows > 0) {
    $user = $resultUser->fetch_assoc();    // Mengambil data dalam bentuk array associative
    $userName = $user['nama'];             // Menyimpan nama pengguna
} else {
    $userName = 'Nama Pengguna Tidak Ditemukan';  // Nilai default jika data tidak ditemukan
}

// Mengambil parameter ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;    // Mengambil dan memvalidasi ID

// Query untuk mengambil data usulan pelatihan
$sql = "SELECT * FROM usulan_pelatihan WHERE id = $id"; 
$result = $conn->query($sql);             // Eksekusi query

// Memproses data usulan pelatihan
if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();       // Mengambil data dalam bentuk array associative
    
    // Menyimpan data ke variabel-variabel
    $judulPelatihan = $data['judul_pelatihan'];
    $jenisPelatihan = $data['jenis_pelatihan'];
    $namaPeserta = explode(',', $data['nama_peserta']);    // Memecah string nama peserta menjadi array
    $lembaga = $data['lembaga'];
    $jurusan = $data['jurusan'];
    $programStudi = $data['program_studi'];
    $tanggalMulai = $data['tanggal_mulai'];
    $tanggalSelesai = $data['tanggal_selesai'];
    $tempat = $data['tempat'];
    $sumberDana = $data['sumber_dana'];
    $namaManajer = $data['manajer_pembimbing'];
    $target = $data['target'];
} else {
    // Inisialisasi variabel dengan nilai kosong jika data tidak ditemukan
    $judulPelatihan = $jenisPelatihan = $namaPeserta = $lembaga = $jurusan = $programStudi = 
    $tanggalMulai = $tanggalSelesai = $tempat = $sumberDana = $namaManajer = $target = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">                                                         <!-- Pengaturan karakter encoding UTF-8 untuk mendukung berbagai karakter -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">        <!-- Pengaturan viewport untuk tampilan responsif di berbagai perangkat -->
    <link rel="stylesheet" href="/assets/css/style.css">                          <!-- Menghubungkan dengan file CSS utama untuk styling -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap">    <!-- Import font Poppins dari Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">      <!-- Import icon Font Awesome -->
    <title>Detail Usulan Pelatihan</title>                                        <!-- Judul halaman yang akan ditampilkan di tab browser -->
    <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">    <!-- Icon kecil yang muncul di tab browser -->
    <style>
        textarea {
            resize: vertical;     /* Mengatur agar textarea hanya bisa diresize secara vertikal */
            height: 100px;        /* Mengatur tinggi default textarea */
            overflow-y: auto;     /* Menambahkan scrollbar vertikal jika konten melebihi tinggi */
        }
    </style>
</head>

<body>
    <!-- Bagian Navigation Bar -->
    <nav class="navbar">
        <img class="logo" src="/assets/images/Logo_Ajukan.png" alt="Ajukan" style="width: fit-content;">    <!-- Logo aplikasi dengan ukuran menyesuaikan -->
        <div class="profile">
            <img class="profile-image" src="https://web.rupa.ai/wp-content/uploads/2023/08/aruna3619_Elegant_black_and_white_profesional_photo_of_a_young__46c0ef4a-ba0a-4e23-af98-c824d9b7127d.png" alt="Profile Image">    <!-- Gambar profil pengguna -->
            <p class="profile-name"><?php echo $user['nama']; ?></p>    <!-- Menampilkan nama pengguna dari database -->
        </div>
    </nav>

    <!-- Bagian Toggle Sidebar -->
    <input type="checkbox" id="toggle">                    <!-- Checkbox untuk mengontrol tampilan sidebar -->
    <label for="toggle" class="side-toggle">&#9776;</label>    <!-- Tombol hamburger untuk toggle sidebar -->
    <span class="fas fa-bars"></span>
    </label>

    <!-- Bagian Sidebar -->
    <div class="sidebar">
        <!-- Menu Dashboard dengan icon rumah -->
        <div class="sidebar-menu">
            <a href="/views/dashboard/karyawan/dashboard_karyawan.php" class="switch-button active">
                <span class="fas fa-home"></span>          <!-- Icon rumah untuk menu dashboard -->
                <span class="label"> Dashboard</span>      <!-- Label menu dashboard -->
            </a>
        </div>
        
        <!-- Menu Identitas dengan icon user -->
        <div class="sidebar-menu">
            <a href="/views/user/identitas.php" class="switch-button">
                <span class="fas fa-user"></span>          <!-- Icon user untuk menu identitas -->
                <span class="label"> Identitas</span>      <!-- Label menu identitas -->
            </a>
        </div>

        <!-- Menu History dengan icon jam -->
        <div class="sidebar-menu">
            <a href="/views/user/history.php" class="switch-button">
                <span class="fas fa-clock"></span>         <!-- Icon jam untuk menu history -->
                <span class="label"> History</span>        <!-- Label menu history -->
            </a>
        </div>

        <br>
        <br>
        <!-- Menu Logout dengan icon keluar -->
        <div class="sidebar-menu">
            <a href="/views/auth/logout.php" class="switch-button">
                <span class="fas fa-sign-out-alt"></span>  <!-- Icon keluar untuk menu logout -->
                <span class="label"> Logout</span>         <!-- Label menu logout -->
            </a>
        </div>
    </div>

    <!-- Bagian Konten Utama -->
    <main class="main">
        <div class="section-title">Detail Usulan Pelatihan</div>    <!-- Judul halaman detail usulan -->
        <div class="form-container">
            <form id="trainingForm" method="POST" action="/views/user/submit_usulan.php">    <!-- Form untuk menampilkan detail usulan -->
                
                <!-- Input Judul Pelatihan -->
                <div class="form-group">
                    <label>Judul Pelatihan</label>
                    <input type="text" class="form-control" name="judulPelatihan" value="<?php echo $judulPelatihan; ?>" disabled>    <!-- Field judul pelatihan yang tidak bisa diedit -->
                </div>

                <!-- Input Jenis Pelatihan -->
                <div class="form-group">
                    <label>Jenis Pelatihan</label>
                    <input type="text" class="form-control" name="jenisPelatihan" value="<?php echo $jenisPelatihan; ?>" disabled>    <!-- Field jenis pelatihan yang tidak bisa diedit -->
                </div>

                <!-- Input Nama Peserta -->
                <div class="form-group">
                    <label>Nama Peserta</label>
                    <input type="text" class="form-control" name="namaPeserta" value="<?php echo implode(', ', array_map('trim', $namaPeserta)); ?>" disabled>    <!-- Field nama peserta yang digabung dengan koma -->
                </div>

                <!-- Input Lembaga -->
                <div class="form-group">
                    <label>Lembaga / Institusi</label>
                    <input type="text" class="form-control" name="lembaga" value="<?php echo $lembaga; ?>" disabled>    <!-- Field lembaga yang tidak bisa diedit -->
                </div>

                <!-- Input Jurusan dengan konversi kode -->
                <div class="form-group">
                    <label>Jurusan</label>
                    <input type="text" class="form-control" name="jurusan" value="<?php 
                        // Konversi kode jurusan ke nama lengkap
                        switch ($jurusan) {
                            case 'TI':
                                echo 'Teknik Informatika';
                                break;
                            case 'TM':
                                echo 'Teknik Mesin';
                                break;
                            case 'TE':
                                echo 'Teknik Elektro';
                                break;
                            case 'MB':
                                echo 'Manajemen Bisnis';
                                break;
                            default:
                                echo 'Jurusan Tidak Diketahui';
                        } 
                    ?>" disabled>    <!-- Field jurusan dengan konversi kode ke nama lengkap -->
                </div>

                <!-- Input Program Studi -->
                <div class="form-group">
                    <label>Program Studi</label>
                    <input type="text" class="form-control" name="programStudi" value="<?php echo $programStudi; ?>" disabled>    <!-- Field program studi yang tidak bisa diedit -->
                </div>

                <!-- Input Tanggal Mulai dan Selesai -->
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Tanggal Mulai</label>
                        <input type="text" class="form-control" name="tanggalMulai" value="<?php echo $tanggalMulai; ?>" disabled>    <!-- Field tanggal mulai -->
                    </div>
                    <div class="form-group col-md-6">
                        <label>Tanggal Selesai</label>
                        <input type="text" class="form-control" name="tanggalSelesai" value="<?php echo $tanggalSelesai; ?>" disabled>    <!-- Field tanggal selesai -->
                    </div>
                </div>

                <!-- Input Tempat/Alamat -->
                <div class="form-group">
                    <label>Tempat / Alamat</label>
                    <textarea class="form-control" name="tempat" disabled><?php echo $tempat; ?></textarea>    <!-- Field tempat dalam bentuk textarea -->
                </div>

                <!-- Input Sumber Dana -->
                <div class="form-group">
                    <label>Sumber Dana (Kode Account)</label>
                    <input type="text" class="form-control" name="sumberDana" value="<?php echo $sumberDana; ?>" disabled>    <!-- Field sumber dana -->
                </div>

                <!-- Input Nama Manajer -->
                <div class="form-group">
                    <label>Manajer</label>
                    <input type="text" class="form-control" name="namaManajer" value="<?php echo $namaManajer; ?>" disabled>    <!-- Field nama manajer -->
                </div>

                <!-- Input Target -->
                <div class="form-group">
                    <label>Target</label>
                    <textarea class="form-control" name="target" disabled><?php echo $target; ?></textarea>    <!-- Field target dalam bentuk textarea -->
                </div>

                <!-- Tombol Aksi -->
                <div class="form-actions">
                    <a href="/views/dashboard/karyawan/dashboard_karyawan.php" class="training-button">Kembali</a>    <!-- Tombol untuk kembali ke halaman dashboard -->
                </div>
            </form>
        </div>
    </main>
</body>
</html>