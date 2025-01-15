<?php 
// Memulai session PHP untuk manajemen sesi pengguna
session_start();

// Memeriksa apakah pengguna sudah login dan memiliki role admin
// Jika tidak, redirect ke halaman login
if (!isset($_SESSION['user_id']) || $_SESSION['session_role'] != 'admin') {
    header("Location: /views/auth/login.php");
    exit();
}

// Mengambil ID admin dari session yang aktif
$admin_id = $_SESSION['user_id'];

// Konfigurasi koneksi database
$host = "localhost";          // Hostname database server
$username = "root";           // Username database
$password = "";               // Password database (kosong untuk default XAMPP)
$database = "db_ajukan";      // Nama database yang digunakan
$koneksi = mysqli_connect($host, $username, $password, $database);  // Membuat koneksi ke database

// Memeriksa koneksi database
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Query untuk mengambil data admin dari database berdasarkan admin_id
$query = "SELECT admin_id, nama, email, password, nomor_telepon 
        FROM admin 
        WHERE admin_id = '$admin_id'";
$result = mysqli_query($koneksi, $query);

// Memeriksa apakah query berhasil dijalankan
if (!$result) {
    die("Query gagal dijalankan: " . mysqli_error($koneksi));
}

// Mengambil data admin dalam bentuk array associative
// Jika data tidak ditemukan, tampilkan pesan error
$user = mysqli_fetch_assoc($result);
if (!$user) {
    echo "Data pengguna tidak ditemukan.";
    exit();
}

