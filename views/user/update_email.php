<?php
// Konfigurasi koneksi database
$host = "localhost";
$username = "root";
$password = "";
$database = "db_ajukan";

$conn = new mysqli($host, $username, $password, $database);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Memulai sesi
session_start();

// Memeriksa login
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Memproses permintaan POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_email = $_POST['email'];

    // Validasi email
    if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Email tidak valid.');</script>";
        exit();
    }

    // Mengupdate email baru
    $update_query = "UPDATE user SET email = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("si", $new_email, $user_id);

    if ($update_stmt->execute()) {
        echo "<script>alert('Email berhasil diperbarui.'); window.location.href = 'identitas.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat memperbarui email.');</script>";
    }
}
?>
