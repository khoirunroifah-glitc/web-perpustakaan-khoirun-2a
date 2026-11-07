<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // kosong untuk XAMPP bawaan
$db   = 'db_perpustakaan'; // nama database yang sudah kamu buat

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
  die("Koneksi gagal: " . mysqli_connect_error());
}

?>