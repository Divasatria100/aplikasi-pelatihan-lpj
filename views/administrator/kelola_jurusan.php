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

// Get admin_id from session
$admin_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // Update Jurusan
    if (isset($_POST['update_jurusan'])) {
        foreach ($_POST['jurusan_ids'] as $key => $jurusan_id) {
            $jurusan_name = mysqli_real_escape_string($koneksi, $_POST['jurusan_names'][$key]);
            
            if ($jurusan_id) {
                // Update existing jurusan
                $updateQuery = $koneksi->prepare("UPDATE jurusan SET nama_jurusan=?, admin_id=? WHERE jurusan_id=?");
                $updateQuery->bind_param("sii", $jurusan_name, $admin_id, $jurusan_id);
                $updateQuery->execute();
                if ($updateQuery->affected_rows > 0) {
                    echo "<script>alert('Jurusan updated successfully.');</script>";
                } else {
                    echo "<script>alert('Failed to update jurusan.');</script>";
                }
            } else {
                // Insert new jurusan with admin_id
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

    // Update Program Studi
    if (isset($_POST['update_program_studi'])) {
        foreach ($_POST['program_studi_ids'] as $key => $program_studi_id) {
            $program_studi_name = mysqli_real_escape_string($koneksi, $_POST['program_studi_names'][$key]);
            $jurusan_id = mysqli_real_escape_string($koneksi, $_POST['program_studi_jurusan_ids'][$key]);
            
            if ($program_studi_id) {
                // Update existing program studi
                $updateQuery = $koneksi->prepare("UPDATE program_studi SET nama_program_studi=?, jurusan_id=?, admin_id=? WHERE program_studi_id=?");
                $updateQuery->bind_param("siii", $program_studi_name, $jurusan_id, $admin_id, $program_studi_id);
                $updateQuery->execute();
                if ($updateQuery->affected_rows > 0) {
                    echo "<script>alert('Program Studi updated successfully.');</script>";
                } else {
                    echo "<script>alert('Failed to update Program Studi.');</script>";
                }
            } else {
                // Insert new program studi with admin_id
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

    // Delete Jurusan
    if (isset($_POST['delete_jurusan_id'])) {
        $jurusan_id = mysqli_real_escape_string($koneksi, $_POST['delete_jurusan_id']);

        // Begin transaction
        $koneksi->begin_transaction();

        try {
            // Delete related program_studi records
            $deleteProgramStudiQuery = $koneksi->prepare("DELETE FROM program_studi WHERE jurusan_id = ?");
            $deleteProgramStudiQuery->bind_param("i", $jurusan_id);
            $deleteProgramStudiQuery->execute();

            // Delete jurusan record
            $deleteJurusanQuery = $koneksi->prepare("DELETE FROM jurusan WHERE jurusan_id = ?");
            $deleteJurusanQuery->bind_param("i", $jurusan_id);
            $deleteJurusanQuery->execute();

            // Commit transaction
            $koneksi->commit();

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $koneksi->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to delete jurusan and related program studi']);
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

// Save handler
if(isset($_POST['save_program_studi'])) {
    if(isset($_POST['prodi_names']) && isset($_POST['jurusan_ids'])) {
        $names = $_POST['prodi_names'];
        $jurusan_ids = $_POST['jurusan_ids'];
        
        foreach($names as $key => $nama_program_studi) {
            if(!empty(trim($nama_program_studi)) && !empty($jurusan_ids[$key])) {
                $nama_program_studi = mysqli_real_escape_string($koneksi, trim($nama_program_studi));
                $jurusan_id = mysqli_real_escape_string($koneksi, $jurusan_ids[$key]);
                
                $query = "INSERT INTO program_studi (nama_program_studi, jurusan_id, admin_id) 
                         VALUES ('$nama_program_studi', '$jurusan_id', '$admin_id')";
                mysqli_query($koneksi, $query);
            }
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Delete handler
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

    .btn-save {
        margin-top: 10px;
        padding: 8px 16px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-danger {
        background: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        margin: 0 5px;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-success {
        background: #28a745;
        color: white;
        border: none;
        padding: 5px 10px;
        margin: 0 5px;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-primary {
        background: #007bff;
        color: white;
        border: none;
        padding: 8px 16px;
        margin-top: 10px;
        border-radius: 4px;
        cursor: pointer;
    }

    select {
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        width: 100%;
        box-sizing: border-box;
    }

    .records-section {
        margin-top: 30px;
    }

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
                <form id="jurusanForm" method="POST">
                    <table class="options-table">
                        <thead>
                            <tr>
                                <th>Jurusan Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="jurusanOptions">
                            <tr>
                                <td><input type="text" name="jurusan_names[]" required></td>
                                <td>
                                    <button type="button" class="btn-danger" onclick="deleteJurusanRow(this)">Delete</button>
                                    <button type="button" class="btn-success" onclick="addJurusanRow()">Add</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="submit" name="save_jurusan" class="btn-primary">Save All Jurusan</button>
                </form>
            </div>

            <!-- Table for Program Studi -->
            <div class="dropdown-section">
                <h4>Program Studi Options</h4>
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
                            <tr>
                                <td><input type="text" name="prodi_names[]" required></td>
                                <td>
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
                                    <button type="button" class="btn-danger" onclick="deleteProdiRow(this)">Delete</button>
                                    <button type="button" class="btn-success" onclick="addProdiRow()">Add</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary">Save All Program Studi</button>
                </form>
            </div>

            <!-- Add this after your existing forms -->

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
                        $query = "SELECT * FROM jurusan ORDER BY jurusan_id";
                        $result = mysqli_query($koneksi, $query);
                        $no = 1;
                        while($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $no . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_jurusan']) . "</td>";
                            echo "<td>";
                            echo "<button class='btn-warning' onclick='editJurusan(" . $row['jurusan_id'] . ", \"" . htmlspecialchars($row['nama_jurusan']) . "\")'>Edit</button>";
                            echo "<button class='btn-danger' onclick='deleteJurusan(" . $row['jurusan_id'] . ")'>Delete</button>";
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
                        // Modified query to ensure unique records
                        $query = "SELECT ps.program_studi_id, ps.nama_program_studi, j.nama_jurusan 
                                  FROM program_studi ps 
                                  LEFT JOIN jurusan j ON ps.jurusan_id = j.jurusan_id 
                                  ORDER BY ps.program_studi_id";
                        
                        $result = mysqli_query($koneksi, $query);
                        $no = 1;
                        while($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $no . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_program_studi']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_jurusan']) . "</td>";
                            echo "<td>";
                            echo "<button class='btn-warning' onclick='editProdi(" . $row['program_studi_id'] . ", \"" . htmlspecialchars($row['nama_program_studi']) . "\")'>Edit</button>";
                            echo "<button class='btn-danger' onclick='deleteProdiRecord(" . $row['program_studi_id'] . ")'>Delete</button>";
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
    // Jurusan functions
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

    function deleteJurusanRow(button) {
        const row = button.closest('tr');
        const tbody = row.parentElement;
        
        if (tbody.children.length <= 1) {
            alert('Cannot delete the last row');
            return;
        }
        
        row.remove();
        
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

    // Program Studi functions
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

    function deleteProdiRow(button) {
        const row = button.closest('tr');
        const tbody = row.parentElement;
        
        if (tbody.children.length <= 1) {
            alert('Cannot delete the last row');
            return;
        }
        
        row.remove();
        
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

    function editJurusan(id, name) {
        const newName = prompt("Edit Jurusan Name:", name);
        if (newName && newName !== name) {
            if (confirm("Are you sure you want to update this jurusan?")) {
                const formData = new FormData();
                formData.append('edit_jurusan', '1');
                formData.append('jurusan_id', id);
                formData.append('nama_jurusan', newName);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(() => location.reload());
            }
        }
    }

    function confirmDeleteJurusan(id) {
        if (confirm("Warning: Deleting this jurusan will also delete all related program studi. Are you sure you want to continue?")) {
            const formData = new FormData();
            formData.append('delete_jurusan', '1');
            formData.append('jurusan_id', id);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(result => {
                try {
                    const data = JSON.parse(result);
                    if (data.success) {
                        document.location = document.location.href;
                    }
                } catch (e) {
                    document.location = document.location.href;
                }
            })
            .catch(error => {
                document.location = document.location.href;
            });
        }
    }

    function editProdi(id, name, jurusanId) {
        const newName = prompt("Edit Program Studi Name:", name);
        if (newName && newName !== name) {
            if (confirm("Are you sure you want to update this program studi?")) {
                const formData = new FormData();
                formData.append('edit_prodi', '1');
                formData.append('prodi_id', id);
                formData.append('nama_prodi', newName);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(() => location.reload());
            }
        }
    }

    function deleteProdiRecord(id) {
        if (confirm("Are you sure you want to delete this program studi?")) {
            const formData = new FormData();
            formData.append('delete_prodi', '1');
            formData.append('prodi_id', id);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(() => {
                window.location.href = window.location.href;
            });
        }
    }

    // Add this function for saving
    function saveAllProgramStudi() {
        document.getElementById('formProgramStudi').submit();
    }

    // Add these JavaScript functions
    function deleteAllJurusan() {
        if (confirm("Warning: This will delete ALL jurusan and their associated program studi. Are you sure?")) {
            const formData = new FormData();
            formData.append('delete_all_jurusan', '1');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(() => {
                window.location.reload();
            });
        }
    }

    function deleteAllProdi() {
        if (confirm("Warning: This will delete ALL program studi records. Are you sure?")) {
            const formData = new FormData();
            formData.append('delete_all_prodi', '1');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(() => {
                window.location.reload();
            });
        }
    }

    // Individual delete functions
    function deleteJurusan(id) {
        if (confirm("Are you sure you want to delete this jurusan?")) {
            const formData = new FormData();
            formData.append('delete_jurusan', '1');
            formData.append('jurusan_id', id);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(() => {
                window.location.reload();
            });
        }
    }

    function deleteProdiRecord(id) {
        if (confirm("Are you sure you want to delete this program studi?")) {
            const formData = new FormData();
            formData.append('delete_prodi', '1');
            formData.append('prodi_id', id);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(() => {
                window.location.reload();
            });
        }
    }
    </script>

    <?php
    // Save Jurusan
    if(isset($_POST['save_jurusan'])) {
        if(isset($_POST['jurusan_names'])) {
            foreach($_POST['jurusan_names'] as $nama_jurusan) {
                if(!empty(trim($nama_jurusan))) {
                    // Check if this jurusan already exists
                    $nama_jurusan = mysqli_real_escape_string($koneksi, trim($nama_jurusan));
                    $check_query = "SELECT COUNT(*) as count FROM jurusan WHERE nama_jurusan = '$nama_jurusan'";
                    $check_result = mysqli_query($koneksi, $check_query);
                    $row = mysqli_fetch_assoc($check_result);
                    
                    // Only insert if it doesn't exist
                    if($row['count'] == 0) {
                $query = "INSERT INTO jurusan (nama_jurusan, admin_id) VALUES ('$nama_jurusan', '$admin_id')";
                        mysqli_query($koneksi, $query);
                    }
                }
            }
            echo "<script>alert('Jurusan saved successfully!'); location.href = location.href;</script>";
            exit();
        }
    }

    // Save Program Studi with jurusan relationship
    if(isset($_POST['save_prodi'])) {
        if(isset($_POST['prodi_names']) && isset($_POST['jurusan_ids'])) {
            $prodi_names = $_POST['prodi_names'];
            $jurusan_ids = $_POST['jurusan_ids'];
            
            for($i = 0; $i < count($prodi_names); $i++) {
                if(!empty(trim($prodi_names[$i])) && !empty($jurusan_ids[$i])) {
                    $nama_prodi = mysqli_real_escape_string($koneksi, trim($prodi_names[$i]));
                    $jurusan_id = mysqli_real_escape_string($koneksi, $jurusan_ids[$i]);
                    
                    $query = "INSERT INTO program_studi (nama_program_studi, jurusan_id) 
                             VALUES ('$nama_prodi', '$jurusan_id')";
                    mysqli_query($koneksi, $query);
                }
            }
            echo "<script>alert('Program Studi saved successfully!');</script>";
        }
    }

    // Add these PHP handlers at the end of your file

    if(isset($_POST['edit_jurusan'])) {
        $id = mysqli_real_escape_string($koneksi, $_POST['jurusan_id']);
        $name = mysqli_real_escape_string($koneksi, $_POST['nama_jurusan']);
        $query = "UPDATE jurusan SET nama_jurusan = '$name' WHERE jurusan_id = '$id'";
        mysqli_query($koneksi, $query);
        exit;
    }

    if(isset($_POST['delete_jurusan'])) {
        $id = mysqli_real_escape_string($koneksi, $_POST['jurusan_id']);
        
        mysqli_begin_transaction($koneksi);
        
        try {
            // Delete related program studi first
            $delete_prodi = "DELETE FROM program_studi WHERE jurusan_id = '$id'";
            mysqli_query($koneksi, $delete_prodi);
            
            // Then delete the jurusan
            $delete_jurusan = "DELETE FROM jurusan WHERE jurusan_id = '$id'";
            mysqli_query($koneksi, $delete_jurusan);
            
            mysqli_commit($koneksi);
            
            die(json_encode(['success' => true]));
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            die(json_encode(['success' => false, 'message' => $e->getMessage()]));
        }
    }

    if(isset($_POST['edit_prodi'])) {
        $id = mysqli_real_escape_string($koneksi, $_POST['prodi_id']);
        $name = mysqli_real_escape_string($koneksi, $_POST['nama_prodi']);
        $query = "UPDATE program_studi SET nama_program_studi = '$name' WHERE program_studi_id = '$id'";
        mysqli_query($koneksi, $query);
        exit;
    }

    if(isset($_POST['delete_prodi'])) {
        $id = mysqli_real_escape_string($koneksi, $_POST['prodi_id']);
        
        try {
            $query = "DELETE FROM program_studi WHERE program_studi_id = '$id'";
            mysqli_query($koneksi, $query);
            exit;
        } catch (Exception $e) {
            exit;
        }
    }

    if(isset($_POST['delete_all_jurusan'])) {
        mysqli_begin_transaction($koneksi);
        try {
            // Delete all program studi first (due to foreign key)
            mysqli_query($koneksi, "DELETE FROM program_studi");
            // Then delete all jurusan
            mysqli_query($koneksi, "DELETE FROM jurusan");
            mysqli_commit($koneksi);
            exit();
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            exit();
        }
    }

    if(isset($_POST['delete_all_prodi'])) {
        try {
            mysqli_query($koneksi, "DELETE FROM program_studi");
            exit();
        } catch (Exception $e) {
            exit();
        }
    }

    // Prevent automatic form submission after deletion
    if($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Clear any stored form data
        unset($_SESSION['form_data']);
    }
    ?>
</body>
</html>
