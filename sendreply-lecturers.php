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
if(strlen($_SESSION['alogin'])==0)
	{	
header('location:index.php');
}
else{

	if(isset($_GET['reply']))
	{
		$replyto=$_GET['reply'];
	}
	
	if(isset($_GET['id']))
	{
		$answerId=$_GET['id'];
	}   


	if(isset($_POST['submit']))
  	{	
	$id=uniqid();
	$receiver=$_POST['email'];
    	$message=$_POST['message'];
	$course=$_POST['course'];
	$sender=$_SESSION['alogin'];
	
	
	// validation
	$inputValidation="";

	// email validation		
	if(empty($receiver)) {
		$emailResponse = array(
			"type" => "emailError",
			"message" => "This field cannot be changed"
		);
		$logger->info('Bruker forsøkte å endre et felt som ikke er lov å endre'); // logging
	}
	else if(!preg_match("/^[a-zA-Z0-9 \@\. æøåÆØÅ]*$/", $receiver)) {
		$emailResponse = array(
			"type" => "emailError",
			"message" => "This field cannot be changed"
		);
		$logger->info('Bruker forsøkte å endre et felt som ikke er lov å endre'); // logging
	}
	else if(preg_match("/^[a-zA-Z0-9 \@\. æøåÆØÅ]*$/", $receiver)) {
		$inputValidation = "receiver";
	}
           
	// course validation		
	if (empty($course)) {
		$courseResponse = array(
			"type" => "courseError",
			"message" => "This field cannot be changed"
		);
		$logger->info('Bruker forsøkte å endre et felt som ikke er lov å endre'); // logging
	}
	else if (!in_array($_REQUEST['course'], ["3474", "8473", "1273", "8674", "9375", "7573"], true)) {
		$courseResponse = array(
			"type" => "courseError",
			"message" => "This field cannot be changed"
		);
		$logger->info('Bruker forsøkte å endre et felt som ikke er lov å endre'); // logging
	}
	else if (in_array($_REQUEST['course'], ["3474", "8473", "1273", "8674", "9375", "7573"], true)) {
		$inputValidation .= "Course";
	}
	
	// message validation
    	if (empty($message)) {
        	$msgResponse = array(
            		"type" => "msgError",
            		"message" => "Message is required"
        	);
    	}    
	else if (!preg_match("/^[a-zA-Z \-\'\,\.\?\!\/\(\)\%\+\=\"\^\r?\n æøåÆØÅ 0-9]*$/", $message)) {
        	$msgResponse = array(
            		"type" => "msgError",
            		"message" => "Invalid message"
        	); 
			$logger->info('Invalid message ved sending av svar til student fra foreleser'); // logging
    	} 
	else if (preg_match("/^[a-zA-Z \-\'\,\.\?\!\/\(\)\%\+\=\"\^\r?\n æøåÆØÅ 0-9]*$/", $message)) {
		$inputValidation .= "Msg";
	}
	
	
	// Sender informasjonen til databasen om alle validations er suksessfulle
	if($inputValidation == "receiverCourseMsg") {
		sleep(1);
		$sql = "CALL lecturerSendreplyInfo(:id, :sender, :receiver, :course, :message)";
		$query = $dbh->prepare($sql);
		$query-> bindParam(':id', $id, PDO::PARAM_STR);
		$query-> bindParam(':sender', $sender, PDO::PARAM_STR);
		$query-> bindParam(':receiver', $receiver, PDO::PARAM_STR);
		$query-> bindParam(':course', $course, PDO::PARAM_STR);
		$query-> bindParam(':message', $message, PDO::PARAM_STR);
		$query->execute();
		
		$answerSql = "CALL updateAnsweredValue(:answerId)";
		$answerQuery = $dbh->prepare($answerSql);
		$answerQuery-> bindParam(':answerId', $answerId, PDO::PARAM_STR);
		$answerQuery->execute();

		$logger->info('En foreleser har svart på en melding fra en student'); // logging 
		
		echo "<script type='text/javascript'>alert('Reply Sent!');</script>";
		echo "<script type='text/javascript'> document.location = 'feedback-lecturers.php'; </script>";
		
		// sender feedback og redirecter tilbake til oversikten over meldinger
		?>
	<?php
	//
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
	
	<title>Send Reply</title>

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
		$user = $_SESSION['alogin'];
		$sql = "CALL lecturerCourse(:user)";
		$query = $dbh -> prepare($sql);
		$query-> bindParam(':user', $user, PDO::PARAM_STR);
		$query->execute();
		$result=$query->fetch(PDO::FETCH_OBJ);	
		$course = ($result->course);
			
		// henter det som er i URL
		$url=$_SERVER['QUERY_STRING'];
		$url = str_replace("reply=", "", $url);
		//
?>
	<?php include('includes/header.php');?>
	<div class="ts-main-content">
	<?php include('includes/leftbar.php');?>
		<div class="content-wrapper">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="col-md-12">
                            <h2>Reply Feedback</h2>
								<div class="panel panel-default">
									<div class="panel-heading">Send Reply</div>


									<div class="panel-body">
<form method="post" class="form-horizontal" enctype="multipart/form-data">

<input type="hidden" name="email" class="form-control" readonly required value="<?php echo htmlentities($replyto);?>">

<div class="form-group">
		<label class="col-sm-2 control-label">Reply to<span style="color:red">*</span></label> <!-- byttet "Title" til "Reply to" --> 
	<div class="col-sm-4">
		<input type="text" name="title" class="form-control" readonly required value="<?php echo htmlentities($replyto);?>"> <!-- byttet "result->title" til "url" -->
			<?php if(!empty($replytoResponse)) { ?>
			<div class="response <?php echo $replytoResponse["type"]; ?> " color=red>
			<?php echo $replytoResponse["message"]; ?>
			</div>
			<?php }?>		
	</div>
</div>

<div class="form-group">
		<label class="col-sm-2 control-label">Course<span style="color:red">*</span></label>
	<div class="col-sm-4">
		<input type="text" name="course" class="form-control" readonly required value="<?php echo htmlentities($result->course);?>">
			<?php if(!empty($courseResponse)) { ?>
			<div class="response <?php echo $courseResponse["type"]; ?> " color=red>
			<?php echo $courseResponse["message"]; ?>
			</div>
			<?php }?>	
	</div>
</div>

<div class="form-group">
		<label class="col-sm-2 control-label">Message<span style="color:red">*</span></label>
	<div class="col-sm-6">
		<textarea name="message" class="form-control" cols="30" rows="10"></textarea>
			<?php if(!empty($msgResponse)) { ?>
			<div class="response <?php echo $msgResponse["type"]; ?> " color=red>
			<?php echo $msgResponse["message"]; ?>
			</div>
			<?php }?>	
	</div>
</div>

<div class="form-group">
	<div class="col-sm-8 col-sm-offset-2">
		<button class="btn btn-primary" name="submit" type="submit" href="feedback-lecturers.php" >Send Reply</button>
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
