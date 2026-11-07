<?php
session_start();
if(!isset($_SESSION['login'])){
  header("Location: index.php");
  exit;
}
// ✅ BARU: Cek Hanya Admin
if($_SESSION['role'] != 'admin'){
  header("Location: peminjam_dashboard.php");
  exit;
}
include 'config/koneksi.php';

// ==== STATISTIK BARU ====
$total_buku_sql = mysqli_query($conn, "SELECT SUM(stok) AS total FROM buku");
$total_buku = mysqli_fetch_assoc($total_buku_sql)['total'];

$terpinjam_sql = mysqli_query($conn, "SELECT COUNT(*) AS jumlah FROM peminjaman WHERE status_pinjam='Dipinjam'");
$terpinjam = mysqli_fetch_assoc($terpinjam_sql)['jumlah'];

$tersedia = $total_buku - $terpinjam;

// ========== BARU: Hitung Peminjaman Terlambat ==========
$hari_ini = date('Y-m-d');
$terlambat_sql = mysqli_query($conn, "
SELECT p.*, b.judul, p.nama_peminjam, p.batas_kembali
FROM peminjaman p
JOIN buku b ON p.id_buku = b.id_buku
WHERE p.status_pinjam = 'Dipinjam' AND p.batas_kembali < '$hari_ini'
ORDER BY p.batas_kembali ASC
");
$jumlah_terlambat = mysqli_num_rows($terlambat_sql);

// Simpan data terlambat ke array (penting agar tidak rusak pointer)
$terlambat_data = [];
while($row = mysqli_fetch_assoc($terlambat_sql)){
  $terlambat_data[] = $row;
}

// ==== Pencarian + Filter + Pagination (yang sudah ada) ====
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$filter_kategori = isset($_GET['filter_kategori']) ? $_GET['filter_kategori'] : '';

$buku_per_halaman = 5;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$mulai = ($halaman - 1) * $buku_per_halaman;

$where = "WHERE 1=1";
if(!empty($cari)){
  $where .= " AND (judul LIKE '%$cari%' OR penulis LIKE '%$cari%' OR kategori LIKE '%$cari%')";
}
if(!empty($filter_kategori)){
  $where .= " AND kategori = '$filter_kategori'";
}

$total_sql = mysqli_query($conn, "SELECT COUNT(*) AS total FROM buku $where");
$total = mysqli_fetch_assoc($total_sql)['total'];
$total_halaman = ceil($total / $buku_per_halaman);

$sql = mysqli_query($conn, "SELECT * FROM buku $where ORDER BY id_buku ASC LIMIT $mulai, $buku_per_halaman");

$kat_sql = mysqli_query($conn, "SELECT DISTINCT kategori FROM buku ORDER BY kategori ASC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard Mini Digital Library</title>
<link rel="stylesheet" href="style.css">
<!-- Tambah Chart JS di head -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container">
<h1>📚 Dashboard Mini Digital Library</h1>

<!-- Selamat Datang + Logout + Tambah Buku + Tombol Peminjam -->
<div class="top-vertikal" style="display:flex; flex-wrap:wrap; gap:8px; align-items:center; margin-bottom:10px;">
  <p style="margin:0;">Selamat Datang: <?php echo $_SESSION['username']; ?> | <a href="logout.php" class="btn btn-logout">Logout</a></p>
  <a href="tambah_buku.php" class="btn btn-tambah">➕ Tambah Buku</a>
  <a href="daftar_peminjaman.php?status=Dipinjam" class="btn btn-tambah">👥 Lihat Peminjam Aktif</a>
  <a href="daftar_peminjaman.php" class="btn btn-edit">📋 Semua Riwayat Peminjaman</a>
</div>

<!-- ✅ FORM PENCARIAN + FILTER KATEGORI -->
<form method="GET" action="dashboard.php" style="display:flex; gap:8px; align-items:center; margin-bottom:15px; flex-wrap:wrap;">
  <input type="text" name="cari" placeholder="🔍 Cari Judul / Penulis..." 
  style="padding:10px; border-radius:10px; border:1px solid #e9b8d0; flex-grow:1;"
  value="<?php echo isset($_GET['cari']) ? $_GET['cari'] : ''; ?>">

  <select name="filter_kategori" style="padding:10px; border-radius:10px; border:1px solid #e9b8d0;">
    <option value="">— Semua Kategori —</option>
    <?php while($k = mysqli_fetch_assoc($kat_sql)): ?>
      <option value="<?php echo $k['kategori']; ?>" <?php if($filter_kategori == $k['kategori']) echo "selected"; ?>>
        <?php echo $k['kategori']; ?>
      </option>
    <?php endwhile; ?>
  </select>

  <button type="submit" class="btn btn-tambah">Terapkan</button>
  <a href="dashboard.php" class="btn btn-edit">Reset</a>
</form>

<!-- ✅ BAGIAN STATISTIK KOTAK -->
<div style="display:flex; gap:15px; margin-bottom:15px; flex-wrap:wrap;">
  <div style="background:#fff; padding:15px; border-radius:12px; box-shadow:0 2px 8px #00000014; flex:1;">
    <h3>📚 Total Keseluruhan Buku</h3>
    <p style="font-size:24px; font-weight:bold;"><?php echo $total_buku; ?></p>
  </div>
  <div style="background:#fff; padding:15px; border-radius:12px; box-shadow:0 2px 8px #00000014; flex:1;">
    <h3>📖 Buku Sedang Terpinjam</h3>
    <p style="font-size:24px; font-weight:bold; color:#d63384;"><?php echo $terpinjam; ?></p>
  </div>
  <div style="background:#fff; padding:15px; border-radius:12px; box-shadow:0 2px 8px #00000014; flex:1;">
    <h3>✅ Buku Tersedia</h3>
    <p style="font-size:24px; font-weight:bold; color:#28a745;"><?php echo $tersedia; ?></p>
  </div>
</div>

<!-- ========== NOTIFIKASI TERLAMBAT (Diperbaiki Tidak Bertumpu) ========== -->
<?php if($jumlah_terlambat > 0): ?>
<div style="background:#ffe6ec; border:2px solid #dc3545; padding:15px; border-radius:12px; margin-bottom:15px; max-height:220px; overflow-y:auto;">
  <h3 style="color:#dc3545; margin:0 0 10px 0; font-size:18px;">⚠️ Peringatan: Ada <?php echo $jumlah_terlambat; ?> peminjaman yang sudah terlambat!</h3>
  <ul style="margin:0 0 10px 20px; padding:0; line-height:1.6;">
    <?php foreach($terlambat_data as $tl): ?>
      <li style="margin-bottom:4px;"><?php echo $tl['nama_peminjam']; ?> – <?php echo $tl['judul']; ?> (Batas: <?php echo $tl['batas_kembali']; ?>)</li>
    <?php endforeach; ?>
  </ul>
  <a href="daftar_peminjaman.php?status=Dipinjam" class="btn btn-hapus">Lihat Semua</a>
</div>
<?php else: ?>
<div style="background:#e8f8f0; border:2px solid #28a745; padding:15px; border-radius:12px; margin-bottom:15px;">
  <h3 style="color:#28a745; margin:0; font-size:18px;">✅ Tidak ada peminjaman yang terlambat</h3>
</div>
<?php endif; ?>

<!-- ✅ BAGIAN GRAFIK BARU -->
<div style="background:#fff; padding:20px; border-radius:12px; box-shadow:0 2px 8px #00000014; margin-bottom:15px;">
  <h3>📊 Grafik Statistik Buku</h3>
  <canvas id="chartStatistik" width="400" height="200"></canvas>
</div>

<script>
const ctx = document.getElementById('chartStatistik').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Total Buku', 'Sedang Terpinjam', 'Buku Tersedia'],
    datasets: [{
      label: 'Jumlah',
      data: [<?php echo $total_buku; ?>, <?php echo $terpinjam; ?>, <?php echo $tersedia; ?>],
      backgroundColor: ['#66b3ff','#ff6699','#57cc99']
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: {
        beginAtZero: true
      }
    }
  }
});
</script>

