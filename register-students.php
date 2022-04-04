<?php
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
if(isset($_POST['submit']))
{
$id=uniqid();
$name=$_POST['name'];
$email=$_POST['email'];
$password=$_POST['password'];
$fieldofstudy=$_POST['fieldofstudy'];
$class=$_POST['class'];
$receiver='Admin';

// input validation
$inputValidation='';
$uppercase    = preg_match('@[A-Z ÆØÅ]@', $password);
$lowercase    = preg_match('@[a-z æøå]@', $password);
$number    	  = preg_match('@[0-9]@', $password);
$specialChars = preg_match('@[^\w]@', $password);

// Check if email is in use
$query = $dbh->prepare("SELECT email FROM students WHERE email=?");
$query->execute([$email]); 
$emailCheck = $query->fetch();

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
	else if ($emailCheck) {
		$emailResponse = array(
			"type" => "emailError",
			"message" => "Invalid email"
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
	
	// password validation
	if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
		$pwdResponse = array(
			"type" => "passwordError",
			"message" => "Password must be at least 8 characters long and must include at least one upper case letter, one lower case letter, one number, and one special character."
		);
	}
	else if ($uppercase && $lowercase && $number && $specialChars && strlen($password) > 8){
		$password=password_hash($_POST['password'], PASSWORD_DEFAULT);
		$inputValidation .= "Pw";
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
	if($inputValidation == "nameEmailPwFosClass") {
		$sql ="CALL studentRegistrationInfo(:id, :name, :email, :password, :fieldofstudy, :class, '1')";
		$query= $dbh -> prepare($sql);
		$query-> bindParam(':id', $id, PDO::PARAM_STR);
		$query-> bindParam(':name', $name, PDO::PARAM_STR);
		$query-> bindParam(':email', $email, PDO::PARAM_STR);
		$query-> bindParam(':password', $password, PDO::PARAM_STR);
		$query-> bindParam(':fieldofstudy', $fieldofstudy, PDO::PARAM_STR);
		$query-> bindParam(':class', $class, PDO::PARAM_STR);
		$query->execute();
		
		echo "<script type='text/javascript'>alert('Registration Successful!');</script>";
		echo "<script type='text/javascript'> document.location = 'index.php'; </script>";
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
					<div class="col-md-12">
						<h1 class="text-center text-bold mt-2x">Student Registration</h1>
                        			<div class="hr-dashed"></div>
						<div class="well row pt-2x pb-3x bk-light text-center">
                         			<form method="post" class="form-horizontal" enctype="multipart/form-data" name="regform">
                            
							<div class="form-group">
								<label class="col-sm-1 control-label">Name<span style="color:red">*</span></label>
                            				<div class="col-sm-5">
								<input type="text" name="name" class="form-control" required>
									<?php if(!empty($nameResponse)) { ?>
									<div class="response <?php echo $nameResponse["type"]; ?> " color=red>
									<?php echo $nameResponse["message"]; ?>
									</div>
									<?php }?>
                            				</div>
                            
								<label class="col-sm-1 control-label">Email<span style="color:red">*</span></label>
                            				<div class="col-sm-5">
								<input type="text" name="email" class="form-control" required>
									<?php if(!empty($emailResponse)) { ?>
									<div class="response <?php echo $emailResponse["type"]; ?> " color=red>
									<?php echo $emailResponse["message"]; ?>
									</div>
									<?php }?>
                            				</div>
                            				</div>

                            				<div class="form-group">
								<label class="col-sm-1 control-label">Password<span style="color:red">*</span></label>
                            				<div class="col-sm-5">
								<input type="password" name="password" class="form-control" id="password"  >
									<?php if(!empty($pwdResponse)) { ?>
									<div class="response <?php echo $pwdResponse["type"]; ?> " color=red>
									<?php echo $pwdResponse["message"]; ?>
									</div>
									<?php }?>
                            				</div>

								<label class="col-sm-1 control-label">Field of study<span style="color:red">*</span></label>
                            				<div class="col-sm-5">
								<input type="text" name="fieldofstudy" class="form-control" required>
									<?php if(!empty($fosResponse)) { ?>
									<div class="response <?php echo $fosResponse["type"]; ?> " color=red>
									<?php echo $fosResponse["message"]; ?>
									</div>
									<?php }?>
                            				</div>
                            				</div>
							
							<div class="form-group">
								<label class="col-sm-1 control-label">Class<span style="color:red">*</span></label>
                            				<div class="col-sm-5">
								<select name="class" class="form-control" required>
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


							<br>
							
							<button class="btn btn-primary" name="submit" type="submit">Register</button>
						</form>
							
							<br>
							<br>
							
							<p>Already Have An Account? <a href="index.php" >Signin</a></p>
							<p>Are You A Lecturer? <a href="register-lecturers.php" >Signup as lecturer</a></p>
							
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
