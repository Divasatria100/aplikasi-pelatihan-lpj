<?php 
session_start();  // Memulai session untuk menyimpan data user

// Konfigurasi koneksi database
$host_db    = "localhost";    // Alamat host database
$user_db    = "root";        // Username database
$pass_db    = "";            // Password database 
$nama_db    = "db_ajukan";   // Nama database
$koneksi    = mysqli_connect($host_db, $user_db, $pass_db, $nama_db); // Membuat koneksi ke database

// Cek apakah koneksi berhasil
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());  // Tampilkan pesan error jika koneksi gagal
}

// Fungsi untuk redirect berdasarkan role
function redirect_based_on_role($role) {
    echo "Redirecting based on role: " . $role;
    if ($role == 'karyawan') {
        header("Location: /views/dashboard/karyawan/dashboard_karyawan.php");
    } elseif ($role == 'manajer') {
        header("Location: /views/dashboard/manajer/dashboard_manajer.php");
    } elseif ($role == 'admin') {
        header("Location: /views/dashboard/admin/dashboard_admin.php");
    }
    exit();
}

// Cek apakah user sudah login (session ada)
if (isset($_SESSION['session_username'])) {
    echo "Session found, redirecting...";
    redirect_based_on_role($_SESSION['session_role']);
}

// Inisialisasi variabel
$err = ''; // Mendefinisikan variabel $err untuk menampung pesan error
$username = isset($_POST['username']) ? $_POST['username'] : ''; // Input NIK/Email
$password = isset($_POST['password']) ? $_POST['password'] : ''; // Input password
$ingataku = isset($_POST['ingataku']) ? 1 : 0;                   // Checkbox "ingat saya"

// Cek cookie untuk fitur "ingat saya"
if (isset($_COOKIE['cookie_username']) && !isset($_SESSION['session_username'])) {
    echo "Cookie found, processing...";
    $cookie_username = $_COOKIE['cookie_username'];
    $cookie_password = $_COOKIE['cookie_password'];

    // Cek apakah input berupa email atau NIK
    if (filter_var($cookie_username, FILTER_VALIDATE_EMAIL)) {
        $sql = "SELECT * FROM admin WHERE email = '$cookie_username'";
    } else {
        $sql = "SELECT * FROM user WHERE nik = '$cookie_username'";
    }

    $result = mysqli_query($koneksi, $sql);
    $data = mysqli_fetch_array($result);

    if ($data && md5($data['password']) == $cookie_password) {
        $_SESSION['user_id'] = $data['user_id'] ?? $data['admin_id'];
        $_SESSION['session_username'] = $cookie_username;
        $_SESSION['session_role'] = $data['role'] ?? 'admin';
        $_SESSION['session_nama'] = $data['nama'];
        $_SESSION['login_success'] = true;
        $_SESSION['session_nik'] = $data['nik'];

        redirect_based_on_role($_SESSION['session_role']);
    }
}

// Proses form login
if (isset($_POST['login'])) {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $ingataku = isset($_POST['ingataku']) ? 1 : 0;

    // Debug line
    echo "Attempting login with username: $username<br>";

    // Validasi form
    if ($username == '' || $password == '') {
        $err .= "<li>Silakan masukkan NIK/Email dan password.</li>";
    } else {
        // Determine if input is email or NIK
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $sql = "SELECT * FROM admin WHERE email = '$username'";
            echo "Checking admin table<br>"; // Debug line
        } else {
            $sql = "SELECT * FROM user WHERE nik = '$username'";
            echo "Checking user table<br>"; // Debug line
        }

        $result = mysqli_query($koneksi, $sql);
        $data = mysqli_fetch_array($result);

        if (!$data) {
            $err .= "<li>Username/Email tidak ditemukan.</li>";
            echo "No user found<br>"; // Debug line
        } else {
            echo "User found. Stored password: " . substr($data['password'], 0, 20) . "...<br>"; // Debug line
            
            $loginSuccess = false;

            // Try password_verify first
            if (password_verify($password, $data['password'])) {
                echo "Password verified with password_verify<br>"; // Debug line
                $loginSuccess = true;
            } 
            // If that fails and it's a user account, try MD5
            else if (!filter_var($username, FILTER_VALIDATE_EMAIL) && md5($password) === $data['password']) {
                echo "Password verified with MD5<br>"; // Debug line
                $loginSuccess = true;
            } else {
                echo "Password verification failed<br>"; // Debug line
            }

            if ($loginSuccess) {
                $_SESSION['user_id'] = $data['user_id'] ?? $data['admin_id'];
                $_SESSION['session_username'] = $username;
                $_SESSION['session_role'] = $data['role'] ?? 'admin';
                $_SESSION['session_nama'] = $data['nama'];
                $_SESSION['session_nik'] = $data['nik'] ?? null;
                $_SESSION['login_success'] = true;

                echo "Login success. Role: " . $_SESSION['session_role'] . "<br>"; // Debug line

                if ($ingataku == 1) {
                    setcookie('cookie_username', $username, time() + (60 * 60 * 24 * 30), "/");
                    setcookie('cookie_password', md5($password), time() + (60 * 60 * 24 * 30), "/");
                }

                redirect_based_on_role($_SESSION['session_role']);
            } else {
                $err .= "<li>Password yang dimasukkan salah.</li>";
            }
        }
    }
}