// Memproses form ketika ada request POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengambil dan membersihkan data form dari potensi SQL injection
    $nik = mysqli_real_escape_string($koneksi, $_POST['nik']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $tempat_lahir = mysqli_real_escape_string($koneksi, $_POST['tempat_lahir']);
    $tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);  // Mengenkripsi password
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);

    // Inisialisasi variabel untuk foto profil
    $profileImage = NULL;
    
    // Memproses upload file foto profil jika ada
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_image']['tmp_name'];    // Path temporary file
        $fileName = $_FILES['profile_image']['name'];           // Nama asli file
        $fileSize = $_FILES['profile_image']['size'];          // Ukuran file
        $fileType = $_FILES['profile_image']['type'];          // Tipe MIME file
        $fileNameCmps = explode(".", $fileName);               // Memisahkan nama file dan ekstensi
        $fileExtension = strtolower(end($fileNameCmps));       // Mengambil ekstensi file
        
        // Mendefinisikan ekstensi file yang diperbolehkan
        $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'gif');

        // Memeriksa apakah ekstensi file valid
        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Membuat nama file baru dengan MD5 hash
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = './uploads_photo/';                // Direktori upload
            $dest_path = $uploadFileDir . $newFileName;         // Path lengkap file tujuan

            // Memindahkan file dari temporary ke direktori tujuan
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

    // Query untuk menyimpan data user baru ke database
    $query = "INSERT INTO user (nik, nama, email, tempat_lahir, tanggal_lahir, alamat, password, role, admin_id, foto_profil) 
              VALUES ('$nik', '$nama', '$email', '$tempat_lahir', '$tanggal_lahir', '$alamat', '$password', '$role', '$admin_id', '$profileImage')";

    // Menjalankan query dan memeriksa hasilnya
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
    <meta charset="UTF-8">                                                         <!-- Pengaturan karakter encoding UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">        <!-- Pengaturan viewport untuk responsivitas -->
    <title>Create User</title>                                                    <!-- Judul halaman -->
    
    <!-- Import file CSS dan font -->
    <link rel="stylesheet" href="/assets/css/style.css">                          <!-- CSS utama -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap">    <!-- Font Poppins -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">      <!-- Font Awesome icons -->
    
    <!-- Import JavaScript -->
    <script src="/assets/js/script.js"></script>                                  <!-- File JavaScript utama -->
    
    <!-- Import DataTables CSS -->
    <link rel="stylesheet" href="/lib/datatables/dataTables.css">                 <!-- CSS untuk DataTables -->
    
    <!-- Favicon dan pengaturan tema -->
    <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">    <!-- Favicon 16x16 -->
    <meta name="msapplication-TileColor" content="#ffffff">                       <!-- Warna tile untuk MS -->
    <meta name="theme-color" content="#ffffff">                                   <!-- Warna tema umum -->
    
    <!-- CSS Inline untuk styling spesifik halaman -->
    <style>
    /* Styling untuk konten utama */
    .main-content {
        background-color: #f0f0f0;                /* Warna latar belakang abu-abu muda */
        min-height: 100vh;                        /* Tinggi minimal 100% viewport */
        padding: 40px;                            /* Padding di semua sisi */
        margin-left: 250px;                       /* Margin kiri untuk sidebar */
    }

    /* Styling untuk container */
    .container {
        max-width: 1000px;                        /* Lebar maksimal container */
        margin: 0 auto;                           /* Margin otomatis kiri-kanan */
    }

    /* Styling untuk card konten */
    .content-card {
        background: white;                        /* Latar belakang putih */
        border-radius: 8px;                       /* Sudut melengkung */
        padding: 30px;                            /* Padding dalam */
        margin-bottom: 30px;                      /* Margin bawah */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Bayangan */
    }

    /* Styling untuk judul section */
    .section-title {
        font-size: 20px;                          /* Ukuran font */
        font-weight: 600;                         /* Ketebalan font */
        margin-bottom: 25px;                      /* Margin bawah */
        color: #333;                              /* Warna teks */
    }

    /* Styling untuk grid form */
    .form-grid {
        display: grid;                            /* Layout grid */
        grid-template-columns: 2fr 1fr;           /* Pembagian kolom */
        gap: 30px;                                /* Jarak antar elemen */
    }

    /* Styling untuk baris form */
    .form-row {
        display: flex;                            /* Layout flex */
        margin-bottom: 15px;                      /* Margin bawah */
        align-items: center;                      /* Perataan vertikal */
    }

    /* Styling untuk label form */
    .form-row label {
        width: 120px;                             /* Lebar label */
        font-weight: 500;                         /* Ketebalan font */
    }

    /* Styling untuk input dan select */
    .form-row input,
    .form-row select {
        flex: 1;                                  /* Mengisi sisa ruang */
        padding: 8px;                             /* Padding dalam */
        border: 1px solid #ddd;                   /* Border */
        border-radius: 4px;                       /* Sudut melengkung */
    }

    /* Styling untuk area upload profil */
    .profile-upload {
        text-align: center;                       /* Perataan teks tengah */
        margin-bottom: 20px;                      /* Margin bawah */
    }

    /* Styling untuk preview profil */
    .profile-preview {
        width: 150px;                             /* Lebar preview */
        height: 150px;                            /* Tinggi preview */
        border: 2px dashed #ddd;                  /* Border putus-putus */
        border-radius: 8px;                       /* Sudut melengkung */
        margin: 0 auto 10px;                      /* Margin */
        display: flex;                            /* Layout flex */
        align-items: center;                      /* Perataan vertikal */
        justify-content: center;                  /* Perataan horizontal */
        overflow: hidden;                         /* Menghilangkan overflow */
    }

    /* Styling untuk gambar preview */
    .profile-preview img {
        max-width: 100%;                          /* Lebar maksimal */
        max-height: 100%;                         /* Tinggi maksimal */
        object-fit: cover;                        /* Mode tampilan gambar */
    }

    /* Styling untuk tombol upload file */
    .custom-file-upload {
        display: inline-block;                    /* Display inline-block */
        padding: 8px 24px;                        /* Padding */
        cursor: pointer;                          /* Cursor pointer */
        background-color: #000066;                /* Warna latar belakang */
        color: white;                             /* Warna teks */
        border-radius: 4px;                       /* Sudut melengkung */
        margin-top: 10px;                         /* Margin atas */
    }

    /* Hover effect untuk tombol upload */
    .custom-file-upload:hover {
        background-color: #333399;                /* Warna latar saat hover */
    }

    /* Menyembunyikan input file asli */
    input[type="file"] {
        display: none;                            /* Sembunyikan elemen */
    }

    /* Styling untuk grup tombol */
    .button-group {
        display: flex;                            /* Layout flex */
        justify-content: flex-end;                /* Perataan ke kanan */
        gap: 10px;                                /* Jarak antar tombol */
        margin-top: 20px;                         /* Margin atas */
    }

    /* Hover effect untuk tombol grup */
    .button-group button:hover {
        background-color: #333399;                /* Warna latar saat hover */
    }

    /* Styling untuk tombol submit dan cancel */
    .btn-submit,
    .btn-cancel {
        padding: 8px 24px;                        /* Padding */
        border-radius: 4px;                       /* Sudut melengkung */
        cursor: pointer;                          /* Cursor pointer */
    }

    /* Styling khusus tombol submit */
    .btn-submit {
        background: #000066;                      /* Warna latar */
        color: white;                             /* Warna teks */
        border: none;                             /* Tanpa border */
    }

    /* Styling khusus tombol cancel */
    .btn-cancel {
        background: white;                        /* Warna latar */
        color: #000066;                           /* Warna teks */
        border: 1px solid #000066;                /* Border */
    }
