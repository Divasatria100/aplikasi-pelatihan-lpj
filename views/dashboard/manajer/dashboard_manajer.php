<?php 
session_start(); // Memulai session untuk manajemen login

// Memastikan pengguna sudah login dengan mengecek session nik
if (!isset($_SESSION['user_id'])) {
    header("Location: /views/auth/login.php"); // Redirect ke halaman login jika belum login
    exit();
}

// Mengecek role pengguna apakah manajer
if ($_SESSION['session_role'] !== 'manajer') {
    header("Location: /views/dashboard/karyawan/dashboard_karyawan.php"); // Redirect ke dashboard karyawan jika bukan manajer
    exit();
}

// Mengambil user_id dari session untuk identifikasi user
$user_id = $_SESSION['user_id'] ?? null;

// Mengecek apakah user_id tersedia dalam session
if ($user_id === null) {
    echo "User ID tidak ditemukan. Silakan login ulang.";
    exit();
}

// Konfigurasi koneksi database
$host = "localhost";      // Hostname database
$username = "root";       // Username database
$password = "";           // Password database 
$database = "db_ajukan";  // Nama database
$koneksi = mysqli_connect($host, $username, $password, $database);

// Mengecek koneksi database berhasil
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

// Query untuk mengambil data usulan pelatihan yang ditujukan ke manajer ini
$query_usulan = "SELECT up.*, u.nama AS nama_peserta 
                 FROM usulan_pelatihan up 
                 JOIN user u ON up.user_id = u.user_id 
                 WHERE up.manajer_pembimbing = '$user_id'";
$result_usulan = mysqli_query($koneksi, $query_usulan);

// Mengecek query berhasil dijalankan
if (!$result_usulan) {
    die("Query gagal dijalankan: " . mysqli_error($koneksi));
}

mysqli_close($koneksi); // Menutup koneksi database
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">                                                                             
    <meta name="viewport" content="width=device-width, initial-scale=1.0">                            
    <link rel="stylesheet" href="/assets/css/style.css">                                             
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">    
    <script src="/assets/js/script.js"></script>                                                     
    <link rel="stylesheet" href="/lib/datatables/dataTables.css">                                    
    <title>Dashboard Manajer</title>                                                                 
    <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">             
    <meta name="msapplication-TileColor" content="#ffffff">                                          
    <meta name="theme-color" content="#ffffff">                                                      
    
    <style>
        #myTable th {
            text-align: center;                
        }

        #myTable td {
            text-align: center;                
            vertical-align: middle;            
        }

        .edit-btn, .detailButton {
            display: inline-block;             
            padding: 5px 10px;                 
            text-align: center;                
        }

        .edit-btn, .detailButton, .lpjButton {
            display: inline-block;             
            padding: 5px 10px; text-align: center;                
            color: #fff;                       
            background-color: #007bff;         
            border: none;                      
            border-radius: 4px;                
            text-decoration: none;             
            transition: background-color 0.3s;  
        }

        .edit-btn:hover, .detailButton:hover, .lpjButton:hover {
            background-color: #0056b3;         
        }

    </style>
</head>

<body>
    <?php 
    // Menampilkan notifikasi login berhasil
    if (isset($_SESSION['login_success']) && $_SESSION['login_success'] == true) {
        echo '<div class="login-notification">
                <i class="fas fa-check-circle"></i> Login berhasil! Selamat datang ' . $user['nama'] . '
              </div>';
        unset($_SESSION['login_success']); // Hapus session setelah ditampilkan
    }
    ?>
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
            <a href="/views/dashboard/manajer/dashboard_manajer.php" class="switch-button active">    
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

    <!-- Konten utama -->
    <main>
        <div class="banner-card"></div>    
        <div class="dashboard-container1">
        <br>
        <div class="card detail">
            <div class="detail-header">
                <h2>Tabel Usulan Pelatihan</h2>    
            </div>
            <table id="myTable" class="display">
                <thead>
                    <tr>
                        <th>ID</th>                    
                        <th>Judul Pelatihan</th>       
                        <th>Jenis Kegiatan</th>        
                        <th>Nama Karyawan</th>          
                        <th>Tanggal Mulai</th>         
                        <th>Pengajuan</th> 
                        <th>Status LPJ</th>          
                        <th>LPJ</th>                   
                        <th>Aksi</th>                              
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
                            <td><?= htmlspecialchars($row['id']) ?></td>    
                            <td><?= htmlspecialchars($row['judul_pelatihan']) ?></td>    
                            <td><?= htmlspecialchars($row['jenis_pelatihan']) ?></td>    
                            <td><?= htmlspecialchars($row['nama_peserta']) ?></td>    
                            <td><?= htmlspecialchars($row['tanggal_mulai']) ?></td>    
                            <td>
                                <span class="status <?= $statusClass ?>">
                                    <i class="fas fa-circle"></i> <?= htmlspecialchars($row['status']) ?>    
                                </span>
                            </td>
                            <!-- Status LPJ -->
                            <td>
                                <?php if ($row['status'] === 'Disetujui' && !$row['lpj_submitted']) { ?>
                                    <span class="status waiting"><i class="fas fa-clock"></i> Waiting</span>
                                <?php } elseif ($row['lpj_status'] === 'On Progress') { ?>
                                    <span class="status on-progress"><i class="fas fa-spinner"></i> On Progress</span>
                                <?php } elseif ($row['lpj_status'] === 'Disetujui') { ?>
                                    <span class="status disetujui"><i class="fas fa-check-circle"></i> Disetujui</span>
                                <?php } elseif ($row['lpj_status'] === 'Revisi') { ?>
                                    <span class="status revisi"><i class="fas fa-times-circle"></i> Revisi</span>
                                <?php } else { ?>
                                    <span class="status pending"><i class="fas fa-clock"></i> Belum Diajukan</span>
                                <?php } ?>
                            </td>
                            <!-- Kolom LPJ -->
                            <td>
                                <?php if ($row['status'] === 'Disetujui') { 
                                    if ($row['lpj_submitted'] && $row['lpj_status'] === 'On Progress') { ?>
                                        <a href="/views/user/lpj_manajer.php?id=<?= urlencode($row['id']) ?>" class="lpjButton">Review</a>
                                    <?php } elseif ($row['lpj_status'] === 'Disetujui') { ?>
                                        <a href="/views/user/lpj_manajer.php?id=<?= urlencode($row['id']) ?>" class="detailButton">Detail</a>
                                    <?php } elseif ($row['lpj_status'] === 'Revisi') { ?>
                                        <span class="status revisi"><i class="fas fa-times-circle"></i> Revisi</span>
                                    <?php } else { ?>
                                        <span class="status waiting"><i class="fas fa-clock"></i> Waiting</span>
                                    <?php }
                                } else { ?>
                                    <span class="status pending"><i class="fas fa-clock"></i> Belum Diajukan</span>
                                <?php } ?>
                            </td>
                            <td>
                                <a href="/views/user/dp_manajer.php?id=<?= urlencode($row['id']) ?>" class="detailButton">Detail</a>    
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="/lib/jquery/jquery-3.7.1.js"></script>                           
    <script src="/lib/datatables/dataTables.js"></script>                         

    <script>
    $(document).ready(function () {
        $('#myTable').DataTable();    
    });
    </script>
</body>
</html>