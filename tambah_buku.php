<?php
session_start();
if(!isset($_SESSION['login'])){
  header("Location: index.php");
}
include 'config/koneksi.php';

// Daftar kategori yang tersedia
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

if($_SERVER['REQUEST_METHOD'] == 'POST'){
  $judul = $_POST['judul'];
  $penulis = $_POST['penulis'];
  $tahun_terbit = $_POST['tahun_terbit'];
  $kategori = $_POST['kategori'];
  $stok = $_POST['stok'];

  // Upload sampul
  $sampul = '';
  if(isset($_FILES['sampul']['name']) && $_FILES['sampul']['name'] != ''){
    $nama_file = time()."_".$_FILES['sampul']['name'];
    $lokasi = "uploads/".$nama_file;
    move_uploaded_file($_FILES['sampul']['tmp_name'], $lokasi);
    $sampul = $nama_file;
  }

  $query = mysqli_query($conn, "INSERT INTO buku(judul, penulis, tahun_terbit, kategori, stok, sampul)
  VALUES('$judul','$penulis','$tahun_terbit','$kategori','$stok','$sampul')");

  if($query){
    echo "<script>alert('✅ Buku berhasil ditambahkan!'); window.location='dashboard.php';</script>";
  }else{
    echo "<script>alert('❌ Gagal: ".mysqli_error($conn)."'); history.back();</script>";
  }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Tambah Buku</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
<h1>➕ Tambah Buku Baru</h1>
<form method="POST" action="tambah_buku.php" enctype="multipart/form-data">
  <label>Judul Buku:</label>
  <input type="text" name="judul" required>

  <label>Penulis:</label>
  <input type="text" name="penulis" required>

  <label>Tahun Terbit:</label>
  <input type="number" name="tahun_terbit" min="1900" max="2099" required>

  <label>Kategori:</label>
  <select name="kategori" required>
    <option value="">-- Pilih Kategori --</option>
    <?php foreach($daftar_kategori as $kat): ?>
      <option value="<?php echo $kat; ?>"><?php echo $kat; ?></option>
    <?php endforeach; ?>
  </select>

  <label>Stok:</label>
  <input type="number" name="stok" min="1" required>

  <label>Sampul Buku (opsional):</label>
  <input type="file" name="sampul" accept="image/*">

  <button type="submit" class="btn btn-tambah">Simpan Buku</button>
</form>
<a href="dashboard.php" class="btn btn-edit">⬅️ Kembali ke Dashboard</a>
</div>
</body>
</html>