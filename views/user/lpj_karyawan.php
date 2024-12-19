<?php
// Konfigurasi koneksi database
$host = "localhost";
$username = "root";
$password = "";
$database = "db_ajukan";

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Mulai session
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Query untuk mengambil data pengguna
$stmt = $conn->prepare("SELECT u.nama, u.role FROM user u WHERE u.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultUser = $stmt->get_result();

if ($resultUser->num_rows > 0) {
    $user = $resultUser->fetch_assoc();
    $userName = $user['nama'];
    $userRole = $user['role'];
} else {
    $userName = 'Nama Pengguna Tidak Ditemukan';
    $userRole = 'unknown';
}

// Mengambil parameter ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Query untuk mengambil data usulan pelatihan
$sql = "SELECT * FROM usulan_pelatihan WHERE id = $id"; 
$result = $conn->query($sql);

// Memproses data usulan pelatihan
if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    
    // Menyimpan data ke variabel-variabel
    $judulPelatihan = $data['judul_pelatihan'];
    $jenisPelatihan = $data['jenis_pelatihan'];
    $namaPeserta = explode(',', $data['nama_peserta']);
    $lembaga = $data['lembaga'];
    $jurusan = $data['jurusan'];
    $programStudi = $data['program_studi'];
    $tanggalMulai = $data['tanggal_mulai'];
    $tanggalSelesai = $data['tanggal_selesai'];
    $tempat = $data['tempat'];
    $sumberDana = $data['sumber_dana'];
    $namaManajer = $data['manajer_pembimbing'];
    $target = $data['target'];
    $lpjFile = $data['lpj_file'];
    $sertifikatFile = $data['sertifikat_file'];  // Mendapatkan nama file sertifikat
    $komentar = $data['komentar'];
    $lpjStatus = $data['lpj_status'];

    // Menangani form pengiriman (upload file LPJ, sertifikat dan komentar)
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if ($userRole === 'karyawan') {
            // Logika upload file LPJ
            $fileUpload = $_FILES['fileUpload'] ?? null;
            if ($fileUpload && $fileUpload['error'] === 0) {
                $fileName = uniqid('LPJ_') . '.' . pathinfo($fileUpload['name'], PATHINFO_EXTENSION);
                $fileDestination = 'uploads/' . $fileName;

                if (move_uploaded_file($fileUpload['tmp_name'], $fileDestination)) {
                    // Hapus file lama jika status revisi
                    if ($lpjStatus === 'Revisi' && !empty($lpjFile)) {
                        $oldFile = 'uploads/' . $lpjFile;
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }

                    // Update data LPJ di database
                    $sqlUpdate = "
                        UPDATE usulan_pelatihan SET 
                            lpj_file = '$fileName',
                            lpj_status = 'On Progress',
                            lpj_submitted = 1,
                            tanggal_upload = NOW()
                        WHERE id = $id
                    ";
                    if ($conn->query($sqlUpdate)) {
                        echo "<script>
                            alert('File LPJ berhasil diupload!');
                            window.location.href = '/views/dashboard/karyawan/dashboard_karyawan.php';
                        </script>";
                    } else {
                        echo "Error: " . $conn->error;
                    }
                } else {
                    echo "Gagal mengupload file LPJ.";
                }
            } else {
                echo "Tidak ada file LPJ yang diupload atau terjadi kesalahan.";
            }

            // Logika upload file Sertifikat
            $sertifikatUpload = $_FILES['sertifikatUpload'] ?? null;
            if ($sertifikatUpload && $sertifikatUpload['error'] === 0) {
                $sertifikatName = uniqid('Sertifikat_') . '.' . pathinfo($sertifikatUpload['name'], PATHINFO_EXTENSION);
                $sertifikatDestination = 'uploads/' . $sertifikatName;

                if (move_uploaded_file($sertifikatUpload['tmp_name'], $sertifikatDestination)) {
                    // Hapus file lama sertifikat jika ada revisi
                    if (!empty($sertifikatFile)) {
                        $oldSertifikatFile = 'uploads/' . $sertifikatFile;
                        if (file_exists($oldSertifikatFile)) {
                            unlink($oldSertifikatFile);
                        }
                    }

                    // Update data sertifikat di database
                    $sqlUpdateSertifikat = "
                        UPDATE usulan_pelatihan SET 
                            sertifikat_file = '$sertifikatName'
                        WHERE id = $id
                    ";
                    if ($conn->query($sqlUpdateSertifikat)) {
                        echo "<script>
                            alert('File Sertifikat berhasil diupload!');
                            window.location.href = '/views/dashboard/karyawan/dashboard_karyawan.php';
                        </script>";
                    } else {
                        echo "Error: " . $conn->error;
                    }
                } else {
                    echo "Gagal mengupload file Sertifikat.";
                }
            }
        } elseif ($userRole === 'manajer') {
            // Logika manajer menambah komentar dan memperbarui status
            $komentar = $_POST['komentar'] ?? '';
            $lpjStatus = $_POST['lpj_status'] ?? '';
            
            $sqlUpdate = "
                UPDATE usulan_pelatihan SET 
                    komentar = '$komentar', 
                    lpj_status = '$lpjStatus' 
                WHERE id = $id
            ";
            if ($conn->query($sqlUpdate)) {
                echo "<script>
                    alert('Komentar dan status berhasil diperbarui.');
                    window.location.href = '/views/dashboard/manajer/dashboard_manajer.php';
                </script>";
            } else {
                echo "Error: " . $conn->error;
            }
        }
    }
} else {
    echo "Data usulan pelatihan tidak ditemukan.";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">                                                         <!-- Pengaturan karakter encoding UTF-8 untuk mendukung berbagai karakter -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">        <!-- Pengaturan viewport untuk tampilan responsif di berbagai perangkat -->
    <link rel="stylesheet" href="/assets/css/style.css">                          <!-- Menghubungkan dengan file CSS utama untuk styling -->
    <link rel="stylesheet" href="https://fonts.googleapis .com/css?family=Poppins:400,500,600,700&display=swap">    <!-- Import font Poppins dari Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">      <!-- Import icon Font Awesome -->
    <title>Detail LPJ</title>                                        <!-- Judul halaman yang akan ditampilkan di tab browser -->
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
            <form id="trainingForm" method="POST" action="" enctype="multipart/form-data">    <!-- Form untuk menampilkan detail usulan -->
                
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
                    <input type="text" class="form-control" name="jurusan" 
                        value="<?php 
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

                <!-- Input untuk Upload File (hanya untuk karyawan) -->
                <?php if ($userRole === 'karyawan') { ?>
                    <!-- Form upload file baru -->
                    <div class="form-group">
                        <label>Upload File LPJ <?php echo ($lpjStatus === 'Revisi') ? '(Revisi)' : ''; ?></label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="file" class="form-control" name="fileUpload" accept=".pdf" <?php echo ($lpjStatus === 'Disetujui') ? 'disabled' : 'required'; ?>>
                            <small class="form-text text-muted" style="margin: 0;">Format yang didukung: PDF (Maks. 5MB)</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Upload Sertifikat <?php echo ($lpjStatus === 'Revisi') ? '(Revisi)' : ''; ?></label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="file" class="form-control" name="sertifikatUpload" <?php echo ($lpjStatus === 'Disetujui') ? 'disabled' : ''; ?>>
                            <small class="form-text text-muted" style="margin: 0;">Format yang didukung: PDF, JPG, PNG (Maks. 5MB)</small>
                        </div>
                    </div>

                    <!-- Menampilkan komentar manajer dalam mode read-only untuk karyawan -->
                    <?php if (!empty($komentar)) { ?>
                        <div class="form-group">
                            <label>Komentar Manajer</label>
                            <textarea class="form-control" rows="4" disabled><?php echo $komentar; ?></textarea>
                        </div>
                    <?php } ?>

                    <!-- Bagian untuk menampilkan dan download file -->
                    <div class="form-group">
                        <label>File LPJ Saat Ini:</label>
                        <?php if (!empty($lpjFile)) { ?>
                            <a href="uploads/<?php echo htmlspecialchars($lpjFile); ?>" class="downloadButton" download>Download LPJ</a>
                        <?php } else { ?>
                            <span>Belum ada file LPJ yang diupload.</span>
                        <?php } ?>
                    </div>

                    <div class="form-group">
                        <label>File Sertifikat Saat Ini:</label>
                        <?php if (!empty($data['sertifikat_file'])) { ?>
                            <a href="uploads/sertifikat/<?php echo htmlspecialchars($data['sertifikat_file']); ?>" class="downloadButton" download>Download Sertifikat</a>
                        <?php } else { ?>
                            <span>Belum ada file sertifikat yang diupload.</span>
                        <?php } ?>
                    </div>
                <?php } ?>

                <!-- Input untuk Komentar Manajer (hanya untuk manajer) -->
                <?php if ($userRole === 'manajer') { ?>
                    <div class="form-group">
                        <label>Komentar Manajer</label>
                        <textarea class="form-control" name="komentar" placeholder="Komentar dari manajer" rows="4" <?php echo ($lpjStatus === 'Disetujui') ? 'disabled' : ''; ?>><?php echo $komentar; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status LPJ</label>
                        <select name="lpj_status" class="form-control" <?php echo ($lpjStatus === 'Disetujui') ? 'disabled' : ''; ?>>
                            <option value="Disetujui" <?php echo ($lpjStatus === 'Disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                            <option value="Revisi" <?php echo ($lpjStatus === 'Revisi') ? 'selected' : ''; ?>>Revisi</option>
                        </select>
                    </div>
                <?php } ?>


                <!-- Tombol Aksi -->
                <div class="form-actions">
                    <button type="submit" class="training-button" <?php echo ($lpjStatus === 'Disetujui') ? 'disabled' : ''; ?>>Kirim</button>    <!-- Tombol untuk mengirim form -->
                    <a href="/views/dashboard/karyawan/dashboard_karyawan.php" class="training-button">Kembali</a>    <!-- Tombol untuk kembali ke halaman dashboard -->
                </div>
            </form>
        </div>
    </main>
</body>
</html>