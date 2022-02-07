<?php
session_start();
error_reporting(0);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
	{	
header('location:index.php');
}
else{
	
if(isset($_POST['submit']))
  {	
	$file = $_FILES['attachment']['name'];
	$file_loc = $_FILES['attachment']['tmp_name'];
	$folder="attachment/";
	$new_file_name = strtolower($file);
	$final_file=str_replace(' ','-',$new_file_name);
	
	$title=$_POST['title'];
    	$description=$_POST['description'];
	$course=$_POST['course'];
	$user=$_SESSION['alogin'];
	$receiver='Lecturers' AND 'Admin';
    	$notitype='Send Feedback';
    	$attachment=' ';

	if(move_uploaded_file($file_loc,$folder.$final_file))
		{
			$attachment=$final_file;
		}
	$notireceiver = 'Admin';
    	$sqlnoti="INSERT INTO notification (notiuser,notireceiver,notitype) VALUES (:notiuser,:notireceiver,:notitype)";
    	$querynoti = $dbh->prepare($sqlnoti);
	$querynoti-> bindParam(':notiuser', $user, PDO::PARAM_STR);
	$querynoti-> bindParam(':notireceiver', $notireceiver, PDO::PARAM_STR);
    	$querynoti-> bindParam(':notitype', $notitype, PDO::PARAM_STR);
   	$querynoti->execute();

	$sql="INSERT INTO feedback (sender,receiver,course,title,feedbackdata,attachment) VALUES (:user,:receiver,:course,:title,:description,:attachment)";
	$query = $dbh->prepare($sql);
	$query-> bindParam(':user', $user, PDO::PARAM_STR);
	$query-> bindParam(':receiver', $receiver, PDO::PARAM_STR);
	$query-> bindParam(':course', $course, PDO::PARAM_STR);
	$query-> bindParam(':title', $title, PDO::PARAM_STR);
	$query-> bindParam(':description', $description, PDO::PARAM_STR);
	$query-> bindParam(':attachment', $attachment, PDO::PARAM_STR);
    	$query->execute(); 
	$msg="Feedback Send";
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
	$sql = "SELECT *, lecturers.image FROM students, lecturers;";
	$query = $dbh -> prepare($sql);
	$query->execute();
	$result=$query->fetch(PDO::FETCH_OBJ);
	$cnt=1;	
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
									<div class="panel-heading">Edit Info</div>
<?php if($error){?><div class="errorWrap"><strong>ERROR</strong>:<?php echo htmlentities($error); ?> </div><?php } 
				else if($msg){?><div class="succWrap"><strong>SUCCESS</strong>:<?php echo htmlentities($msg); ?> </div><?php }?>

<div class="panel-body">
<form method="post" class="form-horizontal" enctype="multipart/form-data">

<div class="form-group">
	<label class="col-sm-2 control-label">Lecturer<span style="color:red">*</span></label>
	<div class="col-sm-4">
		<img src="images/<?php echo htmlentities($result->image);?>" width="150px"/>
		<input type="hidden" name="image" value="<?php echo htmlentities($result->image);?>" >
		<input type="hidden" name="idedit" value="<?php echo htmlentities($result->id);?>" >
	</div>

    <input type="hidden" name="user" value="<?php echo htmlentities($result->email); ?>">
	<label class="col-sm-1 control-label">Title<span style="color:red">*</span></label>
	<div class="col-sm-4">
	<input type="text" name="title" class="form-control" required>
	</div>
</div>
	
	
<div class="form-group">
    <label class="col-sm-2 control-label">Course<span style="color:red">*</span></label>
    <div class="col-sm-4">
	<select name="course" class="form-control" required>
    		<option value="">Select</option>
    		<option value=".NET">.NET</option>
		<option value="Algoritmer og datastrukturer">Algoritmer og datastrukturer</option>
		<option value="Datasikkerhet i utvikling og drift">Datasikkerhet i utvikling og drift</option>
		<option value="Bildeanalyse">Bildeanalyse</option>
		<option value="Lineær algebra og integraltransformer">Lineær algebra og integraltransformer</option>
		<option value="Autonome kjøretøy">Autonome kjøretøy</option>
	</select>
	</div>  

	<label class="col-sm-1 control-label">Attachment<span style="color:red"></span></label>
	<div class="col-sm-4">
		<input type="file" name="attachment" class="form-control">
	</div>
</div>

<div class="form-group">
	<label class="col-sm-2 control-label">Description<span style="color:red">*</span></label>
	<div class="col-sm-10">
		<textarea class="form-control" rows="5" name="description"></textarea>
	</div>
</div>

<div class="form-group">
	<div class="col-sm-8 col-sm-offset-2">
		<button class="btn btn-primary" name="submit" type="submit">Send</button>
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
