<?php
// Menghubungkan ke database
$koneksi = mysqli_connect("localhost", "root", "", "db_ajukan");

// Cek apakah koneksi berhasil
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Memeriksa apakah ada parameter 'jurusan_id' yang dikirim melalui URL
if (isset($_GET['jurusan_id'])) {
    // Mengambil 'jurusan_id' dan memastikan input aman
    $jurusan_id = mysqli_real_escape_string($koneksi, $_GET['jurusan_id']);

    // Menyusun query untuk mengambil data program studi berdasarkan jurusan_id
    $query = "SELECT program_studi_id, nama_program_studi 
              FROM program_studi 
              WHERE jurusan_id = '$jurusan_id' 
              ORDER BY nama_program_studi";
    
    // Menjalankan query
    $result = mysqli_query($koneksi, $query);

    // Menyusun array untuk hasil
    $prodi_list = array();
    
    if ($result) {
        // Mengambil data hasil query dan memasukkan ke dalam array
        while ($row = mysqli_fetch_assoc($result)) {
            $prodi_list[] = $row;
        }

        // Mengatur header untuk JSON dan mengirimkan hasil sebagai JSON
        header('Content-Type: application/json');
        echo json_encode($prodi_list);
    } else {
        // Jika query gagal
        echo json_encode(["error" => "Data tidak ditemukan atau terjadi kesalahan dalam query."]);
    }
} else {
    // Jika 'jurusan_id' tidak diterima
    echo json_encode(["error" => "Jurusan ID tidak ditemukan."]);
}

// Menutup koneksi database
mysqli_close($koneksi);
?>
