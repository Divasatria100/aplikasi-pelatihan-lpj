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
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi form
    if (empty($new_password) || empty($confirm_password)) {
        echo "Semua kolom harus diisi.";
        exit();
    }

    if ($new_password !== $confirm_password) {
        echo "Password baru dan konfirmasi password tidak cocok.";
        exit();
    }

    // Memeriksa password lama
    $query = "SELECT user.password FROM user WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Mengupdate password baru
    $hashed_password = md5($new_password); // Hashing menggunakan MD5
    $update_query = "UPDATE user SET password = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("si", $hashed_password, $user_id);

    if ($update_stmt->execute()) {
echo "<script>alert('Password berhasil diperbarui.'); window.location.href = 'identitas.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat memperbarui password.');</script>";
    }
}
?>
