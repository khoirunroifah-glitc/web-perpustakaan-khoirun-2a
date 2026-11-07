<?php
session_start();
if(!isset($_SESSION['login'])){
  header("Location: index.php");
}
include 'config/koneksi.php';

// ========== BARU: Filter Status ==========
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$where = "";
if(!empty($filter_status)){
  $where = "WHERE p.status_pinjam = '$filter_status'";
}

// ========== BARU: Pagination ==========
$per_halaman = 5;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$mulai = ($halaman - 1) * $per_halaman;

// Hitung total data
$total_sql = mysqli_query($conn, "SELECT COUNT(*) AS total FROM peminjaman p $where");
$total = mysqli_fetch_assoc($total_sql)['total'];
$total_halaman = ceil($total / $per_halaman);

// Query utama
$sql = mysqli_query($conn, "
SELECT p.*, b.judul 
FROM peminjaman p 
JOIN buku b ON p.id_buku = b.id_buku 
$where
ORDER BY p.id_peminjaman DESC
LIMIT $mulai, $per_halaman
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Daftar Peminjaman</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
<h1>📋 Daftar Peminjaman Buku</h1>

<!-- ========== BARU: Form Filter Status ========== -->
<form method="GET" action="daftar_peminjaman.php" style="display:flex; gap:8px; align-items:center; margin-bottom:15px; flex-wrap:wrap;">
  <select name="status" style="padding:10px; border-radius:10px; border:1px solid #e9b8d0;">
    <option value="">-- Semua Status --</option>
    <option value="Dipinjam" <?php if($filter_status == 'Dipinjam') echo "selected"; ?>>Dipinjam</option>
    <option value="Sudah Kembali" <?php if($filter_status == 'Sudah Kembali') echo "selected"; ?>>Sudah Kembali</option>
  </select>
  <button type="submit" class="btn btn-tambah">Terapkan</button>
  <a href="daftar_peminjaman.php" class="btn btn-edit">Reset</a>
</form>

<a href="export_peminjaman.php?status=<?php echo urlencode($filter_status); ?>" class="btn btn-tambah">📥 Export ke Excel (CSV)</a>
<a href="dashboard.php" class="btn btn-edit">⬅️ Kembali ke Dashboard</a>

<table>
<tr>
<th>No</th>
<th>Judul Buku</th>
<th>Nama Peminjam</th>
<th>Tanggal Pinjam</th>
<th>Batas Kembali</th>
<th>Tanggal Kembali</th>
<th>Status</th>
<th>Denda</th>
<th>Aksi</th>
</tr>
<?php
$no = $mulai + 1;
while($p = mysqli_fetch_assoc($sql)){
?>
<tr>
<td><?php echo $no++; ?></td>
<td><?php echo $p['judul']; ?></td>
<td><?php echo $p['nama_peminjam']; ?></td>
<td><?php echo $p['tanggal_pinjam']; ?></td>
<td><?php echo $p['batas_kembali']; ?></td>
<td><?php echo $p['tanggal_kembali'] ? $p['tanggal_kembali'] : 'Belum Kembali'; ?></td>
<td><?php echo $p['status_pinjam']; ?></td>
<td>Rp<?php echo number_format($p['denda']); ?></td>
<td>
  <?php if($p['status_pinjam'] == 'Dipinjam'): ?>
    <a href="proses_kembali.php?id=<?php echo $p['id_peminjaman']; ?>" class="btn btn-tambah" onclick="return confirm('Yakin buku sudah dikembalikan?')">Kembalikan</a>
  <?php else: ?>
    Sudah Kembali
  <?php endif; ?>
</td>
</tr>
<?php } ?>
</table>

<!-- ========== BARU: Navigasi Pagination ========== -->
<div style="margin-top:15px; text-align:center;">
  <?php if($halaman > 1): ?>
    <a href="daftar_peminjaman.php?halaman=<?php echo $halaman-1; ?>&status=<?php echo urlencode($filter_status); ?>" class="btn btn-edit">⬅️ Sebelumnya</a>
  <?php endif; ?>

  <span style="margin:0 10px;">Halaman <?php echo $halaman; ?> dari <?php echo $total_halaman; ?> (Total: <?php echo $total; ?> data)</span>

  <?php if($halaman < $total_halaman): ?>
    <a href="daftar_peminjaman.php?halaman=<?php echo $halaman+1; ?>&status=<?php echo urlencode($filter_status); ?>" class="btn btn-tambah">Selanjutnya ➡️</a>
  <?php endif; ?>
</div>

</div>
</body>
</html>