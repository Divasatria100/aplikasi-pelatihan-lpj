<?php 
session_start(); // Memulai session PHP

// Menghapus semua data session
session_unset(); // Mengosongkan variabel session
session_destroy(); // Menghentikan session

// Menghapus cookie login
setcookie('cookie_username', "", time() - 3600, "/"); // Menghapus cookie username
setcookie('cookie_password', "", time() - 3600, "/"); // Menghapus cookie password

// Redirect ke halaman login
header("Location: /views/landing_page/landing_page.php");
exit();
