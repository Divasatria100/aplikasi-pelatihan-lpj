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

if (!$user_id) {
    $_SESSION['error_message'] = "No user specified for deletion.";
    header("Location: dashboard_admin.php");
    exit();
}

// Fetch user data for confirmation
$query = "SELECT nama, nik, foto_profil FROM user WHERE user_id = '$user_id'";
$result = mysqli_query($koneksi, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    $_SESSION['error_message'] = "User not found.";
    header("Location: dashboard_admin.php");
    exit();
}

// Process deletion
if (isset($_POST['confirm_delete'])) {
    // Check if there is a profile image and delete it from the server
    if ($user['foto_profil']) {
        $imagePath = './uploads_photo/' . $user['foto_profil'];
        if (file_exists($imagePath)) {
            unlink($imagePath);  // Delete the file from server
        }
    }

    // Delete the user from the database
    $delete_query = "DELETE FROM user WHERE user_id = '$user_id'";
    if (mysqli_query($koneksi, $delete_query)) {
        $_SESSION['success_message'] = "User successfully deleted.";
        header("Location: dashboard_admin.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error deleting user: " . mysqli_error($koneksi);
        header("Location: dashboard_admin.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User</title>
    
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <style>
        .main-content {
            display: flex;
            justify-content: center;
            padding: 20px;
            margin: 0 auto;
            width: 100%;
            max-width: 1000px; /* Membatasi lebar konten utama */
        }

        .delete-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 50px auto;
            text-align: center;
        }

        .warning-icon {
            color: #dc3545;
            font-size: 48px;
            margin-bottom: 20px;
        }

        .delete-message {
            margin: 20px 0;
            font-size: 16px;
            color: #333;
        }

        .user-details {
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .button-group {
    margin-top: 30px;
    display: flex;
    gap: 10px;
    justify-content: center;
}

.btn-delete, .btn-cancel {
    padding: 12px 20px;  /* Menyamakan padding */
    font-size: 16px;  /* Ukuran font sama agar tampak proporsional */
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-align: center;
    width: 150px;  /* Memastikan lebar tombol sama */
}

.btn-delete {
    background: #dc3545;
    color: white;
}

.btn-cancel {
    background: #6c757d;
    color: white;
}

.btn-delete:hover {
    background: #c82333;
}

.btn-cancel:hover {
    background: #5a6268;
}

        /* Responsiveness */
        @media (max-width: 768px) {
            .main-content {
                padding: 10px;
            }

            .delete-container {
                width: 100%;
                padding: 20px;
            }
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
        <div class="delete-container">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2>Konfirmasi Hapus User</h2>
            <div class="delete-message">
                Apakah Anda yakin ingin menghapus user ini?
            </div>
            <div class="user-details">
                <p><strong>Nama:</strong> <?php echo htmlspecialchars($user['nama']); ?></p>
                <p><strong>NIK:</strong> <?php echo htmlspecialchars($user['nik']); ?></p>
            </div>
            <div class="button-group">
                <a href="dashboard_admin.php" class="btn-cancel">Cancel</a>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="confirm_delete" class="btn-delete">Delete</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 