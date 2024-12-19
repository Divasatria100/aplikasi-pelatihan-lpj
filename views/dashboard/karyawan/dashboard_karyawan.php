<?php 
session_start();

// Memastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: /views/auth/login.php");
    exit();
}

// Mengecek role pengguna
if ($_SESSION['session_role'] !== 'karyawan') {
    header("Location: /views/dashboard/manajer/dashboard_manajer.php");
    exit();
}

// Mengambil user_id dari session
$user_id = $_SESSION['user_id'] ?? null;

// Mengecek apakah user_id tersedia
if ($user_id === null) {
    echo "User ID tidak ditemukan. Silakan login ulang.";
    exit();
}

// Koneksi database
$host = "localhost";
$username = "root";
$password = "";
$database = "db_ajukan";
$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Query untuk mengambil data pengguna berdasarkan user_id
$query = "SELECT user_id, nik, nama, email, tempat_lahir, tanggal_lahir, alamat
        FROM user 
        WHERE user_id = '$user_id'";
$result = mysqli_query($koneksi, $query);
$user = mysqli_fetch_assoc($result);

// Memeriksa apakah data pengguna ditemukan
if (!$user) {
    echo "Data pengguna tidak ditemukan.";
    exit();
}

// Query untuk mengambil data usulan pelatihan berdasarkan user_id
$query_usulan = "SELECT * FROM usulan_pelatihan WHERE user_id = '$user_id'"; // Memastikan hanya data milik karyawan yang ditampilkan
$result_usulan = mysqli_query($koneksi, $query_usulan);

if (!$result_usulan) {
    die("Query gagal dijalankan: " . mysqli_error($koneksi));
}

