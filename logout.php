<?php
session_start();
session_destroy();
// Hapus cookie remember me
setcookie("user_login", "", time()-3600, "/");
header("Location: index.php");
exit;
?>