// Tambahkan pesan kesalahan jika ada
if ($err != '') {
    echo '<ul>' . $err . '</ul>';
}
?>

<?php
// Menampilkan notifikasi berhasil login jika session login_success ada
if (isset($_SESSION['login_success']) && $_SESSION['login_success'] == true) {
    echo "<script>alert('Login berhasil!');</script>";
    unset($_SESSION['login_success']);  // Menghapus session login_success setelah notifikasi ditampilkan
}
?>

<!DOCTYPE html>                                                                    <!-- Deklarasi tipe dokumen HTML -->
<html lang="en">                                                                  <!-- Tag pembuka HTML dengan bahasa Inggris -->
<head>
    <meta charset="UTF-8">                                                        <!-- Pengaturan karakter encoding UTF-8 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">                         <!-- Kompatibilitas dengan Internet Explorer -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">        <!-- Pengaturan viewport untuk responsif -->
    <title>Login</title>                                                          <!-- Judul halaman web -->
    <!-- Import Bootstrap CSS dan Font Poppins -->
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">  <!-- Import Bootstrap CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">      <!-- Import Font Poppins -->
    <style>
        * {                                                                       /* Reset CSS untuk semua elemen */
            padding: 0;                                                           /* Hapus padding default */
            margin: 0;                                                           /* Hapus margin default */
            box-sizing: border-box;                                              /* Gunakan box-sizing border-box */
        }

        body {                                                                   /* Styling untuk body */
            font-family: 'Poppins', sans-serif;                                 /* Gunakan font Poppins */
            overflow: hidden;                                                   /* Sembunyikan scrollbar */
            background: rgba(0, 0, 0, 0.3);                                    /* Background overlay gelap */
        }

        .container {                                                            /* Styling untuk container utama */
            width: 100%;                                                       /* Lebar penuh */
            height: 100vh;                                                     /* Tinggi sesuai viewport */
            display: flex;                                                     /* Gunakan flexbox */
            justify-content: center;                                          /* Pusatkan secara horizontal */
            align-items: center;                                              /* Pusatkan secara vertikal */
        }

        #loginbox {                                                            /* Styling untuk box login */
            background-color: rgba(255, 255, 255, 0.8);                       /* Background putih transparan */
            padding: 30px;                                                    /* Padding dalam box */
            border-radius: 10px;                                             /* Sudut melengkung */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);                      /* Efek bayangan */
        }

        .panel-heading {                                                       /* Styling untuk judul panel */
            text-align: center;                                               /* Teks di tengah */
            font-size: 1.8rem;                                               /* Ukuran font */
            color: #333;                                                     /* Warna teks */
            font-weight: bold;                                               /* Teks tebal */
        }

        .panel-body {                                                         /* Styling untuk body panel */
            padding: 20px;                                                   /* Padding dalam panel */
        }

        .input-group {                                                        /* Styling untuk grup input */
            margin-bottom: 15px;                                             /* Margin bawah */
        }

        .input-group-addon {                                                  /* Styling untuk ikon input */
            background-color: rgba(4, 22, 67, 1);                            /* Warna background ikon */
            color: white;                                                    /* Warna ikon */
        }

        input.form-control {                                                  /* Styling untuk input form */
            border-radius: 5px;                                              /* Sudut melengkung */
            border: 1px solid rgba(4, 22, 67, 1);                           /* Border input */
            padding: 10px;                                                   /* Padding dalam input */
            font-size: 1rem;                                                /* Ukuran font */
        }

        input.form-control:focus {                                           /* Styling saat input difokuskan */
            border-color: #32be8f;                                          /* Warna border saat fokus */
            box-shadow: 0 0 5px rgba(4, 22, 67, 1);                        /* Efek bayangan saat fokus */
        }

        .checkbox label {                                                    /* Styling untuk label checkbox */
            font-size: 1rem;                                                /* Ukuran font */
            color: #333;                                                    /* Warna teks */
        }

        .btn {                                                              /* Styling untuk tombol */
            display: block;                                                 /* Tampilan block */
            width: 100%;                                                   /* Lebar penuh */
            height: 50px;                                                  /* Tinggi tombol */
            border-radius: 25px;                                          /* Sudut melengkung */
            background-image: linear-gradient(to right, rgba(4, 22, 67, 1), rgba(4, 22, 67, 0.8), rgba(4, 22, 67, 1)) !important;  /* Gradient background */
            background-size: 200%;                                        /* Ukuran background */
            font-size: 1.2rem;                                           /* Ukuran font */
            color: white !important;                                     /* Warna teks */
            font-family: 'Poppins', sans-serif;                         /* Font tombol */
            text-transform: uppercase;                                   /* Teks kapital */
            margin: 1rem 0;                                             /* Margin atas bawah */
            cursor: pointer;                                            /* Cursor pointer */
            transition: .5s;                                            /* Efek transisi */
        }

        .btn:hover {                                                        /* Styling tombol saat hover */
            background-image: linear-gradient(to right, rgba(2, 47, 98, 1), rgba(4, 22, 67, 0.9), rgba(2, 47, 98, 1)) !important;
            background-position: right !important;                          /* Posisi background bergeser */
        }

        .btn:active, .btn:focus {                                          /* Styling tombol saat aktif/fokus */
            background-image: linear-gradient(to right, rgba(4, 22, 67, 1), rgba(4, 22, 67, 0.8), rgba(4, 22, 67, 1)) !important;
            background-position: left !important;                          /* Posisi background kembali */
            outline: none;                                                /* Hapus outline */
        }

        /* Responsive design untuk layar kecil */
        @media screen and (max-width: 900px) {                            /* Media query untuk layar < 900px */
            .container {
                padding: 0 2rem;                                          /* Padding container */
            }

            #loginbox {
                width: 100%;                                              /* Lebar penuh */
                padding: 20px;                                            /* Padding box login */
            }

            .panel-heading {
                font-size: 1.5rem;                                        /* Ukuran font judul */
            }

            .btn {
                font-size: 1rem;                                          /* Ukuran font tombol */
            }
        }
    </style>
