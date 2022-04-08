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
	$file = $_FILES['image']['name'];
	$file_loc = $_FILES['image']['tmp_name'];
	$folder="../images/";
	$new_file_name = strtolower($file);
	$final_file=str_replace(' ','-',$new_file_name);
	
	$name=$_POST['name'];
	$email=$_POST['email'];
	$course=$_POST['course'];
	$idedit=$_POST['idedit'];
	$image=$_POST['image'];

	// input validation
	$inputValidation="";
	
    $allowed_image_extension = array(
        "jpg",
        "jpeg"
    );
    
    // Get image file extension
    $file_extension = pathinfo($file, PATHINFO_EXTENSION);
    
    // Check that the image input is not empty
    if (! file_exists($file_loc)) {
        $response = array(
            "type" => "error",
            "message" => "Choose image file to upload."
        );
    }    // Check that the image has the valid extension
    else if (! in_array($file_extension, $allowed_image_extension)) {
        $response = array(
            "type" => "error",
            "message" => "Image must be .JPG or .JPEG."
        ); 
        
    }    // Check if file size is lower than the set size
    else if (($_FILES["image"]["size"] > 2000000)) {
        $response = array(
            "type" => "error",
            "message" => "Image size exceeds 2MB"
        );
    } else {
        if (move_uploaded_file($file_loc, $folder.$final_file)) {
			$image=$final_file;
			$inputValidation="img";
        } else {
            $response = array(
                "type" => "error",
                "message" => "Error uploading image."
            );
		}
    }
	// name validation
    if(empty($name)) {
        $nameResponse = array(
            "type" => "nameError",
            "message" => "Name is required"
        );
    }    
    else if(!preg_match("/^[a-zA-Z-' æøåÆØÅ]*$/", $name)) {
        $nameResponse = array(
            "type" => "nameError",
            "message" => "Invalid name"
        ); 
    } 
	else if(preg_match("/^[a-zA-Z-' æøåÆØÅ]*$/", $name)) {
		$inputValidation .= "Name";
	}
           
	// email validation		
	if(empty($email)) {
		$emailResponse = array(
			"type" => "emailError",
			"message" => "Email is required"
		);
	}
	else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$emailResponse = array(
			"type" => "emailError",
			"message" => "Invalid email"
		);
	}
	else if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$inputValidation .= "Email";
	}

	// course validation
	if(isset($_REQUEST['course']) && $_REQUEST['course'] == "0") { 
        $courseResponse = array(
            "type" => "courseError",
            "message" => "Course is required"
        );
    }    
    else if(isset($_REQUEST['course']) &&  !in_array($_REQUEST['course'], [".NET", "aod", "diuod", "blyse", "laoi", "ak"], true)) {
        $courseResponse = array(
            "type" => "courseError",
            "message" => "Invalid course"
        ); 
    } 
	else if(isset($_REQUEST['course']) &&  in_array($_REQUEST['course'], [".NET", "aod", "diuod", "blyse", "laoi", "ak"], true)) {
		$inputValidation .= "Course";
	}
	
	// Sender informasjonen til databasen om alle validations er suksessfulle
	if($inputValidation == "imgNameEmailCourse") {
		sleep(1);
		$sql="CALL editLecturersUpdate(:name, :email, :course, :image, :idedit)";
		$query = $dbh->prepare($sql);
		$query-> bindParam(':name', $name, PDO::PARAM_STR);
		$query-> bindParam(':email', $email, PDO::PARAM_STR);
		$query-> bindParam(':course', $course, PDO::PARAM_STR);
		$query-> bindParam(':image', $image, PDO::PARAM_STR);
		$query-> bindParam(':idedit', $idedit, PDO::PARAM_STR);
		$query->execute();
		$msg="Information Updated Successfully";
		
		echo "<script type='text/javascript'>alert('Editing Successful!');</script>";
		echo "<script type='text/javascript'> document.location = 'list-lecturers.php'; </script>";
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
	
	<title>Edit Lecturer</title>

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
$sql = "CALL editLecturerInfo(:editid)";
$query = $dbh -> prepare($sql);
$query->bindParam(':editid',$editid,PDO::PARAM_INT);
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
						<h3 class="page-title">Edit Lecturer: <?php echo htmlentities($result->name); ?></h3>
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
			<label class="col-sm-2 control-label">Image<span style="color:red">*</span></label>
			<div class="col-sm-4">
				<input type="file" name="image" class="form-control">
					<?php if(!empty($response)) { ?>
					<div class="response <?php echo $response["type"]; ?>
					">
					<?php echo $response["message"]; ?>
					</div>
					<?php }?>
			</div>

			<label class="col-sm-2 control-label">Course<span style="color:red">*</span></label>
			<div class="col-sm-4">
				<select name="course" class="form-control" required>
					<option value="">Select</option>
					<option value=".NET">.NET</option>
					<option value="aod">Algoritmer og datastrukturer</option>
					<option value="diuod">Datasikkerhet i utvikling og drift</option>
					<option value="blyse">Bildeanalyse</option>
					<option value="laoi">Lineær algebra og integraltransformer</option>
					<option value="ak">Autonome kjøretøy</option>
				</select>
					<?php if(!empty($courseResponse)) { ?>
					<div class="response <?php echo $courseResponse["type"]; ?> " color=red>
					<?php echo $courseResponse["message"]; ?>
					</div>
					<?php }?>
			</div>
			</div>

				<div class="col-sm-8 col-sm-offset-2">
					<img src="../images/<?php echo htmlentities($result->image);?>" width="150px"/>
					<input type="hidden" name="image" value="<?php echo htmlentities($result->image);?>" >
					<input type="hidden" name="idedit" value="<?php echo htmlentities($result->id);?>" >
				</div>
			</div>

			
			
			<div class="form-group">
				<div class="col-sm-8 col-sm-offset-2">
					<button class="btn btn-primary" name="submit" type="submit">Save Changes</button>
				</div>
			</div>
			</form>
			
			<br>
			

			

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
