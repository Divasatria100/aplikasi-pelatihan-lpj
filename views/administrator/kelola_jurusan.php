<?php
// Mengaktifkan pelaporan error untuk membantu debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Memulai session untuk manajemen state user
session_start(); 

// Memeriksa apakah user yang login adalah admin
// Jika bukan admin atau belum login, redirect ke halaman login
if (!isset($_SESSION['user_id']) || $_SESSION['session_role'] != 'admin') {
    header("Location: /views/auth/login.php");
    exit();
}

// Konfigurasi koneksi database
$host = "localhost";          // Host database
$username = "root";           // Username database
$password = "";               // Password database 
$database = "db_ajukan";      // Nama database
$koneksi = new mysqli($host, $username, $password, $database);  // Membuat koneksi baru

// Cek apakah koneksi berhasil
if ($koneksi->connect_error) {
    die("Connection failed: " . $koneksi->connect_error);
}

// Mengambil ID admin dari session
$admin_id = $_SESSION['user_id'];

// Handler untuk request AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    
    // Handler untuk update data jurusan
    if (isset($_POST['update_jurusan'])) {
        foreach ($_POST['jurusan_ids'] as $key => $jurusan_id) {
            // Escape string untuk mencegah SQL injection
            $jurusan_name = mysqli_real_escape_string($koneksi, $_POST['jurusan_names'][$key]);
            
            if ($jurusan_id) {
                // Update jurusan yang sudah ada
                $updateQuery = $koneksi->prepare("UPDATE jurusan SET nama_jurusan=?, admin_id=? WHERE jurusan_id=?");
                $updateQuery->bind_param("sii", $jurusan_name, $admin_id, $jurusan_id);
                $updateQuery->execute();
                if ($updateQuery->affected_rows > 0) {
                    echo "<script>alert('Jurusan updated successfully.');</script>";
                } else {
                    echo "<script>alert('Failed to update jurusan.');</script>";
                }
            } else {
                // Insert jurusan baru
                $insertQuery = $koneksi->prepare("INSERT INTO jurusan (nama_jurusan, admin_id) VALUES (?, ?)");
                $insertQuery->bind_param("si", $jurusan_name, $admin_id);
                $insertQuery->execute();
                if ($insertQuery->affected_rows > 0) {
                    echo "<script>alert('Jurusan added successfully.');</script>";
                } else {
                    echo "<script>alert('Failed to add jurusan.');</script>";
                }
            }
        }
    }

    // Handler untuk update program studi
    if (isset($_POST['update_program_studi'])) {
        foreach ($_POST['program_studi_ids'] as $key => $program_studi_id) {
            // Escape string untuk mencegah SQL injection
            $program_studi_name = mysqli_real_escape_string($koneksi, $_POST['program_studi_names'][$key]);
            $jurusan_id = mysqli_real_escape_string($koneksi, $_POST['program_studi_jurusan_ids'][$key]);
            
            if ($program_studi_id) {
                // Update program studi yang sudah ada
                $updateQuery = $koneksi->prepare("UPDATE program_studi SET nama_program_studi=?, jurusan_id=?, admin_id=? WHERE program_studi_id=?");
                $updateQuery->bind_param("siii", $program_studi_name, $jurusan_id, $admin_id, $program_studi_id);
                $updateQuery->execute();
                if ($updateQuery->affected_rows > 0) {
                    echo "<script>alert('Program Studi updated successfully.');</script>";
                } else {
                    echo "<script>alert('Failed to update Program Studi.');</script>";
                }
            } else {
                // Insert program studi baru
                $insertQuery = $koneksi->prepare("INSERT INTO program_studi (nama_program_studi, jurusan_id, admin_id) VALUES (?, ?, ?)");
                $insertQuery->bind_param("sii", $program_studi_name, $jurusan_id, $admin_id);
                $insertQuery->execute();
                if ($insertQuery->affected_rows > 0) {
                    echo "<script>alert('Program Studi added successfully.');</script>";
                } else {
                    echo "<script>alert('Failed to add Program Studi.');</script>";
                }
            }
        }
    }

    // Handler untuk delete jurusan
    if (isset($_POST['delete_jurusan_id'])) {
        $jurusan_id = mysqli_real_escape_string($koneksi, $_POST['delete_jurusan_id']);

        // Memulai transaksi database
        $koneksi->begin_transaction();

        try {
            // Menghapus program studi terkait
            $deleteProgramStudiQuery = $koneksi->prepare("DELETE FROM program_studi WHERE jurusan_id = ?");
            $deleteProgramStudiQuery->bind_param("i", $jurusan_id);
            $deleteProgramStudiQuery->execute();

            // Menghapus jurusan
            $deleteJurusanQuery = $koneksi->prepare("DELETE FROM jurusan WHERE jurusan_id = ?");
            $deleteJurusanQuery->bind_param("i", $jurusan_id);
            $deleteJurusanQuery->execute();

            // Commit transaksi jika berhasil
            $koneksi->commit();

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            // Rollback jika terjadi error
            $koneksi->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to delete jurusan and related program studi']);
        }

        exit();
    }

    // Handler untuk delete program studi
    if (isset($_POST['delete_program_studi_id'])) {
        $program_studi_id = mysqli_real_escape_string($koneksi, $_POST['delete_program_studi_id']);
        $deleteQuery = $koneksi->prepare("DELETE FROM program_studi WHERE program_studi_id = ?");
        $deleteQuery->bind_param("i", $program_studi_id);
        if ($deleteQuery->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete program studi']);
        }
        exit();
    }

    // Redirect untuk menghindari resubmission form
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Mengambil data jurusan yang ada
$jurusan_query = "SELECT * FROM jurusan ORDER BY nama_jurusan";
$jurusan_result = mysqli_query($koneksi, $jurusan_query);
$jurusan_options = [];
while ($jurusan = mysqli_fetch_assoc($jurusan_result)) {
    $jurusan_options[] = $jurusan;
}

