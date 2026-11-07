<?php
session_start();
// Kalau sudah login → arahkan sesuai role
if(isset($_SESSION['login'])){
  if($_SESSION['role'] == 'admin'){
    header("Location: dashboard.php");
  }else{
    header("Location: peminjam_dashboard.php");
  }
  exit;
}
// Baca cookie remember me untuk isi username
$isi_username = isset($_COOKIE['user_login']) ? $_COOKIE['user_login'] : "";
?>
<!DOCTYPE html>
<html>
<head>
<title>Login Mini Digital Library</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
<h1>📚 Login Mini Digital Library</h1>
<form method="POST" action="proses_login.php">
  <label>Username</label>
  <input type="text" name="username" value="<?php echo $isi_username; ?>" required>
  <label>Password</label>
  <input type="password" name="password" required>
  <label><input type="checkbox" name="remember"> Remember Me</label>
  <button type="submit" class="btn btn-tambah">Login</button>
</form>
</div>
</body>
</html>