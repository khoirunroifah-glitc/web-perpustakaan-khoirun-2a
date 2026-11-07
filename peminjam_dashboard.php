<?php
session_start();
if(!isset($_SESSION['login'])){
  header("Location: index.php");
  exit;
}
// Kalau admin masuk ke sini → arah ke dashboard admin
if($_SESSION['role'] != 'peminjam'){
  header("Location: dashboard.php");
  exit;
}
include 'config/koneksi.php';

$nama_peminjam = $_SESSION['username'];
$hari_ini = date('Y-m-d');

// Ambil peminjaman milik sendiri
$sql = mysqli_query($conn, "
SELECT p.*, b.judul, b.penulis, b.tahun_terbit
FROM peminjaman p
JOIN buku b ON p.id_buku = b.id_buku
WHERE p.nama_peminjam = '$nama_peminjam'
ORDER BY p.tanggal_pinjam DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard Peminjam</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
<h1>📖 Dashboard Peminjam</h1>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
  <p>Selamat Datang: <?php echo $_SESSION['username']; ?></p>
  <a href="logout.php" class="btn btn-logout">Logout</a>
</div>

<h3>📋 Peminjaman Milik Anda</h3>
<table>
<tr>
<th>Judul Buku</th>
<th>Penulis</th>
<th>Tanggal Pinjam</th>
<th>Batas Kembali</th>
<th>Status</th>
<th>Denda</th>
</tr>
<?php while($row = mysqli_fetch_assoc($sql)):
  $status = $row['status_pinjam'];
  $denda = 0;
  if($row['status_pinjam'] == 'Dipinjam' && $row['batas_kembali'] < $hari_ini){
    $selisih = strtotime($hari_ini) - strtotime($row['batas_kembali']);
    $hari = floor($selisih / (60*60*24));
    $denda = $hari * 1000; // Rp1.000 per hari
  }
?>
<tr>
<td><?php echo $row['judul']; ?></td>
<td><?php echo $row['penulis']; ?></td>
<td><?php echo $row['tanggal_pinjam']; ?></td>
<td><?php echo $row['batas_kembali']; ?></td>
<td><?php echo $status; ?></td>
<td>Rp<?php echo number_format($denda); ?></td>
</tr>
<?php endwhile; ?>
</table>

</div>
</body>
</html>