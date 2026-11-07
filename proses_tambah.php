<?php
session_start();
if(!isset($_SESSION['login'])){
  header("Location: index.php");
  exit;
}
// ✅ Tambah cek hanya admin yang boleh
if($_SESSION['role'] != 'admin'){
  header("Location: peminjam_dashboard.php");
  exit;
}
include 'config/koneksi.php';

$judul = $_POST['judul'];
$kategori = $_POST['kategori'];
$penulis = $_POST['penulis'];
$tahun = $_POST['tahun'];
$stok = $_POST['stok'];

$nama_file = "";
if(!empty($_FILES['sampul']['name'])){
  $ext = strtolower(pathinfo($_FILES['sampul']['name'], PATHINFO_EXTENSION));
  $ukuran = $_FILES['sampul']['size'];
  $ext_izin = ['jpg','jpeg','png'];

  if(!in_array($ext, $ext_izin)){
    echo "<script>alert('Hanya jpg/jpeg/png!');history.back();</script>";
    exit;
  }
  if($ukuran > 2*1024*1024){
    echo "<script>alert('Maksimal 2MB!');history.back();</script>";
    exit;
  }

  $nama_file = time().".".$ext;
  move_uploaded_file($_FILES['sampul']['tmp_name'], "uploads/".$nama_file);
}

$sql = mysqli_query($conn, "INSERT INTO buku(judul,kategori,penulis,tahun_terbit,stok,sampul)
VALUES('$judul','$kategori','$penulis','$tahun','$stok','$nama_file')");

if($sql){
  header("Location: dashboard.php");
  exit;
}else{
  echo "Error: ".mysqli_error($conn);
}
?>