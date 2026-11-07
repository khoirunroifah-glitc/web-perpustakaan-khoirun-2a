<?php
session_start();
include 'config/koneksi.php';

$user = $_POST['username'];
$pass = md5($_POST['password']);

$sql = mysqli_query($conn, "SELECT * FROM user WHERE username='$user' AND password='$pass'");
$cek = mysqli_fetch_assoc($sql);

if($cek){
  $_SESSION['login'] = true;
  $_SESSION['username'] = $cek['username'];
  $_SESSION['role'] = $cek['role']; // ✅ BARU: Simpan role ke session

  if(isset($_POST['remember'])){
    setcookie("user_login", $cek['username'], time() + (7*24*60*60), "/");
  }

  // ✅ Redirect berdasarkan role
  if($cek['role'] == 'admin'){
    header("Location: dashboard.php");
  }else{
    header("Location: peminjam_dashboard.php");
  }
  exit;
}else{
  echo "<script>alert('Username / Password Salah!');location='index.php';</script>";
}
?>