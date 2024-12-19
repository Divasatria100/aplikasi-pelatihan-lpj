<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
$koneksi = new mysqli($host, $username, $password, $database);

if ($koneksi->connect_error) {
    die("Connection failed: " . $koneksi->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // Update Jurusan
    if (isset($_POST['update_jurusan'])) {
        foreach ($_POST['jurusan_ids'] as $key => $jurusan_id) {
            $jurusan_name = mysqli_real_escape_string($koneksi, $_POST['jurusan_names'][$key]);
            
            if ($jurusan_id) {
                // Update existing jurusan
                $updateQuery = $koneksi->prepare("UPDATE jurusan SET nama_jurusan=? WHERE jurusan_id=?");
                $updateQuery->bind_param("si", $jurusan_name, $jurusan_id);
                $updateQuery->execute();
                if ($updateQuery->affected_rows > 0) {
                    echo "<script>alert('Jurusan updated successfully.');</script>";
                } else {
                    echo "<script>alert('Failed to update jurusan.');</script>";
                }
            } else {
                // Insert new jurusan
                $insertQuery = $koneksi->prepare("INSERT INTO jurusan (nama_jurusan) VALUES (?)");
                $insertQuery->bind_param("s", $jurusan_name);
                $insertQuery->execute();
                if ($insertQuery->affected_rows > 0) {
                    echo "<script>alert('Jurusan added successfully.');</script>";
                } else {
                    echo "<script>alert('Failed to add jurusan.');</script>";
                }
            }
        }
    }

    // Update Program Studi
    if (isset($_POST['update_program_studi'])) {
        foreach ($_POST['program_studi_ids'] as $key => $program_studi_id) {
            $program_studi_name = mysqli_real_escape_string($koneksi, $_POST['program_studi_names'][$key]);
            $jurusan_id = mysqli_real_escape_string($koneksi, $_POST['program_studi_jurusan_ids'][$key]);
            
            if ($program_studi_id) {
                // Update existing program studi
                $updateQuery = $koneksi->prepare("UPDATE program_studi SET nama_program_studi=?, jurusan_id=? WHERE program_studi_id=?");
                $updateQuery->bind_param("sii", $program_studi_name, $jurusan_id, $program_studi_id);
                $updateQuery->execute();
                if ($updateQuery->affected_rows > 0) {
                    echo "<script>alert('Program Studi updated successfully.');</script>";
                } else {
                    echo "<script>alert('Failed to update Program Studi.');</script>";
                }
            } else {
                // Insert new program studi
                $insertQuery = $koneksi->prepare("INSERT INTO program_studi (nama_program_studi, jurusan_id) VALUES (?, ?)");
                $insertQuery->bind_param("si", $program_studi_name, $jurusan_id);
                $insertQuery->execute();
                if ($insertQuery->affected_rows > 0) {
                    echo "<script>alert('Program Studi added successfully.');</script>";
                } else {
                    echo "<script>alert('Failed to add Program Studi.');</script>";
                }
            }
        }
    }

    // Delete Jurusan
    if (isset($_POST['delete_jurusan_id'])) {
        $jurusan_id = mysqli_real_escape_string($koneksi, $_POST['delete_jurusan_id']);
        $deleteQuery = $koneksi->prepare("DELETE FROM jurusan WHERE jurusan_id = ?");
        $deleteQuery->bind_param("i", $jurusan_id);
        if ($deleteQuery->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete jurusan']);
        }
        exit();
    }

    // Delete Program Studi
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

    // Redirect to avoid resubmission (only for non-AJAX requests)
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch current jurusan options
$jurusan_query = "SELECT * FROM jurusan ORDER BY nama_jurusan";
$jurusan_result = mysqli_query($koneksi, $jurusan_query);
$jurusan_options = [];
while ($jurusan = mysqli_fetch_assoc($jurusan_result)) {
    $jurusan_options[] = $jurusan;
}

// Fetch current program studi options
$prodi_query = "SELECT * FROM program_studi ORDER BY nama_program_studi";
$prodi_result = mysqli_query($koneksi, $prodi_query);
$prodi_options = [];
while ($prodi = mysqli_fetch_assoc($prodi_result)) {
    $prodi_options[] = $prodi;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Struktur Tabel</title>
    
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <style>
    .main-content {
        margin-left: 250px;
        padding: 20px;
        flex: 1;
    }

    .structure-container {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin: 20px;
        width: calc(100% - 40px);
    }

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

    .table-structure input[type="text"],
    .table-structure select {
        width: calc(100% - 24px);
        padding: 6px;
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

    .dropdown-management {
        margin-top: 40px;
        padding-top: 20px;
        border-top: 2px solid #dee2e6;
    }

    .dropdown-section {
        margin-bottom: 30px;
    }

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

    <input type="checkbox" id="toggle">
    <label for="toggle" class="side-toggle">&#9776;</label>

    <!-- Sidebar -->
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

    <div class="main-content">
        <div class="structure-container">
            <h2>Kelola Jurusan dan Program Studi</h2>

            <!-- Table for Jurusan -->
            <div class="dropdown-section">
                <h4>Jurusan Options</h4>
                <table class="options-table">
                    <thead>
                        <tr>
                            <th>Jurusan Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="jurusanOptions">
                        <?php foreach ($jurusan_options as $row): ?>
                            <tr id="jurusan-<?php echo $row['jurusan_id']; ?>">
                                <td>
                                    <input type="text" name="jurusan_names[]" value="<?php echo htmlspecialchars($row['nama_jurusan']); ?>">
                                    <input type="hidden" name="jurusan_ids[]" value="<?php echo $row['jurusan_id']; ?>">
                                </td>
                                <td>
                                    <button type="button" class="btn-rename" onclick="renameJurusan(this)">Rename</button>
                                    <form method="POST" action="kelola_jurusan.php" style="display:inline;" onsubmit="return false;">
                                        <input type="hidden" name="delete_jurusan_id" value="<?php echo $row['jurusan_id']; ?>">
                                        <button type="submit" class="btn-delete">Delete</button>
                                    </form>
                                    <button type="submit" class="btn-save" name="update_jurusan" style="display:none;">Save</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Table for Program Studi -->
            <div class="dropdown-section">
                <h4>Program Studi Options</h4>
                <table class="options-table">
                    <thead>
                        <tr>
                            <th>Program Studi Name</th>
                            <th>Jurusan</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="programStudiOptions">
                        <?php foreach ($prodi_options as $row): ?>
                            <tr id="program_studi-<?php echo $row['program_studi_id']; ?>">
                                <td>
                                    <input type="text" name="program_studi_names[]" value="<?php echo htmlspecialchars($row['nama_program_studi']); ?>">
                                    <input type="hidden" name="program_studi_ids[]" value="<?php echo $row['program_studi_id']; ?>">
                                </td>
                                <td>
                                    <select name="program_studi_jurusan_ids[]">
                                        <?php foreach ($jurusan_options as $jurusan): ?>
                                            <option value="<?php echo $jurusan['jurusan_id']; ?>" <?php echo ($jurusan['jurusan_id'] == $row['jurusan_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($jurusan['nama_jurusan']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="btn-rename" onclick="renameProgramStudi(this)">Rename</button>
                                    <button type="button" class="btn-delete" onclick="deleteProgramStudi(<?php echo $row['program_studi_id']; ?>)">Delete</button>
                                    <button type="submit" class="btn-save" name="update_program_studi" style="display:none;">Save</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <script>
function deleteJurusan(jurusanId) {
    if (confirm('Are you sure you want to delete this Jurusan?')) {
        // Kirim permintaan penghapusan menggunakan AJAX
        const formData = new FormData();
        formData.append('delete_jurusan_id', jurusanId);

        fetch('views/administrator/kelola_jurusan.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())  // Pastikan respons diubah ke format JSON
        .then(data => {
            if (data.success) {
                // Hapus baris jurusan dari tampilan
                const row = document.getElementById(`jurusan-${jurusanId}`);
                row.remove();
            } else {
                alert('Failed to delete Jurusan: ' + data.message);
            }
        })
        .catch(error => alert('Error deleting jurusan: ' + error));
    }
}

function deleteProgramStudi(programStudiId) {
    if (confirm('Are you sure you want to delete this Program Studi?')) {
        // Kirim permintaan penghapusan menggunakan AJAX
        const formData = new FormData();
        formData.append('delete_program_studi_id', programStudiId);
        
        fetch('views/administrator/kelola_jurusan.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())  // Pastikan respons diubah ke format JSON
        .then(data => {
            if (data.success) {
                // Hapus baris program studi dari tampilan
                const row = document.getElementById(`program_studi-${programStudiId}`);
                row.remove();
            } else {
                alert('Failed to delete Program Studi: ' + data.message);
            }
        })
        .catch(error => alert('Error deleting program studi: ' + error));
    }
}
    </script>
</body>
</html>
