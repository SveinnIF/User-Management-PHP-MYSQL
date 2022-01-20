<?php
session_start();
error_reporting(0);
include('includes/config.php');
$result = "";
if (isset($_GET["i"]) && isset($_GET["h"])) {
 echo "<p>Punkt 1</p>";
  // (B) CHECK IF VALID REQUEST
  $stmt = $dbh->prepare("SELECT * FROM `password_reset` WHERE `id`=?");
echo "<p>Punkt 2</p>";
  $stmt->execute([$_GET["i"]]);
  $request = $stmt->fetch();
  if (is_array($request)) {
    if ($request["reset_hash"] != $_GET["h"]) { $result = "Invalid request"; }
  } else { $result = "Invalid request"; }
 echo "<p>Punkt 3</p>";
  // (C) CHECK EXPIRED
  if ($result=="") {
    $now = strtotime("now");
    $expire = strtotime($request["reset_time"]) + $prvalid;
    if ($now >= $expire) { $result = "Request expired"; }
  }
 echo "<p>Punkt 4</p>";
  // (D) PROCEED PASSWORD RESET
  if ($result=="") {
    // RANDOM PASSWORD
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-=+?";
    $password = substr(str_shuffle($chars),0 ,8); // 8 characters
 
    // UPDATE DATABASE
    $stmt = $dbh->prepare("UPDATE `students` SET `password`=? WHERE `id`=?");
    $stmt->execute([$password, $_GET["i"]]);
    $stmt = $dbh->prepare("DELETE FROM `password_reset` WHERE `id`=?");
    $stmt->execute([$_GET["i"]]);
 echo "<p>Punkt 5</p>";
    // SHOW RESULTS (UPDATED PASSWORD)
    $result = "Password has been updated to $password. Please login and change it.";
  }
}
 
// (E) INVALID REQUEST
else { $result = "Invalid request"; }
 
// (F) OUTPUT RESULTS
echo "<div>$result</div>";
?>