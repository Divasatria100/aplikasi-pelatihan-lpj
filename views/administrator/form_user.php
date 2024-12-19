<?php 
session_start();

// Memastikan pengguna sudah login sebagai admin
if (!isset($_SESSION['user_id']) || $_SESSION['session_role'] != 'admin') {
    header("Location: /views/auth/login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

// Database connection
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
        WHERE admin_id = '$admin_id'";
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

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $nik = mysqli_real_escape_string($koneksi, $_POST['nik']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $tempat_lahir = mysqli_real_escape_string($koneksi, $_POST['tempat_lahir']);
    $tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);

    // Handle file upload
    $profileImage = NULL;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_image']['tmp_name'];
        $fileName = $_FILES['profile_image']['name'];
        $fileSize = $_FILES['profile_image']['size'];
        $fileType = $_FILES['profile_image']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = './uploads_photo/';
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $profileImage = $newFileName;
            } else {
                $_SESSION['error_message'] = "Error uploading the file.";
                header("Location: /views/dashboard/admin/dashboard_admin.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Only .jpg, .jpeg, .png, .gif files are allowed.";
            header("Location: /views/dashboard/admin/dashboard_admin.php");
            exit();
        }
    }

    // Insert into database
    $query = "INSERT INTO user (nik, nama, email, tempat_lahir, tanggal_lahir, alamat, password, role, admin_id, foto_profil) 
              VALUES ('$nik', '$nama', '$email', '$tempat_lahir', '$tanggal_lahir', '$alamat', '$password', '$role', '$admin_id', '$profileImage')";

    if (mysqli_query($koneksi, $query)) {
        $_SESSION['success_message'] = "User berhasil ditambahkan!";
        header("Location: /views/dashboard/admin/dashboard_admin.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error: " . mysqli_error($koneksi);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
    
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    
    <script src="/assets/js/script.js"></script>
    
    <link rel="stylesheet" href="/lib/datatables/dataTables.css">
    
    <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">
    
    <style>
    .main-content {
        background-color: #f0f0f0;
        min-height: 100vh;
        padding: 40px;
        margin-left: 250px;
    }

    .container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .content-card {
        background: white;
        border-radius: 8px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .section-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 25px;
        color: #333;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
    }

    .form-row {
        display: flex;
        margin-bottom: 15px;
        align-items: center;
    }

    .form-row label {
        width: 120px;
        font-weight: 500;
    }

    .form-row input,
    .form-row select {
        flex: 1;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .profile-upload {
    text-align: center;
    margin-bottom: 20px;
}

.profile-preview {
    width: 150px;
    height: 150px;
    border: 2px dashed #ddd;
    border-radius: 8px;
    margin: 0 auto 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.profile-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
}

.custom-file-upload {
    display: inline-block;
    padding: 8px 24px;
    cursor: pointer;
    background-color: #000066;
    color: white;
    border-radius: 4px;
    margin-top: 10px;
}

.custom-file-upload:hover {
    background-color: #333399;
}

input[type="file"] {
    display: none;
}

    .button-group {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }

    .button-group button:hover {
        background-color: #333399;
    }
    .btn-submit,
    .btn-cancel {
        padding: 8px 24px;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-submit {
        background: #000066;
        color: white;
        border: none;
    }

    .btn-cancel {
        background: white;
        color: #000066;
        border: 1px solid #000066;
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
            <a href="/views/dashboard/admin/dashboard_admin.php" class="switch-button active" >
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

    <div class="main-content">
    <div class="container">
        <form method="POST" enctype="multipart/form-data">
            <!-- Identitas Section -->
            <div class="content-card">
                <h2 class="section-title">Form User</h2>
                <div class="form-grid">
                    <div class="form-details">
                        <div class="form-row">
                            <label>NIK :</label>
                            <input type="text" name="nik" required oninput="updateCreateAccountNik(this.value)">
                        </div>
                        <div class="form-row">
                            <label>Nama :</label>
                            <input type="text" name="nama" required>
                        </div>
                        <div class="form-row">
                            <label>Email :</label>
                            <input type="email" name="email" required>
                        </div>
                        <div class="form-row">
                            <label>Tempat Lahir :</label>
                            <input type="text" name="tempat_lahir" required>
                        </div>
                        <div class="form-row">
                            <label for="tanggal_lahir">Tanggal Lahir :</label>
                            <div class="date-input-container">
                                <input type="date" id="tanggal_lahir" name="tanggal_lahir" required>
                                <span class="calendar-icon"></span>
                            </div>
                        </div>
                        <div class="form-row">
                            <label>Alamat :</label>
                            <input type="text" name="alamat" required>
                        </div>
                    </div>
                    <div class="profile-upload">
                        <div class="profile-preview" id="imagePreview">
                            <img src="/assets/images/default-profile.png" alt="Profile Preview">
                        </div>
                        <label for="profile_image" class="custom-file-upload">
                            Upload Foto Profil
                        </label>
                        <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="previewImage(this)">
                    </div>
                </div>
            </div>

            <!-- Create Account Section -->
            <div class="content-card">
                <h2 class="section-title">Create Account</h2>
                <div class="form-row">
                    <label>NIK :</label>
                    <input type="text" id="create_account_nik" readonly>
                </div>
                <div class="form-row">
                    <label>Password :</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-row">
                    <label>Role :</label>
                    <select name="role" required>
                        <option value="karyawan">Karyawan</option>
                        <option value="manajer">Manajer</option>
                    </select>
                </div>
                <div class="button-group">
                    <button type="submit" class="btn-submit">Submit</button>
                    <a href="/views/dashboard/admin/dashboard_admin.php" class="btn-cancel">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage(input) {
        const preview = document.querySelector('#imagePreview img');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function updateCreateAccountNik(value) {
    const nikInput = document.getElementById('create_account_nik');
    nikInput.value = value;

    // Mengubah background menjadi abu-abu jika NIK sudah diinput
    if (value) {
        nikInput.style.backgroundColor = '#f0f0f0'; // Warna abu-abu muda
    } else {
        nikInput.style.backgroundColor = ''; // Kembalikan ke default jika tidak ada NIK
    }
}
</script>

</body>
</html>