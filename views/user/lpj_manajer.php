<?php
// Konfigurasi koneksi database
$host = "localhost";          // Alamat host database
$username = "root";           // Username untuk akses database
$password = "";               // Password untuk akses database
$database = "db_ajukan";      // Nama database

// Membuat koneksi baru ke database MySQL menggunakan mysqli
$conn = new mysqli($host, $username, $password, $database);

// Memeriksa apakah koneksi berhasil
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Manajemen Session
session_start();              // Memulai session PHP

// Validasi keberadaan session user_id
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");    // Redirect ke halaman login jika belum login
    exit();
}

// Mengambil ID user dari session
$user_id = $_SESSION['user_id'];

// Query untuk mengambil data nama pengguna
$sqlUser = "SELECT nama, role FROM user WHERE user_id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();

// Validasi dan pengambilan data pengguna
if ($resultUser->num_rows > 0) {
    $user = $resultUser->fetch_assoc();    // Mengambil data dalam bentuk array associative
    $userName = $user['nama'];             // Menyimpan nama pengguna
    $userRole = $user['role'];             // Menyimpan role pengguna
} else {
    echo "Pengguna tidak ditemukan.";
    exit();
}

// Mengambil parameter ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;    // Mengambil dan memvalidasi ID

// Query untuk mengambil data usulan pelatihan
$sql = "SELECT * FROM usulan_pelatihan WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Memproses data usulan pelatihan
if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();       // Mengambil data dalam bentuk array associative
    
    // Menyimpan data ke variabel-variabel
    $judulPelatihan = $data['judul_pelatihan'];
    $lpjFile = $data['lpj_file'];
    $sertifikatFile = $data['sertifikat_file'];
    $komentar = $data['komentar'];
    $lpjStatus = $data['lpj_status']; // Status LPJ saat ini
    $lpjSubmitted = $data['lpj_submitted']; // Menambahkan kolom lpj_submitted

    // Memproses komentar dari manajer
    if ($_SERVER["REQUEST_METHOD"] == "POST" && $userRole === 'manajer') {
        $komentar = isset($_POST['komentar']) ? $conn->real_escape_string($_POST['komentar']) : '';
        $lpjStatus = isset($_POST['lpj_status']) ? $conn->real_escape_string($_POST['lpj_status']) : '';

        // Hanya memproses jika status belum disetujui
        if ($data['lpj_status'] !== 'Disetujui') {
            // Query SQL untuk memperbarui komentar dan status LPJ
            $sqlUpdate = "UPDATE usulan_pelatihan SET komentar = ?, lpj_status = ? WHERE id = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("ssi", $komentar, $lpjStatus, $id);

            // Mengeksekusi query dan menangani hasilnya
            if ($stmtUpdate->execute()) {
                echo "<script>
                    alert('Komentar dan status LPJ berhasil disimpan!'); 
                    window.location.href = '/views/dashboard/manajer/dashboard_manajer.php'; 
                </script>";
            } else {
                error_log("Error: " . $stmtUpdate->error); // Mencatat error ke log sistem
                echo "Error: " . $stmtUpdate->error; // Menampilkan pesan error ke user
            }
        }
    }
} else {
    echo "Data tidak ditemukan."; // Menampilkan pesan jika tidak ada data
    exit();
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
    <!-- Navigation Bar dengan logo dan profil -->
    <nav class="navbar">
        <img class="logo" src="/assets/images/Logo_Ajukan.png" alt="Ajukan" style="width: fit-content;">    
        <div class="profile">
            <img class="profile-image" src="https://web.rupa.ai/wp-content/uploads/2023/08/aruna3619_Elegant_black_and_white_profesional_photo_of_a_young__46c0ef4a-ba0a-4e23-af98-c824d9b7127d.png" alt="Profile Image">    
            <p class="profile-name"><?php echo $user['nama']; ?></p>    
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
            <a href="/views/dashboard/manajer/dashboard_manajer.php" class="switch-button active">
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
        <div class="section-title">Detail Laporan Pertanggung Jawaban</div>    <!-- Judul halaman detail usulan -->
        <div class="form-container">
            <form id="trainingForm" method="POST" action="">    <!-- Form untuk menampilkan detail usulan -->
                
                <!-- Input Judul Pelatihan -->
                <div class="form-group">
                    <label>Judul Pelatihan</label>
                    <input type="text" class="form-control" name="judulPelatihan" value="<?php echo $judulPelatihan; ?>" disabled>    <!-- Field judul pelatihan yang tidak bisa diedit -->
                </div>

                <!-- Input untuk menampilkan link download file LPJ -->
                <div class="form-group">
                    <label>File LPJ:</label>
                    <?php if (!empty($lpjFile)) { ?>
                        <a href="uploads/<?php echo htmlspecialchars($lpjFile); ?>" class="downloadButton" download>Download LPJ</a> <!-- Link untuk download file -->
                    <?php } else { ?>
                        <span>Tidak ada file LPJ yang diupload.</span> <!-- Pesan jika tidak ada file -->
                    <?php } ?>
                </div>

                <!-- Input untuk menampilkan link download file Sertifikat -->
                <div class="form-group">
                    <label>File Sertifikat:</label>
                    <?php if (!empty($sertifikatFile)) { ?>
                        <a href="uploads/sertifikat/<?php echo htmlspecialchars($sertifikatFile); ?>" class="downloadButton" download>Download Sertifikat</a>
                    <?php } else { ?>
                        <span>Tidak ada file sertifikat yang diupload.</span>
                    <?php } ?>
                </div>

                <!-- Input untuk Komentar Manajer -->
                <div class="form-group">
                    <label>Komentar Manajer</label>
                    <textarea class="form-control" name="komentar" placeholder="Komentar dari manajer" rows="4" <?php echo ($lpjStatus === 'Disetujui') ? 'disabled' : ''; ?>><?php echo $komentar; ?></textarea>
                </div>
                
                <!-- Input untuk Status LPJ -->
                <?php if ($lpjStatus !== 'Disetujui') { ?>
                    <div class="form-group">
                        <label>Status LPJ</label>
                        <select name="lpj_status" class="form-control">
                            <option value="" disabled selected>Pilih Status</option>
                            <option value="Disetujui">Disetujui</option>
                            <option value="Revisi">Revisi</option>
                        </select>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="form-actions">
                        <button type="submit" class="training-button">Kirim</button>
                        <a href="/views/dashboard/manajer/dashboard_manajer.php" class="training-button">Kembali</a>
                    </div>
                <?php } else { ?>
                    <div class="form-group">
                        <label>Status LPJ</label>
                        <input type="text" class="form-control" value="Disetujui" disabled>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="form-actions">
                        <a href="/views/dashboard/manajer/dashboard_manajer.php" class="training-button">Kembali</a>
                    </div>
                <?php } ?>
            </form>
        </div>
    </main>
</body>
</html>
<?php
// Tambahan untuk menutup koneksi database
$conn->close();
?>