</head>
<body style="
    background-image: url('/assets/images/gedung.png');                    /* Background gambar gedung */
    background-repeat: no-repeat;                                          /* Gambar tidak berulang */
    background-size: cover;                                                /* Gambar menutupi background */
    background-position: center;                                           /* Posisi gambar di tengah */
    height: 100vh;                                                         /* Tinggi sesuai viewport */
    margin: 0;                                                            /* Hapus margin */
">
    <div class="container my-4">                                           <!-- Container utama dengan margin -->
        <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">  <!-- Box login dengan grid system -->
            <div class="panel panel-info">                                 <!-- Panel informasi -->
                <div class="panel-heading">                                <!-- Heading panel -->
                    <div class="panel-title">Login dan Masuk Ke Sistem</div>  <!-- Judul panel -->
                </div>      
                <div style="padding-top:30px" class="panel-body">          <!-- Body panel -->
                    <?php if($err){ ?>                                     <!-- Cek jika ada error -->
                        <div id="login-alert" class="alert alert-danger col-sm-12">  <!-- Alert error -->
                            <ul><?php echo $err ?></ul>                    <!-- Tampilkan pesan error -->
                        </div>
                    <?php } ?>                
                    <form id="loginform" class="form-horizontal" action="" method="post" role="form">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input id="login-username" type="text" class="form-control" name="username" 
                                value="<?php echo isset($username) ? $username : ''; ?>" 
                                placeholder="Masukkan NIK">
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                            <input id="login-password" type="password" class="form-control" name="password" placeholder="Password">
                        </div>
                        <div class="input-group">
                            <div class="checkbox">
                                <label>
                                    <input id="login-remember" type="checkbox" name="ingataku" value="1" 
                                        <?php if(isset($ingataku) && $ingataku == 1) echo "checked"; ?>> Ingat Saya
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12 controls">
                                <input type="submit" name="login" class="btn btn-success" value="Login">
                            </div>
                        </div>
                    </form>
                </div>                     
            </div>  
        </div>
    </div>
</body>
</html>

