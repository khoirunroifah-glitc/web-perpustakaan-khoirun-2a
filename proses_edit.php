<?php
session_start();
if(!isset($_SESSION['login'])){
  header("Location: index.php");
  exit;
}
// ✅ Tambah cek hanya admin yang boleh akses
if($_SESSION['role'] != 'admin'){
  header("Location: peminjam_dashboard.php");
  exit;
}
include 'config/koneksi.php';
$id = $_GET['id'];

$judul = $_POST['judul'];
$penulis = $_POST['penulis'];
$tahun_terbit = $_POST['tahun_terbit'];
$kategori = $_POST['kategori'];
$stok = $_POST['stok'];

// Cek apakah ada upload sampul baru
$sampul_baru = '';
if(isset($_FILES['sampul']['name']) && $_FILES['sampul']['name'] != ''){
  $nama_file = time()."_".$_FILES['sampul']['name'];
  $lokasi = "uploads/".$nama_file;
  move_uploaded_file($_FILES['sampul']['tmp_name'], $lokasi);
  $sampul_baru = $nama_file;

  $query = mysqli_query($conn, "UPDATE buku SET judul='$judul', penulis='$penulis', tahun_terbit='$tahun_terbit', kategori='$kategori', stok='$stok', sampul='$sampul_baru' WHERE id_buku='$id'");
}else{
  // Tanpa ganti sampul
  $query = mysqli_query($conn, "UPDATE buku SET judul='$judul', penulis='$penulis', tahun_terbit='$tahun_terbit', kategori='$kategori', stok='$stok' WHERE id_buku='$id'");
}

if($query){
  echo "<script>alert('✅ Edit Buku Berhasil!'); window.location='dashboard.php';</script>";
}else{
  echo "<script>alert('❌ Gagal Edit: ".mysqli_error($conn)."'); history.back();</script>";
}
?>