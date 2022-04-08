<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
use Monolog\Handler\GelfHandler;
use Gelf\Message;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Logger;
$logger = new Logger('sikkerhet');
$transport = new Gelf\Transport\UdpTransport("127.0.0.1", 12201 /*,
Gelf\Transport\UdpTransport::CHUNK_SIZE_LAN*/);
$publisher = new Gelf\Publisher($transport);
$handler = new GelfHandler($publisher,Logger::DEBUG);
$logger->pushHandler($handler);

error_reporting(0);
include('includes/lecturerConfig.php');
if (isset($_POST["email"])) {
	$email=$_POST["email"];
	
	// email validation		
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		// (B2) CHECK IF VALID USER
		$stmt = $dbh->prepare("SELECT * FROM `lecturers` WHERE `email`=?");
		$stmt->execute([$_POST["email"]]);
		$user = $stmt->fetch();
		$result =is_array($user)
			? "" 
			: $_POST["email"] . " is not registered." ;
 
	// (B3) CHECK PREVIOUS REQUEST (PREVENT SPAM)
	if ($result == "") {
		$stmt = $dbh->prepare("SELECT * FROM `password_reset` WHERE `id`=?");
		$stmt->execute([$user["id"]]);
		$request = $stmt->fetch();
		$now = strtotime("now");
		$prvalid = 400;
		if (is_array($request)) {
		$expire = strtotime($request["reset_time"]) + $prvalid;
		if ($now < $expire) { $result = "Please try again later"; }
		}
	}
 
	// (B4) CHECKS OK - CREATE NEW RESET REQUEST
	if ($result == "") {
		// RANDOM HASH
		$hash = md5($user["email"] . $now);
 
		// DATABASE ENTRY
		$stmt = $dbh->prepare("REPLACE INTO `password_reset` VALUES (?,?,?)");
		$stmt->execute([$user["id"], $hash, date("Y-m-d H:i:s")]);
 
		// SEND EMAIL - CHANGE TO YOUR OWN!
		$from = "admin <sveinnif@hiof.no>";
		$subject = "Password reset";
		$header = implode("\r\n", [
			"From: $from",
			"MIME-Version: 1.0",
			"Content-type: text/html; charset=utf-8"
		]);
		$link = "http://158.39.188.201/steg1/reset.php?i=".$user["id"]."&h=".$hash;
		$message = "<a href='$link'>Click here to reset password</a>";
		if (!@mail($user["email"], $subject, $message, $header)) {
			$result = "Failed to send email! - Contact administrator";
		}
	}
 
	// (B5) RESULTS
	if ($result=="") { $result = "Email has been sent - Please click on the link in the email to confirm."; }
	#echo "<div> $result </div>";
	}
}
?>


<!doctype html>
<html lang="en" class="no-js">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">

	
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
	<link rel="stylesheet" href="css/bootstrap-social.css">
	<link rel="stylesheet" href="css/bootstrap-select.css">
	<link rel="stylesheet" href="css/fileinput.min.css">
	<link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
	<link rel="stylesheet" href="css/style.css">

</head>

<body>
	<div class="login-page bk-img">
		<div class="form-content">
			<div class="container">
				<div class="row">
					<div class="col-md-6 col-md-offset-3">
						<h1 class="text-center text-bold mt-4x">Forgot password</h1>
						<div class="well row pt-2x pb-3x bk-light">
							<div class="col-md-8 col-md-offset-2">
								<form method="post">

									<label for="" class="text-uppercase text-sm">Your Email</label>
									<input type="text" placeholder="Email" name="email" class="form-control mb" required>

									<button class="btn btn-primary btn-block" name="login" type="submit">SEND</button>
									<b>
									<h4 class="text-center"><?=$result?></h4>
									</b>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Loading Scripts -->
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap-select.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/jquery.dataTables.min.js"></script>
	<script src="js/dataTables.bootstrap.min.js"></script>
	<script src="js/Chart.min.js"></script>
	<script src="js/fileinput.js"></script>
	<script src="js/chartData.js"></script>
	<script src="js/main.js"></script>
</body>
</html>
