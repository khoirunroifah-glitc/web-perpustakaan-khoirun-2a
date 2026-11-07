<?php
session_start();
if(!isset($_SESSION['login'])){
  header("Location: index.php");
  exit;
}
include 'config/koneksi.php';

$id_buku = $_GET['id'];
$nama_peminjam = $_POST['nama_peminjam'];
$tanggal_pinjam = $_POST['tanggal_pinjam'];

// Hitung otomatis batas kembali = tanggal pinjam +7 hari
$batas_kembali = date('Y-m-d', strtotime("$tanggal_pinjam +7 days"));

// Cek stok terlebih dahulu
$cek_stok = mysqli_query($conn, "SELECT stok FROM buku WHERE id_buku='$id_buku'");
$buku = mysqli_fetch_assoc($cek_stok);

if($buku['stok'] <= 0){
  echo "<script>alert('❌ Stok sudah habis!'); history.back();</script>";
  exit;
}

// Simpan ke tabel peminjaman (ada kolom denda default 0)
$pinjam = mysqli_query($conn, "INSERT INTO peminjaman(id_buku, nama_peminjam, tanggal_pinjam, batas_kembali, status_pinjam, denda)
VALUES('$id_buku','$nama_peminjam','$tanggal_pinjam','$batas_kembali','Dipinjam',0)");

// Kurangi stok buku
$kurangi_stok = mysqli_query($conn, "UPDATE buku SET stok = stok - 1 WHERE id_buku='$id_buku'");

if($pinjam && $kurangi_stok){
  $pesan = "✅ Peminjaman Berhasil! Batas kembali: $batas_kembali";
  if($_SESSION['role'] == 'admin'){
    // Admin ke daftar peminjaman
    echo "<script>alert('$pesan'); window.location='daftar_peminjaman.php';</script>";
  }else{
    // Peminjam ke peminjam_dashboard.php
    echo "<script>alert('$pesan'); window.location='peminjam_dashboard.php';</script>";
  }
}else{
  echo "<script>alert('❌ Gagal: ".mysqli_error($conn)."'); history.back();</script>";
}
?>