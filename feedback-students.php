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
if(strlen($_SESSION['alogin'])==0)
	{	
header('location:index.php');
}
else{
	
if(isset($_POST['submit']))
  {	
	$id=uniqid();
	$title=$_POST['title'];
	$message=$_POST['message'];
	$course=$_POST['course'];
	$user=$_SESSION['alogin'];
	$receiver='Lecturers' AND 'Admin';
	
	$query=$dbh->prepare("CALL studentFeedbackSender(:user)");
	$query-> bindParam(':user', $user, PDO::PARAM_STR);
	$query->execute();
	$result=$query->fetch(PDO::FETCH_OBJ);
	$anon= ($result->id);
	
	// validation
	$inputValidation="";
	
	// name validation
    	if (empty($title)) {
		$titleResponse = array(
            		"type" => "titleError",
			    "message" => "Title is required"
        	);
    	}    
    	else if (!preg_match("/^[a-zA-Z-' æøåÆØÅ]*$/", $title)) {
        	$titleResponse = array(
            		"type" => "titleError",
            		"message" => "Invalid title"
        	); 
    	} 
	else if (preg_match("/^[a-zA-Z-' æøåÆØÅ]*$/", $title)) {
		$inputValidation="title";
	}

	// course validation
	if(isset($_REQUEST['course']) && $_REQUEST['course'] == "0") { 
        	$courseResponse = array(
            		"type" => "courseError",
            		"message" => "Course is required"
        	);
    	}    
    	else if(isset($_REQUEST['course']) &&  !in_array($_REQUEST['course'], ["3474", "8473", "1273", "8674", "9375", "7573"], true)) {
        	$courseResponse = array(
            		"type" => "courseError",
            		"message" => "Invalid course"
        	); 
    	} 
	else if(isset($_REQUEST['course']) &&  in_array($_REQUEST['course'], ["3474", "8473", "1273", "8674", "9375", "7573"], true)) {
		$inputValidation .="Course";
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
    	} 
	else if (preg_match("/^[a-zA-Z \-\'\,\.\?\!\/\(\)\%\+\=\"\^\r?\n æøåÆØÅ 0-9]*$/", $message)) {
		$inputValidation .= "Msg";
	}
	
	// Sender informasjonen til databasen om alle validations er suksessfulle
	if($inputValidation == "titleCourseMsg") {
		sleep(1);
		$query=$dbh->prepare("CALL studentFeedbackInfo(:id, :user, :receiver, :course, :title, :message)");
		if ($_POST['anon'] == 'anonymous') {
			$query-> bindParam(':user', $anon, PDO::PARAM_STR);
		} else {
			$query-> bindParam(':user', $user, PDO::PARAM_STR);
		  }
		$query-> bindParam(':id', $id, PDO::PARAM_STR);  
		$query-> bindParam(':receiver', $receiver, PDO::PARAM_STR);
		$query-> bindParam(':course', $course, PDO::PARAM_STR);
		$query-> bindParam(':title', $title, PDO::PARAM_STR);
		$query-> bindParam(':message', $message, PDO::PARAM_STR);
		$query->execute(); 
		$msg="Feedback Sent";
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
	
	<title>Send Feedback</title>

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
$query=$dbh->prepare("CALL lecturerInfoStudentFeedback('3474')");
$query->execute();
$dotNET=$query->fetch(PDO::FETCH_OBJ);

$query=$dbh->prepare("CALL lecturerInfoStudentFeedback('8473')");
$query->execute();
$algoritmer=$query->fetch(PDO::FETCH_OBJ);

$query=$dbh->prepare("CALL lecturerInfoStudentFeedback('1273')");
$query->execute();
$datasikkerhet=$query->fetch(PDO::FETCH_OBJ);

$query=$dbh->prepare("CALL lecturerInfoStudentFeedback('8674')");
$query->execute();
$bildeanalyse=$query->fetch(PDO::FETCH_OBJ);

$query=$dbh->prepare("CALL lecturerInfoStudentFeedback('9375')");
$query->execute();
$algebra=$query->fetch(PDO::FETCH_OBJ);

$query=$dbh->prepare("CALL lecturerInfoStudentFeedback('7573')");
$query->execute();
$autonome=$query->fetch(PDO::FETCH_OBJ);
?>
	<?php include('includes/header-students.php');?>
	<div class="ts-main-content">
	<?php include('includes/leftbar-students.php');?>
		<div class="content-wrapper">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
                       
							<div class="col-md-12">
                            <h2>Give us Feedback</h2>
								<div class="panel panel-default">
									<div class="panel-heading">Feedback</div>
<?php if($error){?><div class="errorWrap"><strong>ERROR</strong>:<?php echo htmlentities($error); ?> </div><?php } 
				else if($msg){?><div class="succWrap"><strong>SUCCESS</strong>:<?php echo htmlentities($msg); ?> </div><?php }?>

<div class="panel-body">
<form method="post" class="form-horizontal" enctype="multipart/form-data">

<div class="form-group">
	<label class="col-sm-2 control-label">Title<span style="color:red">*</span></label>
		<div class="col-sm-4">
			<input type="text" name="title" class="form-control" required>
				<?php if(!empty($titleResponse)) { ?>
				<div class="response <?php echo $titleResponse["type"]; ?>
				">
				<?php echo $titleResponse["message"]; ?>
				</div>
				<?php }?>
		</div>
</div>
	
<div class="form-group">
    <label class="col-sm-2 control-label">Course<span style="color:red">*</span></label>
		<div class="col-sm-4">
			<select name="course" class="form-control" required>
				<option value="0">Select</option>
				<option value="3474">.NET</option>
				<option value="8473">Algoritmer og datastrukturer</option>
				<option value="1273">Datasikkerhet i utvikling og drift</option>
				<option value="8674">Bildeanalyse</option>
				<option value="9375">Lineær algebra og integraltransformer</option>
				<option value="7573">Autonome kjøretøy</option>
			</select>
				<?php if(!empty($courseResponse)) { ?>
				<div class="response <?php echo $courseResponse["type"]; ?>
				">
				<?php echo $courseResponse["message"]; ?>
				</div>
				<?php }?>
		</div>  
</div>

<div class="form-group">
	<label class="col-sm-2 control-label">Message<span style="color:red">*</span></label>
		<div class="col-sm-10">
			<textarea class="form-control" rows="5" name="message"></textarea>
				<?php if(!empty($msgResponse)) { ?>
				<div class="response <?php echo $msgResponse["type"]; ?>
				">
				<?php echo $msgResponse["message"]; ?>
				</div>
				<?php }?>
		</div>
</div>

<!-- checkbox anonym  -->
<div class="form-group">
	<div class="col-sm-8 col-sm-offset-2">
		<input type="checkbox" name="anon" value="anonymous">
  		<label for="anonymous"> Hide your name from lecturer </label><br>
	</div>
</div>
<!-- checkbox anonym  -->

<div class="form-group">
	<div class="col-sm-8 col-sm-offset-2">
		<button class="btn btn-primary" name="submit" type="submit">Send</button>
	</div>
</div>


<h2 class="page-title">Lecturers</h2>

<div class="form-group">
	<div class="col-sm-8 col-sm-offset-2">
		<h3>.NET</h3>
			<img src="images/<?php echo htmlentities($dotNET->image);?>" width="150px"/>
			<?php echo str_repeat('&nbsp;', 3), htmlentities($dotNET->name);?>
	</div>
</div>

<div class="form-group">
	<div class="col-sm-8 col-sm-offset-2">
		<h3>Algoritmer og datastrukturer</h3>
			<img src="images/<?php echo htmlentities($algoritmer->image);?>" width="150px"/>
			<?php echo str_repeat('&nbsp;', 3), htmlentities($algoritmer->name);?>
	</div>
</div>

<div class="form-group">	
	<div class="col-sm-8 col-sm-offset-2">
		<h3>Datasikkerhet i utvikling og drift</h3>
			<img src="images/<?php echo htmlentities($datasikkerhet->image);?>" width="150px"/>
			<?php echo str_repeat('&nbsp;', 3), htmlentities($datasikkerhet->name);?>
	</div>
</div>

<div class="form-group">	
	<div class="col-sm-8 col-sm-offset-2">
		<h3>Bildeanalyse</h3>
			<img src="images/<?php echo htmlentities($bildeanalyse->image);?>" width="150px"/>
			<?php echo str_repeat('&nbsp;', 3), htmlentities($bildeanalyse->name);?>
	</div>
</div>

<div class="form-group">	
	<div class="col-sm-8 col-sm-offset-2">
		<h3>Lineær algebra og integraltransformer</h3>
			<img src="images/<?php echo htmlentities($algebra->image);?>" width="150px"/>
			<?php echo str_repeat('&nbsp;', 3), htmlentities($algebra->name);?>
	</div>
</div>

<div class="form-group">	
	<div class="col-sm-8 col-sm-offset-2">
		<h3>Autonome kjøretøy</h3>
			<img src="images/<?php echo htmlentities($autonome->image);?>" width="150px"/>
			<?php echo str_repeat('&nbsp;', 3), htmlentities($autonome->name);?>
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
