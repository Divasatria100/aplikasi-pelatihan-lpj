<?php
// ===== Konfigurasi Database =====
$servername = "localhost";    // Alamat server database, localhost berarti database ada di komputer lokal
$username   = "root";         // Nama pengguna database MySQL, root adalah default untuk pengembangan lokal
$password   = "";            // Kata sandi database MySQL, dikosongkan untuk pengembangan lokal
$dbname     = "db_ajukan";       // Nama database yang akan digunakan untuk aplikasi

// ===== Membuat Koneksi =====
$conn = mysqli_connect($servername, $username, $password, $dbname);    // Mencoba menghubungkan ke database menggunakan fungsi mysqli_connect

// ===== Pengecekan Koneksi =====
if (!$conn) {    // Jika koneksi gagal ($conn bernilai false)
    die("Connection failed: " . mysqli_connect_error());    // Hentikan program dan tampilkan pesan error koneksi
}
?>