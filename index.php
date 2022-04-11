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
include('includes/studentConfig.php');
if(isset($_POST['login'])) {
$email=$_POST['username'];

	// email validation		
	if (empty($email)) {
		sleep(1);
		$emailResponse = array(
			"type" => "emailError",
			"message" => "Invalid Details Or Account Not Confirmed",
			$logger->info('En student forsøkte å logge inn, men feilet'); // logging 
		);
	}
	else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		sleep(1);
		$emailResponse = array(
			"type" => "emailError",
			"message" => "Invalid Details Or Account Not Confirmed",
			$logger->info('En student forsøkte å logge inn, men feilet'); // logging 
		);
	}
	else if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$sql = "CALL studentLoginCheck(:email)";
		$query = $dbh -> prepare($sql);
		$query-> bindParam(':email', $email, PDO::PARAM_STR);
		$query->execute();
		$result=$query->fetch(PDO::FETCH_OBJ);	
		$pwd = ($result->password);  
		$password=password_verify($_POST['password'], $pwd);

		if($password)
		{
			sleep(1);
			$_SESSION['alogin']=$_POST['username'];
			echo "<script type='text/javascript'> document.location = 'feedback-students.php'; </script>";
			$logger->info('En student har logget inn'); // logging 
		} 
		else{
			sleep(1);
			$emailResponse = array(
				"type" => "emailError",
				"message" => "Invalid Details Or Account Not Confirmed",
				$logger->info('En student forsøkte å logge inn, men feilet'); // logging 
			);
		}
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
	
	<title>Student Sign-in</title>
	
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
						<h1 class="text-center text-bold mt-4x">Student Login</h1>
						<div class="well row pt-2x pb-3x bk-light">
							<div class="col-md-8 col-md-offset-2">
								<form method="post">

									<label for="" class="text-uppercase text-sm">Your Email</label>
									<input type="text" placeholder="Email" name="username" class="form-control mb" required>
										
									<label for="" class="text-uppercase text-sm">Password</label>
									<input type="password" placeholder="Password" name="password" class="form-control mb" required>
									
										<?php if(!empty($emailResponse)) { ?>
										<div class="response <?php echo $emailResponse["type"]; ?> " color=red>
										<?php echo $emailResponse["message"]; ?>
										</div>
										<?php }?>
										<br>
										
									<button class="btn btn-primary btn-block" name="login" type="submit">LOGIN</button>
								</form>
										
								<br>
								<p>Don't Have an Account? <a href="register-students.php" >Signup</a></p>
								<p>Are You A Lecturer? <a href="lecturers-login.php" >Click here</a></p>
								<p>Guest loggin <a href="Guest.php"> here </a></p>
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
