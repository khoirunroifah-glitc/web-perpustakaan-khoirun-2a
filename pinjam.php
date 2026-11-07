<?php
session_start();
if(!isset($_SESSION['login'])){
  header("Location: index.php");
  exit;
}
include 'config/koneksi.php';
$id_buku = $_GET['id'];
$ambil = mysqli_query($conn, "SELECT * FROM buku WHERE id_buku='$id_buku'");
$b = mysqli_fetch_assoc($ambil);
$is_admin = ($_SESSION['role'] == 'admin');
?>
<!DOCTYPE html>
<html>
<head>
<title>Pinjam Buku</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
<h1>📖 Form Peminjaman Buku</h1>
<h3>Judul: <?php echo $b['judul']; ?></h3>
<form method="POST" action="proses_pinjam.php?id=<?php echo $id_buku; ?>">
  <?php if($is_admin): ?>
    <label>Nama Peminjam (Admin isi manual):</label>
    <input type="text" name="nama_peminjam" required>
  <?php else: ?>
    <p><strong>Nama Peminjam (Otomatis dari akun):</strong> <?php echo $_SESSION['username']; ?></p>
    <input type="hidden" name="nama_peminjam" value="<?php echo $_SESSION['username']; ?>">
  <?php endif; ?>

  <label>Tanggal Pinjam:</label>
  <input type="date" name="tanggal_pinjam" value="<?php echo date('Y-m-d'); ?>" required>
  <p>💡 Batas kembali otomatis: 7 hari setelah tanggal pinjam</p>
  <button type="submit" class="btn btn-tambah">Proses Pinjam</button>
</form>
<a href="dashboard.php">⬅️ Kembali ke Dashboard</a>
</div>
</body>
</html>