<?php
include 'db_connection.php';                                                   // Mengimpor file koneksi database

if ($_SERVER["REQUEST_METHOD"] == "POST") {                                   // Cek apakah request yang diterima adalah POST
    
    // Mengambil data JSON dari body request dan mengubahnya menjadi array PHP
    $data = json_decode(file_get_contents("php://input"), true);             

    // Memvalidasi kelengkapan data yang dikirim
    if (!isset($data['judulPelatihan'], 
            $data['jenisPelatihan'], 
            $data['namaPeserta'],
            $data['lembaga'],
            $data['jurusan'],
            $data['programStudi'],
            $data['tanggalMulai'],
            $data['tanggalSelesai'],
            $data['alamat'],
            $data['sumberDana'],
            $data['manajerPembimbing'],
            $data['target'])) {
        echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']); // Mengirim response error jika ada data yang kurang
        exit();                                                                  // Menghentikan eksekusi script
    }

    // Mengamankan data dari SQL injection dengan mysqli_real_escape_string
    $judulPelatihan = mysqli_real_escape_string($conn, $data['judulPelatihan']);        // Mengamankan judul pelatihan
    $jenisPelatihan = mysqli_real_escape_string($conn, $data['jenisPelatihan']);        // Mengamankan jenis pelatihan
    $namaPeserta = mysqli_real_escape_string($conn, $data['namaPeserta']);              // Mengamankan nama peserta
    $lembaga = mysqli_real_escape_string($conn, $data['lembaga']);                      // Mengamankan nama lembaga
    $jurusan = mysqli_real_escape_string($conn, $data['jurusan']);                      // Mengamankan jurusan
    $programStudi = mysqli_real_escape_string($conn, $data['programStudi']);            // Mengamankan program studi
    $tanggalMulai = mysqli_real_escape_string($conn, $data['tanggalMulai']);           // Mengamankan tanggal mulai
    $tanggalSelesai = mysqli_real_escape_string($conn, $data['tanggalSelesai']);       // Mengamankan tanggal selesai
    $alamat = mysqli_real_escape_string($conn, $data['alamat']);                        // Mengamankan alamat
    $sumberDana = mysqli_real_escape_string($conn, $data['sumberDana']);               // Mengamankan sumber dana
    $manajerPembimbing = mysqli_real_escape_string($conn, $data['manajerPembimbing']); // Mengamankan manajer pembimbing
    $target = mysqli_real_escape_string($conn, $data['target']);                        // Mengamankan target

    // Menyiapkan query SQL untuk menyimpan data ke tabel usulan_pelatihan
    $query = "INSERT INTO usulan_pelatihan (
        judulPelatihan, 
        jenisPelatihan, 
        namaPeserta, 
        lembaga, 
        jurusan, 
        programStudi, 
        tanggalMulai, 
        tanggalSelesai, 
        alamat, 
        sumberDana, 
        manajerPembimbing, 
        target
    ) VALUES (
        '$judulPelatihan',
        '$jenisPelatihan',
        '$namaPeserta',
        '$lembaga',
        '$jurusan',
        '$programStudi',
        '$tanggalMulai',
        '$tanggalSelesai',
        '$alamat',
        '$sumberDana',
        '$manajerPembimbing',
        '$target'
    )";

    // Eksekusi query dan kirim response sesuai hasilnya
    if (mysqli_query($conn, $query)) {                                        // Jika query berhasil
        echo json_encode(['success' => true]);                                // Kirim response sukses
    } else {                                                                  // Jika query gagal
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]); // Kirim response error dengan detail kesalahan
    }
}
?>