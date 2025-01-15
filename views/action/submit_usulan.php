<?php
include 'db_connection.php'; // Mengimpor file koneksi database

// Memulai session PHP untuk memverifikasi login pengguna
session_start();

// Memeriksa apakah user sudah login dengan mengecek session session_nik
if (!isset($_SESSION['session_username'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") { // Cek apakah request yang diterima adalah POST

    // Mengambil data JSON dari body request dan mengubahnya menjadi array PHP
    $data = json_decode(file_get_contents("php://input"), true);

    // Memvalidasi kelengkapan data yang dikirim
    if (!isset($data['judulPelatihan'], 
            $data['jenisPelatihan'], 
            $data['namaPeserta'],
            $data['lembaga'],
            $data['tanggalMulai'],
            $data['tanggalSelesai'],
            $data['tempat'],
            $data['sumberDana'],
            $data['manajerPembimbing'],
            $data['target'],
            $data['jurusan'],
            $data['programStudi'])) {
        echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']); // Mengirim response error jika ada data yang kurang
        exit(); // Menghentikan eksekusi script
    }

    // Mengamankan data dari SQL injection dengan mysqli_real_escape_string
    $judulPelatihan = mysqli_real_escape_string($conn, $data['judulPelatihan']); // Mengamankan judul pelatihan
    $jenisPelatihan = mysqli_real_escape_string($conn, $data['jenisPelatihan']); // Mengamankan jenis pelatihan
    $namaPeserta = mysqli_real_escape_string($conn, $data['namaPeserta']); // Mengamankan nama peserta
    $lembaga = mysqli_real_escape_string($conn, $data['lembaga']); // Mengamankan nama lembaga
    $tanggalMulai = mysqli_real_escape_string($conn, $data['tanggalMulai']); // Mengamankan tanggal mulai
    $tanggalSelesai = mysqli_real_escape_string($conn, $data['tanggalSelesai']); // Mengamankan tanggal selesai
    $tempat = mysqli_real_escape_string($conn, $data['tempat']); // Mengamankan tempat
    $sumberDana = mysqli_real_escape_string($conn, $data['sumberDana']); // Mengamankan sumber dana
    $manajerPembimbing = mysqli_real_escape_string($conn, $data['manajerPembimbing']); // Mengamankan manajer pembimbing
    $target = mysqli_real_escape_string($conn, $data['target']); // Mengamankan target
    $jurusanId = mysqli_real_escape_string($conn, $data['jurusan']); // Mengamankan jurusan_id
    $programStudiId = mysqli_real_escape_string($conn, $data['programStudi']); // Mengamankan program_studi_id

    // Mengambil user_id dari session
    $user_id = $_SESSION['user_id']; // Dapatkan user_id dari session yang sudah ada

    // Menyiapkan query SQL untuk menyimpan data ke tabel usulan_pelatihan
    $query = "INSERT INTO usulan_pelatihan (
        judul_pelatihan, 
        jenis_pelatihan, 
        nama_peserta, 
        lembaga, 
        tanggal_mulai, 
        tanggal_selesai, 
        tempat, 
        sumber_dana, 
        manajer_pembimbing, 
        target, 
        user_id,
        status, 
        lpj_status,
        jurusan_id,
        program_studi_id
    ) VALUES (
        '$judulPelatihan',
        '$jenisPelatihan',
        '$namaPeserta',
        '$lembaga',
        '$tanggalMulai',
        '$tanggalSelesai',
        '$tempat',
        '$sumberDana',
        '$manajerPembimbing',
        '$target',
        '$user_id',
        'Sedang Ditinjau', 
        'Belum Diajukan',
        '$jurusanId',
        '$programStudiId'
    )";

    // Eksekusi query dan kirim response sesuai hasilnya
    if (mysqli_query($conn, $query)) { // Jika query berhasil
        echo json_encode(['success' => true, 'message' => 'Usulan pelatihan berhasil diajukan!']); // Kirim response sukses
    } else { // Jika query gagal
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]); // Kirim response error dengan detail kesalahan
    }
}
?>
