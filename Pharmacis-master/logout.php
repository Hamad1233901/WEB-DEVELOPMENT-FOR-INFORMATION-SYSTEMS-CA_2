<?php
// Filename: logout.php
// Yeh script session khatam karke user ko wapis login page par bhejta hai.

session_start();
session_destroy();
header("Location: login.php");
exit;
?>