// Mengambil data program studi yang ada
$prodi_query = "SELECT * FROM program_studi ORDER BY nama_program_studi";
$prodi_result = mysqli_query($koneksi, $prodi_query);
$prodi_options = [];
while ($prodi = mysqli_fetch_assoc($prodi_result)) {
    $prodi_options[] = $prodi;
}

// Handler untuk menyimpan program studi baru
if(isset($_POST['save_program_studi'])) {
    if(isset($_POST['prodi_names']) && isset($_POST['jurusan_ids'])) {
        $names = $_POST['prodi_names'];
        $jurusan_ids = $_POST['jurusan_ids'];
        
        foreach($names as $key => $nama_program_studi) {
            if(!empty(trim($nama_program_studi)) && !empty($jurusan_ids[$key])) {
                // Escape string untuk mencegah SQL injection
                $nama_program_studi = mysqli_real_escape_string($koneksi, trim($nama_program_studi));
                $jurusan_id = mysqli_real_escape_string($koneksi, $jurusan_ids[$key]);
                
                // Insert program studi baru
                $query = "INSERT INTO program_studi (nama_program_studi, jurusan_id, admin_id) 
                         VALUES ('$nama_program_studi', '$jurusan_id', '$admin_id')";
                mysqli_query($koneksi, $query);
            }
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handler untuk menghapus program studi
if(isset($_POST['delete_prodi'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['prodi_id']);
    $query = "DELETE FROM program_studi WHERE program_studi_id = '$id'";
    mysqli_query($koneksi, $query);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Struktur Tabel</title>
    
    <!-- Import file CSS dan font -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <style>
    /* CSS untuk layout utama */
    .main-content {
        margin-left: 250px;
        padding: 20px;
        flex: 1;
    }

    /* CSS untuk container struktur */
    .structure-container {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin: 20px;
        width: calc(100% - 40px);
    }

    /* CSS untuk tabel struktur */
    .table-structure {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .table-structure th {
        background-color: #f8f9fa;
        padding: 12px;
        text-align: left;
        border-bottom: 2px solid #dee2e6;
    }

    .table-structure td {
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
    }

    /* CSS untuk input dan select */
    .table-structure input[type="text"],
    .table-structure select {
        width: calc(100% - 24px);
        padding: 6px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    /* CSS untuk grup tombol */
    .button-group {
        margin-top: 20px;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    /* CSS untuk tombol submit */
    .btn-submit {
        background: #007bff;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    /* CSS untuk tombol cancel */
    .btn-cancel {
        background: #6c757d;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        text-decoration: none;
    }

    /* CSS untuk alert */
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        border-radius: 4px;
    }

    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

    /* CSS untuk manajemen dropdown */
    .dropdown-management {
        margin-top: 40px;
        padding-top: 20px;
        border-top: 2px solid #dee2e6;
    }

    .dropdown-section {
        margin-bottom: 30px;
    }

    /* CSS untuk tabel opsi */
    .options-table {
        width: 100%;
        margin-bottom: 15px;
        border-collapse: collapse;
    }

    .options-table th,
    .options-table td {
        padding: 8px;
        border: 1px solid #dee2e6;
    }

    .options-table th {
        background-color: #f8f9fa;
    }

    .options-table td input[type="text"],
    .options-table td select {
        width: calc(100% - 16px);
        padding: 6px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    /* CSS untuk tombol aksi */
    .btn-rename,
    .btn-delete,
    .btn-add {
        padding: 5px 10px;
        margin: 0 5px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-rename {
        background: #ffc107;
        color: #000;
    }

    .btn-delete {
        background: #dc3545;
        color: #fff;
    }

    .btn-add {
        background: #28a745;
        color: #fff;
        margin-top: 10px;
    }

    /* CSS untuk tombol delete dan add */
    .btn-delete, .btn-add {
        margin: 0 5px;
        padding: 5px 10px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-delete {
        background-color: #dc3545;
        color: white;
    }

    .btn-add {
        background-color: #28a745;
        color: white;
    }

    /* CSS untuk tombol save */
    .btn-save {
        margin-top: 10px;
        padding: 8px 16px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    /* CSS untuk tombol danger */
    .btn-danger {
        background: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        margin: 0 5px;
        border-radius: 4px;
        cursor: pointer;
    }

    /* CSS untuk tombol success */
    .btn-success {
        background: #28a745;
        color: white;
        border: none;
        padding: 5px 10px;
        margin: 0 5px;
        border-radius: 4px;
        cursor: pointer;
    }

    /* CSS untuk tombol primary */
    .btn-primary {
        background: #007bff;
        color: white;
        border: none;
        padding: 8px 16px;
        margin-top: 10px;
        border-radius: 4px;
        cursor: pointer;
    }

    /* CSS untuk select */
    select {
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        width: 100%;
        box-sizing: border-box;
    }

    /* CSS untuk section records */
    .records-section {
        margin-top: 30px;
    }

    /* CSS untuk tabel records */
    .records-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .records-table th,
    .records-table td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }

    .records-table th {
        background-color: #f5f5f5;
    }

    /* CSS untuk tombol warning */
    .btn-warning {
        background: #ffc107;
        color: black;
        border: none;
        padding: 5px 10px;
        margin: 0 5px;
        border-radius: 4px;
        cursor: pointer;
    }
    </style>
</head>
<body>
    <!-- Navbar - Bagian atas halaman yang berisi logo dan profil pengguna -->
    <nav class="navbar">
        <!-- Logo aplikasi Ajukan -->
        <img class="logo" src="/assets/images/Logo_Ajukan.png" alt="Ajukan" style="width: fit-content;">
        <!-- Bagian profil pengguna yang menampilkan foto dan nama -->
        <div class="profile">
            <!-- Foto profil default dari web.rupa.ai -->
            <img class="profile-image" src="https://web.rupa.ai/wp-content/uploads/2023/08/aruna3619_Elegant_black_and_white_profesional_photo_of_a_young__46c0ef4a-ba0a-4e23-af98-c824d9b7127d.png" alt="Profile Image">
            <!-- Menampilkan nama pengguna dari session -->
            <p class="profile-name"><?php echo $_SESSION['session_nama']; ?></p>
        </div>
    </nav>

    <!-- Toggle Sidebar - Checkbox dan label untuk mengontrol tampilan sidebar -->
    <input type="checkbox" id="toggle">
    <label for="toggle" class="side-toggle">&#9776;</label>

    <!-- Sidebar - Menu navigasi samping -->
    <div class="sidebar">
        <!-- Menu Dashboard -->
        <div class="sidebar-menu">
            <a href="/views/dashboard/admin/dashboard_admin.php" class="switch-button active">
                <span class="fas fa-home"></span> <!-- Icon rumah -->
                <span class="label"> Dashboard</span>
            </a>
        </div>
        
        <!-- Menu Logout -->
        <div class="sidebar-menu">
            <a href="/views/auth/logout.php" class="switch-button">
                <span class="fas fa-sign-out-alt"></span> <!-- Icon keluar -->
                <span class="label"> Logout</span>
            </a>
        </div>
    </div>

    <!-- Konten Utama -->
    <div class="main-content">
        <div class="structure-container">
            <!-- Judul halaman -->
            <h2>Kelola Jurusan dan Program Studi</h2>

            <!-- Bagian form untuk mengelola Jurusan -->
            <div class="dropdown-section">
                <h4>Jurusan Options</h4>
                <!-- Form untuk menambah/edit jurusan -->
                <form id="jurusanForm" method="POST">
                    <table class="options-table">
                        <thead>
                            <tr>
                                <th>Jurusan Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="jurusanOptions">
                            <!-- Baris template untuk input jurusan -->
                            <tr>
                                <td><input type="text" name="jurusan_names[]" required></td>
                                <td>
                                    <!-- Tombol untuk menghapus dan menambah baris jurusan -->
                                    <button type="button" class="btn-danger" onclick="deleteJurusanRow(this)">Delete</button>
                                    <button type="button" class="btn-success" onclick="addJurusanRow()">Add</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- Tombol untuk menyimpan semua perubahan jurusan -->
                    <button type="submit" name="save_jurusan" class="btn-primary">Save All Jurusan</button>
                </form>
            </div>

            <!-- Bagian form untuk mengelola Program Studi -->
            <div class="dropdown-section">
                <h4>Program Studi Options</h4>
                <!-- Form untuk menambah/edit program studi -->
                <form id="formProgramStudi" method="POST">
                    <input type="hidden" name="save_program_studi" value="1">
                    <table class="options-table">
                        <thead>
                            <tr>
                                <th>Program Studi Name</th>
                                <th>Jurusan</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="prodiOptions">
                            <!-- Baris template untuk input program studi -->
                            <tr>
                                <td><input type="text" name="prodi_names[]" required></td>
                                <td>
                                    <!-- Dropdown untuk memilih jurusan -->
                                    <select name="jurusan_ids[]" required>
                                        <option value="">Select Jurusan</option>
                                        <?php foreach($jurusan_options as $jurusan): ?>
                                            <option value="<?= $jurusan['jurusan_id'] ?>">
                                                <?= htmlspecialchars($jurusan['nama_jurusan']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <!-- Tombol untuk menghapus dan menambah baris program studi -->
                                    <button type="button" class="btn-danger" onclick="deleteProdiRow(this)">Delete</button>
                                    <button type="button" class="btn-success" onclick="addProdiRow()">Add</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- Tombol untuk menyimpan semua perubahan program studi -->
                    <button type="submit" class="btn btn-primary">Save All Program Studi</button>
                </form>
            </div>

            <!-- Tabel untuk menampilkan data Jurusan yang sudah ada -->
            <div class="records-section">
                <h4>Existing Jurusan Records</h4>
                <table class="records-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Jurusan Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Query untuk mengambil semua data jurusan
                        $query = "SELECT * FROM jurusan ORDER BY jurusan_id";
                        $result = mysqli_query($koneksi, $query);
                        $no = 1;
                        // Loop untuk menampilkan setiap data jurusan
                        while($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $no . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_jurusan']) . "</td>";
                            echo "<td>";
                            // Tombol aksi untuk setiap baris jurusan
                            echo "<button class='btn-warning' onclick='editJurusan(" . $row['jurusan_id'] . ", \"" . htmlspecialchars($row['nama_jurusan']) . "\")'>Edit</button>";
                            echo "<button class='btn-danger' onclick='deleteJurusan(" . $row['jurusan_id'] . ")'>Delete</button>";
                            // Tombol delete all hanya muncul di baris pertama
                            if($no == 1) {
                                echo "<button class='btn-danger' onclick='deleteAllJurusan()'>Delete All</button>";
                            }
                            echo "</td>";
                            echo "</tr>";
                            $no++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Tabel untuk menampilkan data Program Studi yang sudah ada -->
            <div class="records-section">
                <h4>Existing Program Studi Records</h4>
                <table class="records-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Program Studi Name</th>
                            <th>Jurusan</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Query untuk mengambil data program studi beserta jurusannya
                        $query = "SELECT ps.program_studi_id, ps.nama_program_studi, j.nama_jurusan 
                                  FROM program_studi ps 
                                  LEFT JOIN jurusan j ON ps.jurusan_id = j.jurusan_id 
                                  ORDER BY ps.program_studi_id";
                        
                        $result = mysqli_query($koneksi, $query);
                        $no = 1;
                        // Loop untuk menampilkan setiap data program studi
                        while($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $no . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_program_studi']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_jurusan']) . "</td>";
                            echo "<td>";
                            // Tombol aksi untuk setiap baris program studi
                            echo "<button class='btn-warning' onclick='editProdi(" . $row['program_studi_id'] . ", \"" . htmlspecialchars($row['nama_program_studi']) . "\")'>Edit</button>";
                            echo "<button class='btn-danger' onclick='deleteProdiRecord(" . $row['program_studi_id'] . ")'>Delete</button>";
                            // Tombol delete all hanya muncul di baris pertama
                            if($no == 1) {
                                echo "<button class='btn-danger' onclick='deleteAllProdi()'>Delete All</button>";
                            }
                            echo "</td>";
                            echo "</tr>";
                            $no++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <script>
    // Fungsi-fungsi JavaScript untuk manipulasi Jurusan

    // Fungsi untuk menambah baris input jurusan baru
    function addJurusanRow() {
        const tbody = document.getElementById('jurusanOptions');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td><input type="text" name="jurusan_names[]" required></td>
            <td>
                <button type="button" class="btn-danger" onclick="deleteJurusanRow(this)">Delete</button>
            </td>
        `;
        tbody.appendChild(newRow);
    }

    // Fungsi untuk menghapus baris input jurusan
    function deleteJurusanRow(button) {
        const row = button.closest('tr');
        const tbody = row.parentElement;
        
        // Mencegah penghapusan baris terakhir
        if (tbody.children.length <= 1) {
            alert('Cannot delete the last row');
            return;
        }
        
        row.remove();
        
        // Menambahkan tombol Add kembali ke baris pertama jika diperlukan
        const firstRow = tbody.querySelector('tr:first-child');
        if (firstRow) {
            const actionCell = firstRow.querySelector('td:last-child');
            if (!actionCell.querySelector('.btn-success')) {
                actionCell.insertAdjacentHTML('beforeend', 
                    '<button type="button" class="btn-success" onclick="addJurusanRow()">Add</button>'
                );
            }
        }
    }

    // Fungsi-fungsi JavaScript untuk manipulasi Program Studi

    // Fungsi untuk menambah baris input program studi baru
    function addProdiRow() {
        const tbody = document.getElementById('prodiOptions');
        const jurusanSelect = document.querySelector('select[name="jurusan_ids[]"]');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td><input type="text" name="prodi_names[]" required></td>
            <td>
                <select name="jurusan_ids[]" required>
                    ${jurusanSelect.innerHTML}
                </select>
            </td>
            <td>
                <button type="button" class="btn-danger" onclick="deleteProdiRow(this)">Delete</button>
            </td>
        `;
        tbody.appendChild(newRow);
    }

    // Fungsi untuk menghapus baris input program studi
    function deleteProdiRow(button) {
        const row = button.closest('tr');
        const tbody = row.parentElement;
        
        // Mencegah penghapusan baris terakhir
        if (tbody.children.length <= 1) {
            alert('Cannot delete the last row');
            return;
        }
        
        row.remove();
        
        // Menambahkan tombol Add kembali ke baris pertama jika diperlukan
        const firstRow = tbody.querySelector('tr:first-child');
        if (firstRow) {
            const actionCell = firstRow.querySelector('td:last-child');
            if (!actionCell.querySelector('.btn-success')) {
                actionCell.insertAdjacentHTML('beforeend', 
                    '<button type="button" class="btn-success" onclick="addProdiRow()">Add</button>'
                );
            }
        }
    }

    // Fungsi untuk mengedit data jurusan
    function editJurusan(id, name) {
        const newName = prompt("Edit Jurusan Name:", name);
        if (newName && newName !== name) {
            if (confirm("Are you sure you want to update this jurusan?")) {
                const formData = new FormData();
                formData.append('edit_jurusan', '1');
                formData.append('jurusan_id', id);
                formData.append('nama_jurusan', newName);
                
                // Mengirim request AJAX untuk update jurusan
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(() => location.reload());
            }
        }
    }

    // Fungsi untuk konfirmasi dan menghapus jurusan
    // Menampilkan peringatan bahwa penghapusan jurusan akan menghapus semua program studi terkait
    function confirmDeleteJurusan(id) {
        // Menampilkan dialog konfirmasi ke user
        if (confirm("Warning: Deleting this jurusan will also delete all related program studi. Are you sure you want to continue?")) {
            // Membuat objek FormData untuk mengirim data
            const formData = new FormData();
            formData.append('delete_jurusan', '1');
            formData.append('jurusan_id', id);
            
            // Mengirim request AJAX untuk menghapus jurusan
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Mengecek apakah response berhasil
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(result => {
                try {
                    // Mencoba parse response sebagai JSON
                    const data = JSON.parse(result);
                    if (data.success) {
                        // Reload halaman jika berhasil
                        document.location = document.location.href;
                    }
                } catch (e) {
                    // Reload halaman jika terjadi error parsing JSON
                    document.location = document.location.href;
                }
            })
            .catch(error => {
                // Reload halaman jika terjadi error network
                document.location = document.location.href;
            });
        }
    }

    // Fungsi untuk mengedit program studi
    // Menerima parameter id, nama, dan id jurusan
    function editProdi(id, name, jurusanId) {
        // Menampilkan prompt untuk input nama baru
        const newName = prompt("Edit Program Studi Name:", name);
        // Mengecek jika user memasukkan nama baru dan berbeda dari sebelumnya
        if (newName && newName !== name) {
            // Konfirmasi update ke user
            if (confirm("Are you sure you want to update this program studi?")) {
                // Membuat objek FormData untuk mengirim data
                const formData = new FormData();
                formData.append('edit_prodi', '1');
                formData.append('prodi_id', id);
                formData.append('nama_prodi', newName);
                
                // Mengirim request AJAX untuk update program studi
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(() => location.reload()); // Reload halaman setelah update
            }
        }
    }

    // Fungsi untuk menghapus program studi
    // Menerima parameter id program studi yang akan dihapus
    function deleteProdiRecord(id) {
        // Konfirmasi penghapusan ke user
        if (confirm("Are you sure you want to delete this program studi?")) {
            // Membuat objek FormData untuk mengirim data
            const formData = new FormData();
            formData.append('delete_prodi', '1');
            formData.append('prodi_id', id);
            
            // Mengirim request AJAX untuk menghapus program studi
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(() => {
                window.location.href = window.location.href; // Reload halaman setelah hapus
            });
        }
    }

    // Fungsi untuk menyimpan semua data program studi
    // Dipanggil saat form program studi di-submit
    function saveAllProgramStudi() {
        document.getElementById('formProgramStudi').submit();
    }

    // Fungsi untuk menghapus semua jurusan
    // Akan menghapus semua jurusan dan program studi terkait
    function deleteAllJurusan() {
        // Konfirmasi penghapusan ke user
        if (confirm("Warning: This will delete ALL jurusan and their associated program studi. Are you sure?")) {
            // Membuat objek FormData untuk mengirim data
            const formData = new FormData();
            formData.append('delete_all_jurusan', '1');
            
            // Mengirim request AJAX untuk menghapus semua jurusan
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(() => {
                window.location.reload(); // Reload halaman setelah hapus
            });
        }
    }

    // Fungsi untuk menghapus semua program studi
    function deleteAllProdi() {
        // Konfirmasi penghapusan ke user
        if (confirm("Warning: This will delete ALL program studi records. Are you sure?")) {
            // Membuat objek FormData untuk mengirim data
            const formData = new FormData();
            formData.append('delete_all_prodi', '1');
            
            // Mengirim request AJAX untuk menghapus semua program studi
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(() => {
                window.location.reload(); // Reload halaman setelah hapus
            });
        }
    }

    // Fungsi untuk menghapus satu jurusan
    // Menerima parameter id jurusan yang akan dihapus
    function deleteJurusan(id) {
        // Konfirmasi penghapusan ke user
        if (confirm("Are you sure you want to delete this jurusan?")) {
            // Membuat objek FormData untuk mengirim data
            const formData = new FormData();
            formData.append('delete_jurusan', '1');
            formData.append('jurusan_id', id);
            
            // Mengirim request AJAX untuk menghapus jurusan
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(() => {
                window.location.reload(); // Reload halaman setelah hapus
            });
        }
    }

    // Fungsi untuk menghapus satu program studi
    // Menerima parameter id program studi yang akan dihapus
    function deleteProdiRecord(id) {
        // Konfirmasi penghapusan ke user
        if (confirm("Are you sure you want to delete this program studi?")) {
            // Membuat objek FormData untuk mengirim data
            const formData = new FormData();
            formData.append('delete_prodi', '1');
            formData.append('prodi_id', id);
            
            // Mengirim request AJAX untuk menghapus program studi
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(() => {
                window.location.reload(); // Reload halaman setelah hapus
            });
        }
    }
    </script>

    <?php
    // Handler untuk menyimpan data jurusan
    if(isset($_POST['save_jurusan'])) {
        if(isset($_POST['jurusan_names'])) {
            // Loop melalui setiap nama jurusan yang dikirim
            foreach($_POST['jurusan_names'] as $nama_jurusan) {
                if(!empty(trim($nama_jurusan))) {
                    // Escape string untuk mencegah SQL injection
                    $nama_jurusan = mysqli_real_escape_string($koneksi, trim($nama_jurusan));
                    
                    // Cek apakah jurusan sudah ada
                    $check_query = "SELECT COUNT(*) as count FROM jurusan WHERE nama_jurusan = '$nama_jurusan'";
                    $check_result = mysqli_query($koneksi, $check_query);
                    $row = mysqli_fetch_assoc($check_result);
                    
                    // Insert hanya jika jurusan belum ada
                    if($row['count'] == 0) {
                        $query = "INSERT INTO jurusan (nama_jurusan, admin_id) VALUES ('$nama_jurusan', '$admin_id')";
                        mysqli_query($koneksi, $query);
                    }
                }
            }
            // Tampilkan alert sukses dan reload halaman
            echo "<script>alert('Jurusan saved successfully!'); location.href = location.href;</script>";
            exit();
        }
    }

    // Handler untuk menyimpan data program studi
    if(isset($_POST['save_prodi'])) {
        if(isset($_POST['prodi_names']) && isset($_POST['jurusan_ids'])) {
            $prodi_names = $_POST['prodi_names'];
            $jurusan_ids = $_POST['jurusan_ids'];
            
            // Loop melalui setiap program studi
            for($i = 0; $i < count($prodi_names); $i++) {
                if(!empty(trim($prodi_names[$i])) && !empty($jurusan_ids[$i])) {
                    // Escape string untuk mencegah SQL injection
                    $nama_prodi = mysqli_real_escape_string($koneksi, trim($prodi_names[$i]));
                    $jurusan_id = mysqli_real_escape_string($koneksi, $jurusan_ids[$i]);
                    
                    // Insert program studi baru
                    $query = "INSERT INTO program_studi (nama_program_studi, jurusan_id) 
                             VALUES ('$nama_prodi', '$jurusan_id')";
                    mysqli_query($koneksi, $query);
                }
            }
            // Tampilkan alert sukses
            echo "<script>alert('Program Studi saved successfully!');</script>";
        }
    }

    // Handler untuk edit jurusan
    if(isset($_POST['edit_jurusan'])) {
        // Escape string untuk mencegah SQL injection
        $id = mysqli_real_escape_string($koneksi, $_POST['jurusan_id']);
        $name = mysqli_real_escape_string($koneksi, $_POST['nama_jurusan']);
        
        // Update nama jurusan
        $query = "UPDATE jurusan SET nama_jurusan = '$name' WHERE jurusan_id = '$id'";
        mysqli_query($koneksi, $query);
        exit;
    }

    // Handler untuk menghapus jurusan
    if(isset($_POST['delete_jurusan'])) {
        // Escape string untuk mencegah SQL injection
        $id = mysqli_real_escape_string($koneksi, $_POST['jurusan_id']);
        
        // Mulai transaksi database
        mysqli_begin_transaction($koneksi);
        
        try {
            // Hapus program studi terkait terlebih dahulu
            $delete_prodi = "DELETE FROM program_studi WHERE jurusan_id = '$id'";
            mysqli_query($koneksi, $delete_prodi);
            
            // Kemudian hapus jurusan
            $delete_jurusan = "DELETE FROM jurusan WHERE jurusan_id = '$id'";
            mysqli_query($koneksi, $delete_jurusan);
            
            // Commit transaksi jika berhasil
            mysqli_commit($koneksi);
            
            die(json_encode(['success' => true]));
        } catch (Exception $e) {
            // Rollback jika terjadi error
            mysqli_rollback($koneksi);
            die(json_encode(['success' => false, 'message' => $e->getMessage()]));
        }
    }

    // Handler untuk edit program studi
    if(isset($_POST['edit_prodi'])) {
        // Escape string untuk mencegah SQL injection
        $id = mysqli_real_escape_string($koneksi, $_POST['prodi_id']);
        $name = mysqli_real_escape_string($koneksi, $_POST['nama_prodi']);
        
        // Update nama program studi
        $query = "UPDATE program_studi SET nama_program_studi = '$name' WHERE program_studi_id = '$id'";
        mysqli_query($koneksi, $query);
        exit;
    }

    // Handler untuk menghapus program studi
    if(isset($_POST['delete_prodi'])) {
        // Escape string untuk mencegah SQL injection
        $id = mysqli_real_escape_string($koneksi, $_POST['prodi_id']);
        
        try {
            // Hapus program studi
            $query = "DELETE FROM program_studi WHERE program_studi_id = '$id'";
            mysqli_query($koneksi, $query);
            exit;
        } catch (Exception $e) {
            exit;
        }
    }

    // Handler untuk menghapus semua jurusan
    if(isset($_POST['delete_all_jurusan'])) {
        // Mulai transaksi database
        mysqli_begin_transaction($koneksi);
        try {
            // Hapus semua program studi terlebih dahulu (karena foreign key)
            mysqli_query($koneksi, "DELETE FROM program_studi");
            // Kemudian hapus semua jurusan
            mysqli_query($koneksi, "DELETE FROM jurusan");
            // Commit transaksi jika berhasil
            mysqli_commit($koneksi);
            exit();
        } catch (Exception $e) {
            // Rollback jika terjadi error
            mysqli_rollback($koneksi);
            exit();
        }
    }

    // Handler untuk menghapus semua program studi
    if(isset($_POST['delete_all_prodi'])) {
        try {
            // Hapus semua program studi
            mysqli_query($koneksi, "DELETE FROM program_studi");
            exit();
        } catch (Exception $e) {
            exit();
        }
    }

    // Mencegah submit form otomatis setelah penghapusan
    if($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Hapus data form yang tersimpan di session
        unset($_SESSION['form_data']);
    }
    ?>
</body>
</html>
