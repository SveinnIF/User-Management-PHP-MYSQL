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
include('includes/adminConfig.php');
if(strlen($_SESSION['alogin'])==0)
	{	
header('location:index.php');
}
else{

if(isset($_GET['edit']))
	{
		$editid=$_GET['edit'];
	}

if(isset($_POST['submit']))
  {
	$name=$_POST['name'];
	$email=$_POST['email'];
	$fieldofstudy=$_POST['fieldofstudy'];
	$class=$_POST['class'];
	$idedit=$_POST['idedit'];

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
	
	
	// field of study validation
    if (empty($fieldofstudy)) {
        $fosResponse = array(
            "type" => "fosError",
            "message" => "Field of study is required"
        );
    }    
    else if (!preg_match("/^[a-zA-Z-' æøåÆØÅ]*$/", $fieldofstudy)) {
        $fosResponse = array(
            "type" => "fosError",
            "message" => "Invalid field of study"
        ); 
    } 
	else if (preg_match("/^[a-zA-Z-' æøåÆØÅ]*$/", $fieldofstudy)) {
		$inputValidation .="Fos";
	}

	// class validation
	if(isset($_REQUEST['class']) && $_REQUEST['class'] == "0") { 
        $classResponse = array(
            "type" => "classError",
            "message" => "Class is required"
        );
    }    
    else if(isset($_REQUEST['class']) &&  !in_array($_REQUEST['class'], ["19/20", "20/21", "21/22"], true)) {
        $classResponse = array(
            "type" => "classError",
            "message" => "Invalid class"
        ); 
    } 
	else if(isset($_REQUEST['class']) &&  in_array($_REQUEST['class'], ["19/20", "20/21", "21/22"], true)) {
		$inputValidation .="Class";
	}
	
	// Sender informasjonen til databasen om alle validations er suksessfulle
	if($inputValidation == "nameEmailFosClass") {
		sleep(1);
		$sql="CALL editStudentUpdate(:name, :email, :fieldofstudy, :class, :idedit)";
		$query = $dbh->prepare($sql);
		$query-> bindParam(':name', $name, PDO::PARAM_STR);
		$query-> bindParam(':email', $email, PDO::PARAM_STR);
		$query-> bindParam(':fieldofstudy', $fieldofstudy, PDO::PARAM_STR);
		$query-> bindParam(':class', $class, PDO::PARAM_STR);
		$query-> bindParam(':idedit', $idedit, PDO::PARAM_STR);
		$query->execute();
		$msg="Information Updated Successfully";
		
		echo "<script type='text/javascript'>alert('Editing Successful!');</script>";
		echo "<script type='text/javascript'> document.location = 'list-students.php'; </script>";
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
	
	<title>Edit Student</title>

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
$sql = "CALL editStudentInfo(:editid)";
$query = $dbh -> prepare($sql);
$query->bindParam(':editid',$editid,PDO::PARAM_STR);
$query->execute();
$result=$query->fetch(PDO::FETCH_OBJ);
?>
	<?php include('includes/header.php');?>
	<div class="ts-main-content">
	<?php include('includes/leftbar.php');?>
		<div class="content-wrapper">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-12">
						<h3 class="page-title">Edit Student : <?php echo htmlentities($result->name); ?></h3>
						<div class="row">
							<div class="col-md-12">
								<div class="panel panel-default">
									<div class="panel-heading">Edit Info</div>
<?php if($error){?><div class="errorWrap"><strong>ERROR</strong>:<?php echo htmlentities($error); ?> </div><?php } 
				else if($msg){?><div class="succWrap"><strong>SUCCESS</strong>:<?php echo htmlentities($msg); ?> </div><?php }?>

<div class="panel-body">

	<form method="post" class="form-horizontal" enctype="multipart/form-data" name="imgform">
		<div class="form-group">
		
			<label class="col-sm-2 control-label">Name<span style="color:red">*</span></label>
			<div class="col-sm-4">
				<input type="text" name="name" class="form-control" required value="<?php echo htmlentities($result->name);?>">
					<?php if(!empty($nameResponse)) { ?>
					<div class="response <?php echo $nameResponse["type"]; ?> " color=red>
					<?php echo $nameResponse["message"]; ?>
					</div>
					<?php }?>
			</div>

			<label class="col-sm-2 control-label">Email<span style="color:red">*</span></label>
			<div class="col-sm-4">
				<input type="email" name="email" class="form-control" required value="<?php echo htmlentities($result->email);?>">
				<?php if(!empty($emailResponse)) { ?>
				<div class="response <?php echo $emailResponse["type"]; ?> " color=red>
				<?php echo $emailResponse["message"]; ?>
				</div>
				<?php }?>
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-2 control-label">Field of Study<span style="color:red">*</span></label>
			<div class="col-sm-4">
				<input type="text" name="fieldofstudy" class="form-control" required value="<?php echo htmlentities($result->fieldofstudy);?>">
				<?php if(!empty($fosResponse)) { ?>
				<div class="response <?php echo $fosResponse["type"]; ?> " color=red>
				<?php echo $fosResponse["message"]; ?>
				</div>
				<?php }?>
			</div>
			
			<label class="col-sm-2 control-label">Class<span style="color:red">*</span></label>
			<div class="col-sm-4">
				<select name="class" class="form-control" required value="<?php echo htmlentities($result->class);?>">
					<option value="0">Select</option>
					<option value="19/20">19/20</option>
					<option value="20/21">20/21</option>
					<option value="21/22">21/22</option>
				</select>
					<?php if(!empty($classResponse)) { ?>
					<div class="response <?php echo $classResponse["type"]; ?> " color=red>
					<?php echo $classResponse["message"]; ?>
					</div>
					<?php }?>
			</div>
		</div>
	
		<input type="hidden" name="idedit" value="<?php echo htmlentities($result->id);?>" >
		
		<div class="form-group">
			<div class="col-sm-8 col-sm-offset-2">
				<button class="btn btn-primary" name="submit" type="submit">Save Changes</button>
			</div>
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
