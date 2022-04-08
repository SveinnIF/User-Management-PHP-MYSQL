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
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
	{	
header('location:index.php');
}
else{
// Code for change password	
if(isset($_POST['submit']))
{
$username=$_SESSION['alogin'];

$sql = "SELECT password FROM admin WHERE username=:username";
$query = $dbh -> prepare($sql);
$query-> bindParam(':username', $username, PDO::PARAM_STR);
$query->execute();
$result=$query->fetch(PDO::FETCH_OBJ);	
$pwd = ($result->password);  
$password=password_verify($_POST['password'], $pwd); 
		
$newpassword=$_POST['newpassword'];
$cnfpassword=$_POST['confirmpassword'];

// validation
$uppercase    = preg_match('@[A-Z ÆØÅ]@', $newpassword);
$lowercase    = preg_match('@[a-z æøå]@', $newpassword);
$number    	  = preg_match('@[0-9]@', $newpassword);
$specialChars = preg_match('@[^\w]@', $newpassword);

	if(!$password) // sjekker om current pw er riktig med password_verify
		{
		sleep(1);
		$pwdResponse = array(
			"type" => "passwordError",
			"message" => "Current password invalid"
		);
	}
	else if ($newpassword != $cnfpassword) {
		sleep(1);
		$pwdResponse = array(
			"type" => "passwordError",
			"message" => "New Password and Confirm Password fields do not match"
		);
	}
	else if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($newpassword) <= 8) {
		sleep(1);
		$pwdResponse = array(
			"type" => "passwordError",
			"message" => "New password must be at least 8 characters long and must include at least one upper case letter, one lower case letter, one number, and one special character."
		);
	}
	else if ($uppercase && $lowercase && $number && $specialChars && strlen($newpassword) >= 8){
		$newpassword=password_hash($_POST['newpassword'], PASSWORD_DEFAULT);
		$cnfpassword=password_hash($_POST['confirmpassword'], PASSWORD_DEFAULT);
		
		sleep(1);
		
		$sql ="SELECT password FROM admin WHERE username=:username and password=:password";
		$query= $dbh -> prepare($sql);
		$query-> bindParam(':username', $username, PDO::PARAM_STR);
		$query-> bindParam(':password', $password, PDO::PARAM_STR);
		$query-> execute();
		$results = $query -> fetchAll(PDO::FETCH_OBJ);
		
		$con="UPDATE admin SET password=:newpassword WHERE username=:username";
		$chngpwd1 = $dbh->prepare($con);
		$chngpwd1-> bindParam(':username', $username, PDO::PARAM_STR);
		$chngpwd1-> bindParam(':newpassword', $newpassword, PDO::PARAM_STR);
		$chngpwd1->execute();
		$msg="Your Password succesfully changed";
	}
//
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
	<meta name="theme-color" content="#3e454c">
	
	<title>BBDMS | Admin Change Password</title>

	<!-- Font awesome -->
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<!-- Sandstone Bootstrap CSS -->
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<!-- Bootstrap Datatables -->
	<link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
	<!-- Bootstrap social button library -->
	<link rel="stylesheet" href="css/bootstrap-social.css">
	<!-- Bootstrap select -->
	<link rel="stylesheet" href="css/bootstrap-select.css">
	<!-- Bootstrap file input -->
	<link rel="stylesheet" href="css/fileinput.min.css">
	<!-- Awesome Bootstrap checkbox -->
	<link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
	<!-- Admin Stye -->
	<link rel="stylesheet" href="css/style.css">
	
	<style>
	.errorWrap {
    		padding: 10px;
   	 	margin: 0 0 20px 0;
		background: #dd3d36;
		color:#fff;
    		-webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    		box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
	}
	.succWrap{
    		padding: 10px;
    		margin: 0 0 20px 0;
		background: #5cb85c;
		color:#fff;
    		-webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    		box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
	}
	</style>


</head>

<body>
	<?php include('includes/header.php');?>
	<div class="ts-main-content">
	<?php include('includes/leftbar.php');?>
		<div class="content-wrapper">
			<div class="container-fluid">

				<div class="row">
					<div class="col-md-12">
					
						<h2 class="page-title">Change Password</h2>

						<div class="row">
							<div class="col-md-10">
								<div class="panel panel-default">
									<div class="panel-heading">Form fields</div>
									<div class="panel-body">
										<form method="post" name="chngpwd" class="form-horizontal" onSubmit="return valid();">
										
											
  	        	  <?php if($error){?><div class="errorWrap"><strong>ERROR</strong>:<?php echo htmlentities($error); ?> </div><?php } 
				else if($msg){?><div class="succWrap"><strong>SUCCESS</strong>:<?php echo htmlentities($msg); ?> </div><?php }?>
											<div class="form-group">
												<label class="col-sm-4 control-label">Current Password</label>
												<div class="col-sm-8">
													<input type="password" class="form-control" name="password" id="password" required>
												</div>
											</div>
											<div class="hr-dashed"></div>
											
											<div class="form-group">
												<label class="col-sm-4 control-label">New Password</label>
												<div class="col-sm-8">
													<input type="password" class="form-control" name="newpassword" id="newpassword" required>
												</div>
											</div>
											<div class="hr-dashed"></div>

											<div class="form-group">
												<label class="col-sm-4 control-label">Confirm Password</label>
												<div class="col-sm-8">
													<input type="password" class="form-control" name="confirmpassword" id="confirmpassword" required>
														<br>
														<br>
														<?php if(!empty($pwdResponse)) { ?>
														<div class="response <?php echo $pwdResponse["type"]; ?> " color=red>
														<?php echo $pwdResponse["message"]; ?>
														</div>
														<?php }?>
												</div>
											</div>
											<div class="hr-dashed"></div>
										
								
											
											<div class="form-group">
												<div class="col-sm-8 col-sm-offset-4">
								
													<button class="btn btn-primary" name="submit" type="submit">Save changes</button>
												</div>
											</div>

										</form>

									</div>
								</div>
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
	<script type="text/javascript">
				 $(document).ready(function () {          
					setTimeout(function() {
						$('.succWrap').slideUp("slow");
					}, 3000);
					});
	</script>

</body>

</html>
<?php } ?>
