<?php
session_start();
if(!isset($_SESSION['login'])){
  header("Location: index.php");
}
include 'config/koneksi.php';
$id_peminjaman = $_GET['id'];
$hari_ini = date('Y-m-d');

// Ambil batas_kembali + id_buku dari data peminjaman
$ambil = mysqli_query($conn, "SELECT id_buku, batas_kembali FROM peminjaman WHERE id_peminjaman='$id_peminjaman'");
$data = mysqli_fetch_assoc($ambil);
$id_buku = $data['id_buku'];
$batas = $data['batas_kembali'];

$denda = 0;
// Hanya hitung kalau batas_kembali sudah terisi (data baru)
if($batas != NULL){
  $selisih = strtotime($hari_ini) - strtotime($batas);
  $hari_terlambat = floor($selisih / (60*60*24));
  if($hari_terlambat > 0){
    $denda = $hari_terlambat * 1000; // Rp1.000 per hari
  }
}

// Update peminjaman
$kembali = mysqli_query($conn, "UPDATE peminjaman SET tanggal_kembali='$hari_ini', status_pinjam='Sudah Kembali', denda='$denda' WHERE id_peminjaman='$id_peminjaman'");

// Tambah stok kembali
$tambah_stok = mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku='$id_buku'");

if($kembali && $tambah_stok){
  if($denda > 0){
    echo "<script>alert('✅ Pengembalian Berhasil! Terlambat $hari_terlambat hari, Denda: Rp$denda'); window.location='daftar_peminjaman.php';</script>";
  }else{
    echo "<script>alert('✅ Pengembalian Berhasil! Tidak ada denda'); window.location='daftar_peminjaman.php';</script>";
  }
}else{
  echo "<script>alert('❌ Gagal: ".mysqli_error($conn)."'); history.back();</script>";
}
?>