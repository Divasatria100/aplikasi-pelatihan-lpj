// Menambahkan event listener untuk tombol toggle sidebar
document.querySelector('.side-toggle').addEventListener('click', () => {
    document.querySelector('.sidebar').classList.toggle('active'); // Menambah/menghapus class 'active' pada sidebar untuk mengubah tampilan
});

// Fungsi untuk membatalkan pengisian form
function handleCancel() {
    // Menampilkan konfirmasi sebelum membatalkan
    if (confirm('Apakah Anda yakin ingin membatalkan pengisian form?')) {
        document.getElementById('trainingForm').reset(); // Mengosongkan semua field pada form
        window.location.href = "/views/dashboard/karyawan/dashboard-karyawan.php"; // Redirect ke halaman dashboard
    }
};

// Fungsi untuk mengirim form
function handleSubmit() {
    // Menampilkan konfirmasi sebelum mengirim
    if (confirm('Apakah Anda yakin ingin mengirim form dan kembali ke dashboard?')) {
        document.getElementById('trainingForm').submit(); // Mengirim data form
        window.location.href = "/views/dashboard/karyawan/dashboard-karyawan.php"; // Redirect ke halaman dashboard
    }
}