<!-- ✅ TABEL BUKU -->
<table>
  <tr>
    <th>No</th>
    <th>Judul</th>
    <th>Kategori</th>
    <th>Penulis</th>
    <th>Tahun</th>
    <th>Stok</th>
    <th>Status</th>
    <th>Sampul</th>
    <th>Aksi</th>
  </tr>
  <?php
  $no = $mulai + 1;
  while($b = mysqli_fetch_assoc($sql)){
    if($b['stok']>0){
      $status = "<span class='status-tersedia'>Tersedia</span>";
    }else{
      $status = "<span class='status-dipinjam'>Dipinjam</span>";
    }
  ?>
  <tr>
    <td><?php echo $no++; ?></td>
    <td><?php echo $b['judul']; ?></td>
    <td><?php echo $b['kategori']; ?></td>
    <td><?php echo $b['penulis']; ?></td>
    <td><?php echo $b['tahun_terbit']; ?></td>
    <td><?php echo $b['stok']; ?></td>
    <td><?php echo $status; ?></td>
    <td>
      <?php if($b['sampul']): ?>
        <img src="uploads/<?php echo $b['sampul']; ?>" width="60">
      <?php else: ?>
        Tidak ada
      <?php endif; ?>
    </td>
    <!-- Kolom Aksi -->
    <td class="aksi">
      <div class="aksi-wrap">
        <a href="edit_buku.php?id=<?php echo $b['id_buku']; ?>" class="btn btn-edit">Edit</a>
        <a href="pinjam.php?id=<?php echo $b['id_buku']; ?>" class="btn btn-tambah">Pinjam</a>
        <a href="hapus_buku.php?id=<?php echo $b['id_buku']; ?>" class="btn btn-hapus" onclick="return confirm('Yakin hapus?')">Hapus</a>
      </div>
    </td>
  </tr>
  <?php } ?>
</table>

<!-- ✅ NAVIGASI PAGINATION -->
<div style="margin-top:15px; text-align:center;">
  <?php if($halaman > 1): ?>
    <a href="dashboard.php?halaman=<?php echo $halaman-1; ?>&cari=<?php echo urlencode($cari); ?>&filter_kategori=<?php echo urlencode($filter_kategori); ?>" class="btn btn-edit">⬅️ Sebelumnya</a>
  <?php endif; ?>

  <span style="margin:0 10px;">Halaman <?php echo $halaman; ?> dari <?php echo $total_halaman; ?> (Total: <?php echo $total; ?> buku)</span>

  <?php if($halaman < $total_halaman): ?>
    <a href="dashboard.php?halaman=<?php echo $halaman+1; ?>&cari=<?php echo urlencode($cari); ?>&filter_kategori=<?php echo urlencode($filter_kategori); ?>" class="btn btn-tambah">Selanjutnya ➡️</a>
  <?php endif; ?>
</div>

</div>
</body>
</html>