// Cek jika login berhasil
if (isset($_SESSION['login_success']) && $_SESSION['login_success'] == true) {
    echo "<script>alert('Login berhasil!');</script>";
    unset($_SESSION['login_success']);  // Menghapus session login_success setelah notifikasi ditampilkan
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- External CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- JavaScript -->
    <script src="/assets/js/script.js"></script>
    <!-- DataTables -->
    <link rel="stylesheet" href="/lib/datatables/dataTables.css">
    <!-- Page Title -->
    <title>Dashboard</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">
    <!-- DataTables CSS -->
    <style>
        /* Center text in the header cells */
        #myTable th {
            text-align: center;
        }

        /* Center text in the table body cells */
        #myTable td {
            text-align: center;
            vertical-align: middle; /* Agar teks berada di tengah secara vertikal */
        }

        /* Optional: Style untuk tombol link */
        .edit-btn, .detailButton {
            display: inline-block;
            padding: 5px 10px;
            text-align: center;
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

    <!-- Toggle untuk sidebar mobile -->
    <input type="checkbox" id="toggle">    
    <label for="toggle" class="side-toggle">&#9776;</label>    

    <!-- Sidebar dengan menu navigasi -->
    <div class="sidebar">
        <div class="sidebar-menu">
            <a href="/views/dashboard/karyawan/dashboard_karyawan.php" class="switch-button active">    
                <span class="fas fa-home"></span>    
                <span class="label"> Dashboard</span> 
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
                <span class="fas fa-clock"></span>    
                <span class="label"> History</span>    
            </a>
        </div>
        <br>
        <br>
        <div class="sidebar-menu">
            <a href="/views/auth/logout.php" class="switch-button">
                <span class="fas fa-sign-out-alt"></span>
                <span class="label"> Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <main>
        <div class="banner-card"></div>
        <div class="dashboard-container1">
            <!-- Training Proposal Button Card -->
            <div class="card totall">
                <div class="info">
                    <div class="info-detail">
                        <a href="/views/user/form_karyawan.php">
                            <button class="training-button">
                                <span class="plus-icon">+</span> Ajukan Usulan Pelatihan
                            </button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <!-- Training Table Card -->
        <div class="card totall">
            <div class="info">
                <div class="info-detail"></div>
            </div>
            <div class="card detail">
                <div class="detail-header">
                    <h2>Tabel Pelatihan</h2>
                </div>
                <!-- Training Data Table -->
                <table id="myTable" class="display">
                    <thead>
                        <tr>
                            <th>ID</th> <!-- Kolom ID pelatihan -->
                            <th>Judul Pelatihan</th> <!-- Kolom judul pelatihan -->
                            <th>Jenis Kegiatan</th> <!-- Kolom jenis kegiatan pelatihan -->
                            <th>Nama Peserta</th> <!-- Kolom nama peserta -->
                            <th>Tanggal Mulai</th> <!-- Kolom tanggal mulai pelatihan -->
                            <th>Pengajuan</th> <!-- Kolom status pengajuan -->
                            <th>Status LPJ</th> <!-- Kolom status persetujuan LPJ -->
                            <th>LPJ</th> <!-- Kolom status LPJ -->
                            <th>Aksi</th> <!-- Kolom tombol aksi -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result_usulan)) { 
                            $statusClass = '';
                            switch($row['status']) {
                                case 'Disetujui':
                                    $statusClass = 'disetujui';
                                    break;
                                case 'Ditolak':
                                    $statusClass = 'ditolak';
                                    break;
                                default:
                                    $statusClass = 'menunggu';
                            }
                        ?>    
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td> <!-- Menampilkan ID dengan pengamanan XSS -->
                                <td><?= htmlspecialchars($row['judul_pelatihan']) ?></td> <!-- Menampilkan judul pelatihan -->
                                <td><?= htmlspecialchars($row['jenis_pelatihan']) ?></td> <!-- Menampilkan jenis pelatihan -->
                                <td><?= htmlspecialchars($row['nama_peserta']) ?></td> <!-- Menampilkan nama peserta -->
                                <td><?= htmlspecialchars($row['tanggal_mulai']) ?></td> <!-- Menampilkan tanggal mulai -->
                                <td>
                                <span class="status <?= $statusClass ?>">
                                        <i class="fas fa-circle"></i> <?= htmlspecialchars($row['status']) ?> <!-- Menampilkan status pengajuan dengan ikon -->
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'Disetujui' && !$row['lpj_submitted']) { ?>
                                        <span class="status on-progress"><i class="fas fa-spinner"></i> On Progress</span> <!-- Status progress LPJ -->
                                    <?php } else { ?>
                                        <span class="status <?= strtolower(str_replace(' ', '', htmlspecialchars($row['lpj_status']))) ?>">
                                            <i class="fas fa-circle"></i> <?= htmlspecialchars($row['lpj_status']) ?> <!-- Status persetujuan LPJ -->
                                        </span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($row['status'] === 'Disetujui') {
                                        if ($row['lpj_status'] === 'Disetujui') {
                                            echo '<a href="/views/user/lpj_karyawan.php?id=' . urlencode($row['id']) . '" class="detailButton">Detail</a>';
                                        } elseif ($row['lpj_status'] === 'Revisi') {
                                            echo '<a href="/views/user/lpj_karyawan.php?id=' . urlencode($row['id']) . '" class="detailButton">Ajukan LPJ</a>';
                                        } elseif ($row['lpj_submitted']) {
                                            echo '<span class="status completed"><i class="fas fa-check-circle"></i> Diajukan</span>';
                                        } else {
                                            echo '<a href="/views/user/lpj_karyawan.php?id=' . urlencode($row['id']) . '" class="detailButton">Ajukan LPJ</a>';
                                        }
                                    } else {
                                        echo '<span class="status pending"><i class="fas fa-clock"></i> Waiting</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="/views/user/dp_karyawan.php?id=<?= urlencode($row['id']) ?>" class="detailButton">Detail</a> <!-- Tombol untuk melihat detail -->
                                </td> 
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script src="/lib/jquery/jquery-3.7.1.js"></script>
    <script src="/lib/datatables/dataTables.js"></script>
    <script>
            $(document).ready( function () {
                $('#myTable').DataTable();
            } );
    </script>
</body>
</html>
