<?php
// Konfigurasi koneksi database
$host = "localhost";          // Alamat server database
$username = "root";           // Username untuk login database (default XAMPP)
$password = "";               // Password untuk login database (default XAMPP kosong)
$database = "db_ajukan";      // Nama database yang digunakan

// Membuat koneksi ke database menggunakan mysqli
$conn = new mysqli($host, $username, $password, $database);

// Memeriksa apakah koneksi berhasil
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);    // Menampilkan pesan error jika koneksi gagal
}

// Memulai session PHP untuk manajemen login
session_start();

// Memeriksa apakah user sudah login dengan mengecek session user_id
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");    // Redirect ke halaman login jika belum login
    exit();
}

// Mengambil ID user dari session untuk identifikasi user
$user_id = $_SESSION['user_id'];  // Ganti login_id dengan user_id

// Query SQL untuk mengambil data user dari database menggunakan prepared statements
$query = "
    SELECT 
        user.user_id,         -- ID unik user
        user.nik,             -- Nomor Induk Kependudukan
        user.nama,            -- Nama lengkap user
        user.email,           -- Alamat email user
        user.tempat_lahir,    -- Tempat lahir user
        user.tanggal_lahir,   -- Tanggal lahir user
        user.alamat,          -- Alamat tempat tinggal user
        user.foto_profil      -- Foto profil user
    FROM 
        user                  -- Tabel utama user
    WHERE 
        user.user_id = ?";    // Menggunakan placeholder untuk prepared statement

// Menyiapkan query
$stmt = $conn->prepare($query);

// Mengikat parameter untuk prepared statement (user_id)
$stmt->bind_param("i", $user_id);

// Menjalankan query
$stmt->execute();

// Mengambil hasil query
$result = $stmt->get_result();

// Memeriksa hasil query dan mengambil data
if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();    // Mengambil data user jika ditemukan
} else {
    echo "Data user tidak ditemukan.";
    // Membuat array kosong jika data tidak ditemukan
    $user = [
        'nik' => '', 'nama' => '', 'email' => '', 
        'tempat_lahir' => '', 'tanggal_lahir' => '', 'alamat' => '', 'foto_profil' => ''
    ];
}

// Menentukan path foto profil
$foto_profil = $user['foto_profil'] ? $user['foto_profil'] : 'https://www.shutterstock.com/image-vector/default-avatar-profile-icon-social-600nw-1906669723.jpg';
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">                                                                                     <!-- Pengaturan karakter encoding -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">                                    <!-- Pengaturan viewport untuk responsive design -->
    
    <link rel="stylesheet" href="/assets/css/style.css">                                                      <!-- Menghubungkan dengan file CSS utama -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap">  <!-- Import font Poppins dari Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">   <!-- Import Font Awesome untuk ikon -->
    
    <script src="/assets/js/script.js"></script>                                                              <!-- Menghubungkan dengan file JavaScript -->
    
    <title>Identitas</title>                                                                                  <!-- Judul halaman web -->
    
    <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">                      <!-- Favicon untuk tab browser -->
    <meta name="msapplication-TileColor" content="#ffffff">                                                   <!-- Warna tile untuk Windows -->
    <meta name="theme-color" content="#ffffff">                                                               <!-- Warna tema untuk browser mobile -->
    
<style>
    .flex-container2 {                                                                                    /* Container utama dengan flexbox */
        display: flex; 
        align-items: flex-start; 
        justify-content: space-between; 
        gap: 20px; 
    }
    .form-section2 { 
        flex: 2; 
        text-align: left; /* Menambahkan ini untuk meratakan teks ke kiri */
        background-color: #f9f9f9; 
        padding: 20px; 
        border-radius: 8px; 
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
    }                                                                           /* Bagian form mengambil 2/3 lebar */
    .photo-section {                                                                                      /* Bagian foto mengambil 1/3 lebar */
        flex: 1; 
        display: flex; 
        justify-content: center; 
        align-items: center; 
    }
    .photo-box {                                                                                          /* Kotak untuk menampilkan foto */
        width: 300px; 
        height: 300px; 
        border: 2px solid #000; 
        overflow: hidden; 
        display: flex; 
        justify-content: center; 
        align-items: center; 
        background-color: #f0f0f0; 
    }
    .photo-box img {                                                                                      /* Pengaturan gambar dalam kotak foto */
        width: 100%; 
        height: 100%; 
        object-fit: cover; 
    }
    .form-section2 table {
        width: 100%; 
        border-collapse: collapse; 
    }
    .form-section2 th, .form-section2 td {
        padding: 15px; 
        text-align: left; 
    }
    .form-section2 th {
        font-weight: bold; 
    }
    .form-section2 tr:hover {
        background-color: #f1f1f1; 
    }
    .form-section2 label {
        font-weight: bold; 
    }
    .form-section2 input {
        width: 100%; 
        padding: 8px; 
        border: none; /* Menghapus border */
        border-radius: 4px; 
        box-sizing: border-box; 
    }
    .title {
        text-align: center;
        margin: 20px 0;
    }
    .main-content {
        margin-top: 70px; /* Add margin to avoid overlapping with the navbar */
    }
