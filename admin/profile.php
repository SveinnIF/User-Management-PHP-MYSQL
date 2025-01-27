<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';
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
include('includes/adminConfig.php');
if(strlen($_SESSION['alogin'])==0)
	{	
header('location:index.php');
}
else{
	
if(isset($_POST['submit']))
  {	
	$name=$_POST['name'];
	$email=$_POST['email'];

	// input validation
	$inputValidation="";
	
	// name validation
    if (empty($name)) {
        $nameResponse = array(
            "type" => "nameError",
            "message" => "Name is required"
        );
    }    
    else if (!preg_match("/^[a-zA-Z-' æøåÆØÅ]*$/", $name)) {
        $nameResponse = array(
            "type" => "nameError",
            "message" => "Invalid name"
        ); 
    } 
	else if (preg_match("/^[a-zA-Z-' æøåÆØÅ]*$/", $name)) {
		$inputValidation="name";
	}
           
	// email validation		
	if (empty($email)) {
		$emailResponse = array(
			"type" => "emailError",
			"message" => "Email is required"
		);
	}
	else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$emailResponse = array(
			"type" => "emailError",
			"message" => "Invalid email"
		);
	}
	else if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$inputValidation .= "Email";
	}
	
	if($inputValidation = "nameEmail") {
		sleep(1);
		$sql="UPDATE admin SET username=(:name), email=(:email)";
		$query = $dbh->prepare($sql);
		$query-> bindParam(':name', $name, PDO::PARAM_STR);
		$query-> bindParam(':email', $email, PDO::PARAM_STR);
		$query->execute();
		$msg="Information Updated Successfully";
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
	<meta name="theme-color" content="#3e454c">
	
	<title>Edit Admin</title>

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

	<script type= "text/javascript" src="../vendor/countries.js"></script>
	
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
<?php
		$sql = "CALL adminProfileInfo()";
		$query = $dbh -> prepare($sql);
		$query->execute();
		$result=$query->fetch(PDO::FETCH_OBJ);
		$cnt=1;	
?>
	<?php include('includes/header.php');?>
	<div class="ts-main-content">
	<?php include('includes/leftbar.php');?>
		<div class="content-wrapper">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-12">
						<h3 class="page-title">Manage Admin</h3>
						<div class="row">
							<div class="col-md-12">
								<div class="panel panel-default">
									<div class="panel-heading">Edit Info</div>
<?php if($error){?><div class="errorWrap"><strong>ERROR</strong>:<?php echo htmlentities($error); ?> </div><?php } 
	else if($msg){?><div class="succWrap"><strong>SUCCESS</strong>:<?php echo htmlentities($msg); ?> </div><?php }?>

	<div class="panel-body">
<form method="post" class="form-horizontal" enctype="multipart/form-data">
	<div class="form-group">
		
		<label class="col-sm-2 control-label">Username<span style="color:red">*</span></label>
	<div class="col-sm-4">
		<input type="text" name="name" class="form-control" required value="<?php echo htmlentities($result->username);?>">
	</div>
		
		<label class="col-sm-2 control-label">Email<span style="color:red">*</span></label>
	<div class="col-sm-4">
		<input type="email" name="email" class="form-control" required value="<?php echo htmlentities($result->email);?>">
	</div>
	</div>


	<div class="form-group">
	<div class="col-sm-8 col-sm-offset-2">
		<button class="btn btn-primary" name="submit" type="submit">Save Changes</button>
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