</style>
</head>

<body>
    <!-- Navbar dengan logo dan profil -->
    <nav class="navbar">
        <img class="logo" src="/assets/images/Logo_Ajukan.png" alt="Ajukan" style="width: fit-content;">
        <div class="profile">
            <img class="profile-image" src="https://web.rupa.ai/wp-content/uploads/2023/08/aruna3619_Elegant_black_and_white_profesional_photo_of_a_young__46c0ef4a-ba0a-4e23-af98-c824d9b7127d.png" alt="Profile Image">
            <p class="profile-name"><?php echo $user['nama']; ?></p>    <!-- Menampilkan nama user -->
        </div>
    </nav>

    <!-- Toggle untuk sidebar mobile -->
    <input type="checkbox" id="toggle">
    <label for="toggle" class="side-toggle">&#9776;</label>

    <!-- Sidebar dengan menu navigasi -->
    <div class="sidebar">
        <!-- Menu Dashboard -->
        <div class="sidebar-menu">
            <a href="/views/dashboard/admin/dashboard_admin.php" class="switch-button active" >
                <span class="fas fa-home"></span>                   <!-- Icon home -->
                <span class="label"> Dashboard</span>               <!-- Label menu -->
            </a>
        </div>
        
        <!-- Menu Logout -->
        <div class="sidebar-menu">
            <a href="/views/auth/logout.php" class="switch-button">
                <span class="fas fa-sign-out-alt"></span>           <!-- Icon logout -->
                <span class="label"> Logout</span>                  <!-- Label menu -->
            </a>
        </div>
        <br>
        <br>
    </div>

    <!-- Konten utama -->
    <div class="main-content">
    <div class="container">
        <!-- Form untuk membuat user baru -->
        <form method="POST" enctype="multipart/form-data">
            <!-- Section Identitas -->
            <div class="content-card">
                <h2 class="section-title">Form User</h2>
                <div class="form-grid">
                    <!-- Detail form untuk data pribadi -->
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
                    <!-- Area upload foto profil -->
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

            <!-- Section Create Account -->
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
                <!-- Tombol submit dan cancel -->
                <div class="button-group">
                    <button type="submit" class="btn-submit">Submit</button>
                    <a href="/views/dashboard/admin/dashboard_admin.php" class="btn-cancel">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Script JavaScript untuk preview gambar dan update NIK -->
<script>
    // Fungsi untuk menampilkan preview gambar yang diupload
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

    // Fungsi untuk mengupdate field NIK pada section Create Account
    function updateCreateAccountNik(value) {
        const nikInput = document.getElementById('create_account_nik');
        nikInput.value = value;

        // Mengubah background menjadi abu-abu jika NIK sudah diinput
        if (value) {
            nikInput.style.backgroundColor = '#f0f0f0';
        } else {
            nikInput.style.backgroundColor = '';
        }
    }
</script>

</body>
</html>