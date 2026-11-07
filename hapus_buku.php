<?php
session_start();
if(!isset($_SESSION['login'])){
  header("Location: index.php");
  exit;
}
// ✅ Tambah cek hanya admin
if($_SESSION['role'] != 'admin'){
  header("Location: peminjam_dashboard.php");
  exit;
}
include 'config/koneksi.php';
$id = $_GET['id'];

$ambil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT sampul FROM buku WHERE id_buku='$id'"));
if($ambil['sampul'] && file_exists("uploads/".$ambil['sampul'])){
  unlink("uploads/".$ambil['sampul']);
}

mysqli_query($conn, "DELETE FROM buku WHERE id_buku='$id'");
header("Location: dashboard.php");
exit;
?>