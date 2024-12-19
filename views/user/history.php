<?php
include '../action/db_connection.php';
session_start();

// Memeriksa apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

// Mengambil user_id dan role dari session
$user_id = $_SESSION['user_id'];

// Mengambil nama profile dan role dari tabel user
$nama_query = "SELECT nama, role FROM user WHERE user_id = $user_id";
$nama_result = mysqli_query($conn, $nama_query);
$nama_row = mysqli_fetch_assoc($nama_result);
$nama_profile = $nama_row['nama'];
$user_role = $nama_row['role'];  // role: karyawan atau manajer

// Mengambil data pelatihan yang sudah disetujui dan LPJ yang juga sudah disetujui
if ($user_role == 'manajer') {
    // Manajer bisa melihat semua pelatihan yang sudah disetujui, milik semua karyawan
    $query = "SELECT 
                up.id,
                up.judul_pelatihan,
                up.jenis_pelatihan,
                up.nama_peserta,
                up.tanggal_mulai,
                up.status,
                up.lpj_status,
                up.lpj_file,
                up.sertifikat_file,
                up.komentar,
                u.nama as nama_karyawan
            FROM usulan_pelatihan up
            JOIN user u ON up.user_id = u.user_id 
            WHERE up.status = 'Disetujui' 
            AND up.lpj_status = 'Disetujui'
            ORDER BY up.tanggal_mulai DESC";
} else {
    // Karyawan hanya bisa melihat pelatihan mereka sendiri
    $query = "SELECT 
                up.id,
                up.judul_pelatihan,
                up.jenis_pelatihan,
                up.nama_peserta,
                up.tanggal_mulai,
                up.status,
                up.lpj_status,
                up.lpj_file,
                up.sertifikat_file,
                up.komentar,
                u.nama as nama_karyawan
            FROM usulan_pelatihan up
            JOIN user u ON up.user_id = u.user_id 
            WHERE up.status = 'Disetujui' 
            AND up.lpj_status = 'Disetujui'
            AND up.user_id = $user_id  -- Filter berdasarkan user_id yang sedang login
            ORDER BY up.tanggal_mulai DESC";
}

$result = mysqli_query($conn, $query);
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
    <script>
        function showKomentar(komentar) {
            document.getElementById('komentarText').innerText = komentar || 'Tidak ada komentar';
            document.getElementById('komentarModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('komentarModal').style.display = 'none';
        }

        // Menutup modal ketika mengklik di luar modal
        window.onclick = function(event) {
            var modal = document.getElementById('komentarModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
    <title>Riwayat Pelatihan</title>
    <style>
        .content-container {
            margin-left: 250px;
            padding: 20px;
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
            font-weight: 600;
        }
        .status-disetujui {
            color: white;
            padding: 5px 15px;
            border-radius: 4px;
            font-size: 14px;
            display: inline-block;
            background-color: #28a745;
            text-align: center;
            min-width: 80px;
        }
        .btn-download {
            display: inline-block;
            padding: 5px 10px;
            margin: 2px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn-download:hover {
            background-color: #0056b3;
        }
        .btn-komentar {
            display: inline-block;
            padding: 5px 10px;
            margin: 2px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
        }
        .btn-komentar:hover {
            background-color: #5a6268;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            position: relative;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .modal-title {
            margin-bottom: 15px;
            font-weight: 600;
        }
        .page-title {
            margin-bottom: 20px;
            color: #333;
            font-size: 24px;
            font-weight: 600;
        }
        .btn-komentar {
            display: inline-block;
            padding: 5px 10px;
            margin: 2px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
        }
        .btn-komentar:hover {
            background-color: #5a6268;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            position: relative;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .modal-title {
            margin-bottom: 15px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <img class="logo" src="/assets/images/Logo_Ajukan.png" alt="Ajukan" style="width: fit-content;">
<div class="profile">
    <img class="profile-image"
        src="https://web.rupa.ai/wp-content/uploads/2023/08/aruna3619_Elegant_black_and_white_profesional_photo_of_a_young__46c0ef4a-ba0a-4e23-af98-c824d9b7127d.png" 
        alt="Profile Image">
        <p class="profile-name"><?php echo $nama_profile; ?></p>
</div>
    </nav>

    <!-- Toggle Sidebar -->
    <input type="checkbox" id="toggle">
    <label for="toggle" class="side-toggle">
        <span class="fas fa-bars"></span>
    </label>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-menu">
            <a href="/views/dashboard/karyawan/dashboard_karyawan.php" class="switch-button">
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
            <a href="/views/user/history.php" class="switch-button active">
                <span class="fas fa-clock"></span>
                <span class="label"> History</span>
            </a>
        </div>

        <br><br>

        <div class="sidebar-menu">
            <a href="/views/auth/logout.php" class="switch-button">
                <span class="fas fa-sign-out-alt"></span>
                <span class="label"> Logout</span>
            </a>
        </div>
    </div>

    <!-- Konten Utama -->
    <div class="content-container">
        <h1 class="page-title">Riwayat Pelatihan Selesai</h1>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul Pelatihan</th>
                        <th>Jenis Kegiatan</th>
                        <th>Nama Peserta</th>
                        <th>Tanggal Mulai</th>
                        <th>Pengajuan</th>
                        <th>LPJ</th>
                        <th>Dokumen</th>
                        <th>Komentar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['judul_pelatihan']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['jenis_pelatihan']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_peserta']) . "</td>";
                            echo "<td>" . date('d-m-Y', strtotime($row['tanggal_mulai'])) . "</td>";
                            echo "<td><span class='status-disetujui'>Selesai</span></td>";
                            echo "<td><span class='status-disetujui'>Selesai</span></td>";
                            echo "<td>";
                            if (!empty($row['lpj_file'])) {
                                echo "<a href='/views/user/uploads/" . htmlspecialchars($row['lpj_file']) . "' class='btn-download' download>LPJ</a> ";
                            }
                            if (!empty($row['sertifikat_file'])) {
                                echo "<a href='/views/user/uploads/sertifikat/" . htmlspecialchars($row['sertifikat_file']) . "' class='btn-download' download>Sertifikat</a>";
                            }
                            echo "</td>";
                            echo "<td>";
                            echo "<button class='btn-komentar' onclick='showKomentar(`" . htmlspecialchars($row['komentar'], ENT_QUOTES) . "`)'>";
                            echo "<i class='fas fa-comment'></i> Lihat";
                            echo "</button>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align: center;'>Belum ada riwayat pelatihan yang selesai</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Komentar -->
    <div id="komentarModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="modal-title">Komentar Manajer</div>
            <p id="komentarText"></p>
        </div>
    </div>

    <!-- Modal Komentar -->
    <div id="komentarModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="modal-title">Komentar Manajer</div>
            <p id="komentarText"></p>
        </div>
    </div>

    <script>
        function showKomentar(komentar) {
            document.getElementById('komentarText').innerText = komentar || 'Tidak ada komentar';
            document.getElementById('komentarModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('komentarModal').style.display = 'none';
        }

        // Menutup modal ketika mengklik di luar modal
        window.onclick = function(event) {
            var modal = document.getElementById('komentarModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
