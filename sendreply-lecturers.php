<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
	{	
header('location:index.php');
}
else{

	if(isset($_GET['reply']))
	{
	$replyto=$_GET['reply'];
	}   

	if(isset($_POST['submit']))
  {	
	$receiver=$_POST['email'];
    $message=$_POST['message'];
	$course=$_POST['course'];
	$notitype='Send Message';
	$sender=$_SESSION['alogin'];
	
    $sqlnoti="insert into notification (notiuser,notireceiver,notitype) values (:notiuser,:notireceiver,:notitype)";
    $querynoti = $dbh->prepare($sqlnoti);
	$querynoti-> bindParam(':notiuser', $sender, PDO::PARAM_STR);
	$querynoti-> bindParam(':notireceiver',$receiver, PDO::PARAM_STR);
    $querynoti-> bindParam(':notitype', $notitype, PDO::PARAM_STR);
    $querynoti->execute();

	$sql = "INSERT INTO feedback (sender, receiver, course, title, feedbackdata, attachment) VALUES (:user,:receiver, :course, '', :description, '')";
	$query = $dbh->prepare($sql);
	$query-> bindParam(':user', $sender, PDO::PARAM_STR);
	$query-> bindParam(':receiver', $receiver, PDO::PARAM_STR);
	$query-> bindParam(':course', $course, PDO::PARAM_STR);
	$query-> bindParam(':description', $message, PDO::PARAM_STR);
    $query->execute(); 
	$msg="Feedback Send";
	// sender feedback og redirecter tilbake til oversikten over meldinger
	?>
	<script type="text/javascript">
	window.location = "feedback-lecturers.php";
	</script>      
		<?php
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
		$sql = "SELECT * from students, feedback;";
		$query = $dbh -> prepare($sql);
		$query->execute();
		$result=$query->fetch(PDO::FETCH_OBJ);
		$cnt=1;	
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
<?php if($error){?><div class="errorWrap"><strong>ERROR</strong>:<?php echo htmlentities($error); ?> </div><?php } 
				else if($msg){?><div class="succWrap"><strong>SUCCESS</strong>:<?php echo htmlentities($msg); ?> </div><?php }?>

									<div class="panel-body">
<form method="post" class="form-horizontal" enctype="multipart/form-data">

<input type="hidden" name="email" class="form-control" readonly required value="<?php echo htmlentities($replyto);?>">

<div class="form-group">
	<label class="col-sm-2 control-label">Reply to<span style="color:red">*</span></label> <!-- byttet "Title" til "Reply to" --> 
	<div class="col-sm-4">
	<input type="text" name="title" class="form-control" readonly required value="<?php echo htmlentities($url);?>"> <!-- byttet "result->title" til "url" --> 
	</div>
</div>

<div class="form-group">
	<label class="col-sm-2 control-label">Course<span style="color:red">*</span></label>
	<div class="col-sm-4">
	<input type="text" name="course" class="form-control" readonly required value="<?php echo htmlentities($result->course);?>">
	</div>
</div>

<div class="form-group">
	<label class="col-sm-2 control-label">Message<span style="color:red">*</span></label>
	<div class="col-sm-6">
	<textarea name="message" class="form-control" cols="30" rows="10"></textarea>
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
