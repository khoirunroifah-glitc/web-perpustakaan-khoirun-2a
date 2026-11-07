<?php
session_start();
if(!isset($_SESSION['login'])){
  header("Location: index.php");
}
include 'config/koneksi.php';

// Daftar kategori (sama persis dengan tambah_buku.php)
$daftar_kategori = [
  'Pendidikan',
  'Sastra',
  'Teknologi',
  'Sejarah',
  'Sains',
  'Psikologi',
  'Ekonomi',
  'Bisnis'
];

$id = $_GET['id'];
$ambil = mysqli_query($conn, "SELECT * FROM buku WHERE id_buku='$id'");
$b = mysqli_fetch_assoc($ambil);
?>
<!DOCTYPE html>
<html>
<head>
<title>Edit Buku</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
<h1>✏️ Edit Buku</h1>
<form method="POST" action="proses_edit.php?id=<?php echo $id; ?>" enctype="multipart/form-data">
  <label>Judul Buku:</label>
  <input type="text" name="judul" value="<?php echo $b['judul']; ?>" required>

  <label>Penulis:</label>
  <input type="text" name="penulis" value="<?php echo $b['penulis']; ?>" required>

  <label>Tahun Terbit:</label>
  <input type="number" name="tahun_terbit" min="1900" max="2099" value="<?php echo $b['tahun_terbit']; ?>" required>

  <label>Kategori:</label>
  <select name="kategori" required>
    <option value="">-- Pilih Kategori --</option>
    <?php foreach($daftar_kategori as $kat): ?>
      <option value="<?php echo $kat; ?>" <?php if($b['kategori'] == $kat) echo "selected"; ?>>
        <?php echo $kat; ?>
      </option>
    <?php endforeach; ?>
  </select>

  <label>Stok:</label>
  <input type="number" name="stok" min="1" value="<?php echo $b['stok']; ?>" required>

  <label>Sampul Buku (ganti jika perlu):</label>
  <input type="file" name="sampul" accept="image/*">
  <?php if($b['sampul'] != ''): ?>
    <p>Sampul saat ini: <img src="uploads/<?php echo $b['sampul']; ?>" width="80"></p>
  <?php endif; ?>

  <button type="submit" class="btn btn-tambah">Update Buku</button>
</form>
<a href="dashboard.php" class="btn btn-edit">⬅️ Kembali ke Dashboard</a>
</div>
</body>
</html>