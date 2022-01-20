<?php
session_start();
error_reporting(0);
include('includes/config.php');
$result = "";
if (isset($_GET["i"]) && isset($_GET["h"])) {
  // (A) CONNECT TO DATABASE
 
  // (B) CHECK IF VALID REQUEST
  $stmt = $pdo->prepare("SELECT * FROM `password_reset` WHERE `id`=?");
  $stmt->execute([$_GET["i"]]);
  $request = $stmt->fetch();
  if (is_array($request)) {
    if ($request["reset_hash"] != $_GET["h"]) { $result = "Invalid request"; }
  } else { $result = "Invalid request"; }
 
  // (C) CHECK EXPIRED
  if ($result=="") {
    $now = strtotime("now");
    $expire = strtotime($request["reset_time"]) + $prvalid;
    if ($now >= $expire) { $result = "Request expired"; }
  }
 
  // (D) PROCEED PASSWORD RESET
  if ($result=="") {
    // RANDOM PASSWORD
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-=+?";
    $password = substr(str_shuffle($chars),0 ,8); // 8 characters
 
    // UPDATE DATABASE
    $stmt = $pdo->prepare("UPDATE `students` SET `password`=? WHERE `id`=?");
    $stmt->execute([$password, $_GET["i"]]);
    $stmt = $pdo->prepare("DELETE FROM `password_reset` WHERE `id`=?");
    $stmt->execute([$_GET["i"]]);
 
    // SHOW RESULTS (UPDATED PASSWORD)
    $result = "Password has been updated to $password. Please login and change it.";
  }
}
 
// (E) INVALID REQUEST
else { $result = "Invalid request"; }
 
// (F) OUTPUT RESULTS
echo "<div>$result</div>";
?>