<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['session_role'] != 'admin') {
    header("Location: /views/auth/login.php");
    exit();
}

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "db_ajukan";
$koneksi = mysqli_connect($host, $username, $password, $database);

// Get user ID from URL
$user_id = isset($_GET['id']) ? $_GET['id'] : '';

// Fetch user data
$query = "SELECT * FROM user WHERE user_id = '$user_id'";
$result = mysqli_query($koneksi, $query);
$user = mysqli_fetch_assoc($result);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nik = mysqli_real_escape_string($koneksi, $_POST['nik']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $tempat_lahir = mysqli_real_escape_string($koneksi, $_POST['tempat_lahir']);
    $tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);

    // Update query
    $update_query = "UPDATE user SET 
                    nik = '$nik',
                    nama = '$nama',
                    email = '$email',
                    tempat_lahir = '$tempat_lahir',
                    tanggal_lahir = '$tanggal_lahir',
                    alamat = '$alamat',
                    role = '$role'
                    WHERE user_id = '$user_id'";

    if (mysqli_query($koneksi, $update_query)) {
        $_SESSION['success_message'] = "Data user berhasil diupdate!";
        header("Location: dashboard_admin.php");
        exit();
    } else {
        $error_message = "Error: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <style>
        .main-content {
            margin-left: 250px;
            padding: 20px;
            display: flex;
            justify-content: center;  /* Menyelaraskan konten secara horizontal */
            align-items: center;  /* Menyelaraskan konten secara vertikal */
            height: 100vh;  /* Menjaga tinggi halaman agar form bisa berada di tengah secara vertikal */
            flex-direction: column;  /* Menyusun konten secara vertikal */
        }


        .form-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 20px auto;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .button-group {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-submit {
            background: #007bff;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <img class="logo" src="/assets/images/Logo_Ajukan.png" alt="Ajukan" style="width: fit-content;">
        <div class="profile">
            <img class="profile-image" src="https://web.rupa.ai/wp-content/uploads/2023/08/aruna3619_Elegant_black_and_white_profesional_photo_of_a_young__46c0ef4a-ba0a-4e23-af98-c824d9b7127d.png" alt="Profile Image">
            <p class="profile-name"><?php echo $_SESSION['session_nama']; ?></p>
        </div>
    </nav>

    <!-- Sidebar -->
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
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="form-container">
            <h2>Edit User</h2>
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>NIK:</label>
                    <input type="text" name="nik" value="<?php echo htmlspecialchars($user['nik']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Nama:</label>
                    <input type="text" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Tempat Lahir:</label>
                    <input type="text" name="tempat_lahir" value="<?php echo htmlspecialchars($user['tempat_lahir']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Tanggal Lahir:</label>
                    <input type="date" name="tanggal_lahir" value="<?php echo htmlspecialchars($user['tanggal_lahir']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Alamat:</label>
                    <input type="text" name="alamat" value="<?php echo htmlspecialchars($user['alamat']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Role:</label>
                    <select name="role" required>
                        <option value="karyawan" <?php echo ($user['role'] == 'karyawan') ? 'selected' : ''; ?>>Karyawan</option>
                        <option value="manajer" <?php echo ($user['role'] == 'manajer') ? 'selected' : ''; ?>>Manajer</option>
                    </select>
                </div>

                <div class="button-group">
                    <a href="dashboard_admin.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-submit">Update</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 