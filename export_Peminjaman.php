<?php
session_start();
if(!isset($_SESSION['login'])){
  header("Location: index.php");
}
include 'config/koneksi.php';

// Baca filter status dari URL
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$where = "";
if(!empty($filter_status)){
  $where = "WHERE p.status_pinjam = '$filter_status'";
}

// Query data
$sql = mysqli_query($conn, "
SELECT p.id_peminjaman, b.judul, p.nama_peminjam, p.tanggal_pinjam, p.batas_kembali, p.tanggal_kembali, p.status_pinjam, p.denda
FROM peminjaman p
JOIN buku b ON p.id_buku = b.id_buku
$where
ORDER BY p.id_peminjaman DESC
");

// Header untuk export CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="daftar_peminjaman_'.date('Ymd').'.csv"');

// Buka output sebagai file
$output = fopen('php://output', 'w');
fputcsv($output, array('ID Peminjaman','Judul Buku','Nama Peminjam','Tanggal Pinjam','Batas Kembali','Tanggal Kembali','Status','Denda (Rp)'));

while($row = mysqli_fetch_assoc($sql)){
  fputcsv($output, array(
    $row['id_peminjaman'],
    $row['judul'],
    $row['nama_peminjam'],
    $row['tanggal_pinjam'],
    $row['batas_kembali'],
    $row['tanggal_kembali'] ? $row['tanggal_kembali'] : 'Belum Kembali',
    $row['status_pinjam'],
    $row['denda']
  ));
}
fclose($output);
exit;
?>