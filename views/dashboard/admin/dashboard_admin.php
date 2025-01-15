<?php 
// Memulai session PHP untuk manajemen sesi pengguna
session_start();

// Memeriksa apakah pengguna sudah login dan memiliki role admin
// Jika tidak, redirect ke halaman login
if (!isset($_SESSION['user_id']) || $_SESSION['session_role'] != 'admin') {
    header("Location: /views/auth/login.php");
    exit();
}

// Mengambil ID pengguna dari session yang sedang aktif
$user_id = $_SESSION['user_id'];

// Konfigurasi koneksi database
$host = "localhost";          // Host database (biasanya localhost)
$username = "root";           // Username default XAMPP
$password = "";              // Password default XAMPP (kosong)
$database = "db_ajukan";     // Nama database yang digunakan
$koneksi = mysqli_connect($host, $username, $password, $database);

// Memeriksa koneksi database
// Jika gagal, tampilkan pesan error
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Query untuk mengambil data admin berdasarkan admin_id
$query = "SELECT admin_id, nama, email, password, nomor_telepon 
        FROM admin 
        WHERE admin_id = '$user_id'";
$result = mysqli_query($koneksi, $query);

// Memeriksa apakah query berhasil dijalankan
// Jika gagal, tampilkan pesan error
if (!$result) {
    die("Query gagal dijalankan: " . mysqli_error($koneksi));
}

// Memeriksa apakah data admin ditemukan
// Jika tidak ditemukan, tampilkan pesan error
$user = mysqli_fetch_assoc($result);
if (!$user) {
    echo "Data pengguna tidak ditemukan.";
    exit();
}

// Query untuk mengambil semua data user
// Diurutkan berdasarkan user_id secara descending (terbaru dulu)
$query = "SELECT * FROM user ORDER BY user_id DESC";
$result = mysqli_query($koneksi, $query);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">                                                         <!-- Pengaturan karakter encoding UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">        <!-- Pengaturan viewport untuk responsif -->
    
    <!-- Import file CSS dan font -->
    <link rel="stylesheet" href="/assets/css/style.css">                          <!-- CSS utama -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap">    <!-- Font Poppins -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">      <!-- Font Awesome icons -->
    
    <!-- Import JavaScript -->
    <script src="/assets/js/script.js"></script>                                  <!-- JavaScript utama -->
    
    <!-- Import DataTables CSS -->
    <link rel="stylesheet" href="/lib/datatables/dataTables.css">                 <!-- CSS DataTables -->
    
    <title>Dashboard admin</title>                                                <!-- Judul halaman -->
    
    <!-- Favicon dan pengaturan tema -->
    <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">

    <!-- CSS untuk styling tabel dan komponen lainnya -->
    <style>
        /* Perataan teks di header tabel */
        #myTable th {
            text-align: center;
        }

        /* Perataan teks di sel tabel */
        #myTable td {
            text-align: center;
            vertical-align: middle;
        }

        /* Style untuk tombol */
        .edit-btn, .detailButton {
            display: inline-block;
            padding: 5px 10px;
            text-align: center;
        }

        /* Container untuk tabel */
        .table-container {
            padding: 20px;
            background: white;
            border-radius: 8px;
            margin: 20px;
        }
        
        /* Style dasar tabel */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        /* Style untuk header tabel */
        th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
        }
        
        /* Style untuk sel tabel */
        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        /* Container untuk tombol aksi */
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        /* Style untuk link tombol aksi */
        .action-buttons a {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
        }
        
        /* Warna untuk tombol edit */
        .edit-btn {
            background-color: #007bff;
        }
        
        /* Warna untuk tombol delete */
        .delete-btn {
            background-color: #dc3545;
        }
    </style>
</head>

<body>
    <?php 
    // Menampilkan notifikasi ketika login berhasil
    if (isset($_SESSION['login_success']) && $_SESSION['login_success'] == true) {
        echo '<div class="login-notification">
                <i class="fas fa-check-circle"></i> Login berhasil! Selamat datang ' . $user['nama'] . '
              </div>';
        unset($_SESSION['login_success']); // Menghapus session notifikasi
    }
    ?>
    <!-- Navbar dengan logo dan profil -->
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
        <!-- Menu Dashboard -->
        <div class="sidebar-menu">
            <a href="/views/dashboard/admin/dashboard_admin.php" class="switch-button active">
                <span class="fas fa-home"></span>
                <span class="label"> Dashboard</span>
            </a>
        </div>
        
        <!-- Menu Logout -->
        <div class="sidebar-menu">
            <a href="/views/auth/logout.php" class="switch-button">
                <span class="fas fa-sign-out-alt"></span>
                <span class="label"> Logout</span>
            </a>
        </div>
        <br>
        <br>
    </div>

    <!-- Konten Utama -->
    <main>
    <div class="banner-card"></div>
        <!-- Container untuk tombol aksi -->
        <div class="dashboard-container1">
            <div class="card totall">
                <div class="info">
                    <div class="info-detail">
    <div class="info-detail">
        <!-- Tombol untuk membuat data user baru -->
        <div class="button-container">
            <a href="/views/administrator/form_user.php">
                <button class="training-button">
                    <span class="plus-icon">+</span> Buat Data User
                </button>
            </a>
        </div>
        <!-- Tombol untuk mengelola jurusan -->
        <div class="button-container">
            <a href="/views/administrator/kelola_jurusan.php">
                <button class="training-button">
                    <span class="plus-icon">✏️</span> Kelola Jurusan
                </button>
            </a>
        </div>
    </div>
    <!-- Style untuk container tombol -->
    <style>
        .button-container {
            display: inline-block;
            margin-right: 10px;
        }
    </style>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <!-- Card untuk tabel user -->
        <div class="card totall">
            <div class="info">
                <div class="info-detail"></div>
            </div>
            <div class="card detail">
                <div class="detail-header">
                    <h2>Tabel User</h2>
                </div>
                <!-- Tabel data user -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Tempat lahir</th>
                                <th>Tanggal lahir</th>
                                <th>Alamat</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <!-- Menampilkan data user dengan escape HTML untuk keamanan -->
                                    <td><?php echo htmlspecialchars($row['nik']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['tempat_lahir']); ?></td>
                                    <td><?php echo htmlspecialchars($row['tanggal_lahir']); ?></td>
                                    <td><?php echo htmlspecialchars($row['alamat']); ?></td>
                                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                                    <td class="action-buttons">
                                        <!-- Tombol untuk edit dan hapus user -->
                                        <a href="edit_user.php?id=<?php echo $row['user_id']; ?>" class="edit-btn">Edit</a>
                                        <a href="delete_user.php?id=<?php echo $row['user_id']; ?>" class="delete-btn">Delete</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <!-- Import library JavaScript -->
    <script src="/lib/jquery/jquery-3.7.1.js"></script>
    <script src="/lib/datatables/dataTables.js"></script>
    <!-- Inisialisasi DataTables -->
    <script>
            $(document).ready( function () {
                $('#myTable').DataTable();
            } );
    </script>
</body>
</html>
