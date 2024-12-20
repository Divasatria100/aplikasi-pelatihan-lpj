document.addEventListener('DOMContentLoaded', function() {
    // Fungsi utama untuk menangani pengiriman form saat di-submit
    function handleSubmit(event) {
        event.preventDefault(); // Mencegah form melakukan submit default ke server
        
        // Membuat objek yang berisi semua data form yang akan dikirim
        const formData = {
            judulPelatihan: document.getElementById('judulPelatihan').value,        // Mengambil judul pelatihan dari input
            jenisPelatihan: document.querySelector('input[name="jenisPelatihan"]:checked')?.value,  // Mengambil jenis pelatihan yang dipilih
            namaPeserta: document.getElementById('namaPeserta').value,              // Mengambil nama peserta pelatihan
            lembaga: document.getElementById('lembaga').value,                      // Mengambil nama lembaga penyelenggara
            jurusan: document.getElementById('jurusan').value,                      // Mengambil jurusan peserta
            programStudi: document.getElementById('programStudi').value,            // Mengambil program studi peserta
            tanggalMulai: document.getElementById('tanggalMulai').value,           // Mengambil tanggal mulai pelatihan
            tanggalSelesai: document.getElementById('tanggalSelesai').value,       // Mengambil tanggal selesai pelatihan
            alamat: document.getElementById('alamat').value,                        // Mengambil alamat tempat pelatihan
            sumberDana: document.getElementById('sumberDana').value,                // Mengambil sumber dana pelatihan
            kompetensi: document.getElementById('kompetensi').value,                // Mengambil kompetensi yang diharapkan
            manajerPembimbing: document.getElementById('namaManajer').value,        // Mengambil nama manajer pembimbing
            target: document.getElementById('target').value,                        // Mengambil target/tujuan pelatihan
        };

        // Mengirim data form ke server menggunakan Fetch API
        fetch('/views/action/submit-usulan.php', {
            method: 'POST',                                    // Menggunakan metode POST untuk mengirim data
            headers: {
                'Content-Type': 'application/json',            // Menentukan tipe konten yang dikirim adalah JSON
            },
            body: JSON.stringify(formData),                    // Mengubah objek formData menjadi string JSON
        })
        .then(response => response.json())                     // Mengubah response dari server menjadi objek JSON
        .then(data => {
            if (data.success) {                               // Jika pengiriman berhasil
                alert('Usulan pelatihan berhasil dikirim!');  // Tampilkan pesan sukses
                window.location.href = '/views/user/dashboard-karyawan.php';  // Arahkan ke halaman dashboard
            } else {                                          // Jika pengiriman gagal
                alert('Gagal mengirim usulan pelatihan: ' + (data.error || 'Tidak ada detail error.')); // Tampilkan pesan error
            }
        })
        .catch(error => console.error('Error:', error));      // Menangkap dan menampilkan error jika terjadi masalah
    }

    // Menambahkan event listener untuk tombol toggle sidebar
    document.querySelector('.side-toggle').addEventListener('click', () => {
        document.querySelector('.sidebar').classList.toggle('active');    // Mengubah status aktif/nonaktif sidebar
    });

    // Fungsi untuk menangani pembatalan pengisian form
    function handleCancel() {
        if (confirm('Apakah Anda yakin ingin membatalkan pengisian form?')) {      // Konfirmasi pembatalan
            document.getElementById('trainingForm').reset();                        // Mengosongkan semua isian form
            window.location.href = "/views/dashboard/karyawan/dashboard-karyawan.php";  // Kembali ke halaman dashboard
        }
    }

    // Menambahkan event listener untuk form ketika di-submit
    document.getElementById('trainingForm').addEventListener('submit', handleSubmit);  // Menjalankan fungsi handleSubmit saat form disubmit
});
