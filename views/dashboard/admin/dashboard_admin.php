<?php 
session_start();

// Memastikan pengguna sudah login sebagai admin
if (!isset($_SESSION['user_id']) || $_SESSION['session_role'] != 'admin') {
    header("Location: /views/auth/login.php");
    exit();
}

// Mengambil user_id dari session
$user_id = $_SESSION['user_id'];

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
$query = "SELECT admin_id, nama, email, password, nomor_telepon 
        FROM admin 
        WHERE admin_id = '$user_id'";
$result = mysqli_query($koneksi, $query);

// Memeriksa apakah query berhasil
if (!$result) {
    die("Query gagal dijalankan: " . mysqli_error($koneksi));
}

// Memeriksa apakah data pengguna ditemukan
$user = mysqli_fetch_assoc($result);
if (!$user) {
    echo "Data pengguna tidak ditemukan.";
    exit();
}

// Cek jika login berhasil
if (isset($_SESSION['login_success']) && $_SESSION['login_success'] == true) {
    echo "<script>alert('Login berhasil!');</script>";
    unset($_SESSION['login_success']);  // Menghapus session login_success setelah notifikasi ditampilkan
}

// Fetch all users
$query = "SELECT * FROM user ORDER BY user_id DESC";
$result = mysqli_query($koneksi, $query);
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
    
    <title>Dashboard admin</title>
    
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

        .table-container {
            padding: 20px;
            background: white;
            border-radius: 8px;
            margin: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-buttons a {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
        }
        
        .edit-btn {
            background-color: #007bff;
        }
        
        .delete-btn {
            background-color: #dc3545;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <img class="logo" src="/assets/images/Logo_Ajukan.png" alt="Ajukan" style="width: fit-content;">
        <div class="profile">
            <img class="profile-image" src="https://web.rupa.ai/wp-content/uploads/2023/08/aruna3619_Elegant_black_and_white_profesional_photo_of_a_young__46c0ef4a-ba0a-4e23-af98-c824d9b7127d.png" alt="Profile Image">
            <p class="profile-name"><?php echo $user['nama']; ?></p>
        </div>
    </nav>

    <input type="checkbox" id="toggle">
    <label for="toggle" class="side-toggle">&#9776;</label>

    <div class="sidebar">
        <div class="sidebar-menu">
            <a href="/views/dashboard/admin/dashboard_admin.php" class="switch-button active">
                <span class="fas fa-home"></span>
                <span class="label"> Dashboard</span>
            </a>
        </div>
        
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
        <div class="dashboard-container1">
            <!-- Training Proposal Button Card -->
            <div class="card totall">
                <div class="info">
                    <div class="info-detail">
    <div class="info-detail">
        <div class="button-container">
            <a href="/views/administrator/form_user.php">
                <button class="training-button">
                    <span class="plus-icon">+</span> Buat Data User
                </button>
            </a>
        </div>
        <div class="button-container">
            <a href="/views/administrator/kelola_jurusan.php">
                <button class="training-button">
                    <span class="plus-icon">✏️</span> Kelola Jurusan
                </button>
            </a>
        </div>
    </div>
    <style>
        .button-container {
            display: inline-block;
            margin-right: 10px; /* Adjust spacing between buttons */
        }
    </style>
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
                    <h2>Tabel User</h2>
                </div>
                <!-- Training Data Table -->
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
                                    <td><?php echo htmlspecialchars($row['nik']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['tempat_lahir']); ?></td>
                                    <td><?php echo htmlspecialchars($row['tanggal_lahir']); ?></td>
                                    <td><?php echo htmlspecialchars($row['alamat']); ?></td>
                                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                                    <td class="action-buttons">
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
    <script src="/lib/jquery/jquery-3.7.1.js"></script>
    <script src="/lib/datatables/dataTables.js"></script>
    <script>
            $(document).ready( function () {
                $('#myTable').DataTable();
            } );
    </script>
</body>
</html>