</style>
</head>
<body>
    <nav class="navbar">                                                                                      <!-- Navigasi bar atas -->
        <img class="logo" src="/assets/images/Logo_Ajukan.png" alt="Ajukan" style="width: fit-content;">     <!-- Logo website -->
        <div class="profile">                                                                                 <!-- Bagian profil di navbar -->
            <img class="profile-image" src="https://web.rupa.ai/wp-content/uploads/2023/08/aruna3619_Elegant_black_and_white_profesional_photo_of_a_young__46c0ef4a-ba0a-4e23-af98-c824d9b7127d.png" alt="Profile Image">
            <p class="profile-name"><?php echo $user['nama']; ?></p>                                         <!-- Menampilkan nama user -->
        </div>
    </nav>

    <input type="checkbox" id="toggle">                                                                       <!-- Checkbox untuk toggle sidebar -->
    <label for="toggle" class="side-toggle">&#9776;</label>                                                  <!-- Tombol toggle sidebar -->
    <span class="fas fa-bars"></span>                                                                        <!-- Ikon menu hamburger -->
    </label>

    <div class="sidebar">                                                                                     <!-- Sidebar navigasi -->
        <div class="sidebar-menu">                                                                           <!-- Menu Dashboard -->
            <a href="/views/dashboard/karyawan/dashboard_karyawan.php" class="switch-button">
                <span class="fas fa-home"></span>
                <span class="label"> Dashboard</span>
            </a>
        </div>
        
        <div class="sidebar-menu">                                                                           <!-- Menu Identitas -->
            <a href="/views/user/identitas.php" class="switch-button active">
                <span class="fas fa-user"></span>
                <span class="label"> Identitas</span>
            </a>
        </div>

        <div class="sidebar-menu">                                                                           <!-- Menu History -->
            <a href="/views/user/history.php" class="switch-button">
                <span class="fas fa-clock"></span>
                <span class="label"> History</span>
            </a>
        </div>

        <br>
        <br>

        <div class="sidebar-menu">                                                                           <!-- Menu Logout -->
            <a href="/views/auth/logout.php" class="switch-button">
                <span class="fas fa-sign-out-alt"></span>
                <span class="label"> Logout</span>
            </a>
        </div>
    </div>

    <main>
    <div class="main-content">
    <div class="container">
    <h1 class="title">IDENTITAS</h1>
        <div class="flex-container2">
            <!-- Bagian Foto di Kiri -->
            <div class="photo-section">
                <div class="photo-box">
                <img src="../administrator/uploads_photo/<?= htmlspecialchars($user['foto_profil']) ?>" alt="Foto Profil">
                </div>
            </div>
            
            <!-- Bagian Form Identitas di Kanan -->
            <div class="form-section2">
                <form>
                    <table>
                        <tr>
                            <td><label for="nik">NIK :</label></td>
                            <td><input type="text" id="nik" value="<?php echo $user['nik']; ?>" readonly aria-label="NIK"></td>
                        </tr>
                        <tr>
                            <td><label for="nama">Nama :</label></td>
                            <td><input type="text" id="nama" value="<?php echo $user['nama']; ?>" readonly aria-label="Nama"></td>
                        </tr>
                        <tr>
                            <td><label for="email">Email :</label></td>
                            <td><input type="email" id="email" value="<?php echo $user['email']; ?>" readonly aria-label="Email"></td>
                        </tr>
                        <tr>
                            <td><label for="tempat">Tempat Lahir :</label></td>
                            <td><input type="text" id="tempat" value="<?php echo $user['tempat_lahir']; ?>" readonly aria-label="Tempat Lahir"></td>
                        </tr>
                        <tr>
                            <td><label for="tanggal">Tanggal Lahir :</label></td>
                            <td><input type="text" id="tanggal" value="<?php echo $user['tanggal_lahir']; ?>" readonly aria-label="Tanggal Lahir"></td>
                        </tr>
                        <tr>
                            <td><label for="alamat">Alamat :</label></td>
                            <td><input type="text" id="alamat" value="<?php echo $user['alamat']; ?>" readonly aria-label="Alamat"></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>

        <!-- Pengaturan Akun dan Ubah Email di Bawah Identitas -->
        <div class="settings-container">
            <div class="settings-box">
                <h3>Pengaturan Akun</h3>
                <form action="update_password.php" method="POST">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required>
                    
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    
                    <button type="submit">Ubah Password</button>
                </form>
            </div>
            <div class="settings-box">
                <h3>Ubah Email</h3>
                <form action="update_email.php" method="POST">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                    
                    <button type="submit">Ubah Email</button>
                </form>
            </div>
        </div>
    </div>
    </main>
</div>
</body>
</html>
