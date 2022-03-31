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

include('includes/config.php');
if(isset($_POST['submit']))
{
$name=$_POST['name'];
	$email=$_POST['email'];
	$password=password_hash($_POST['password'], PASSWORD_DEFAULT);
	$course=$_POST['course'];
	$receiver='Admin';
	$sender=$email;
	
	$file = $_FILES['image']['name'];
	$file_loc = $_FILES['image']['tmp_name'];
	$folder="images/"; 
	$new_file_name = strtolower($file);
	$final_file=str_replace(' ','-',$new_file_name);
	
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
				
			$sql="CALL lecturerRegistrationInfo(:name, :email, :password, :course, :image, '0')";
			$query= $dbh -> prepare($sql);
			$query-> bindParam(':name', $name, PDO::PARAM_STR);
			$query-> bindParam(':email', $email, PDO::PARAM_STR);
			$query-> bindParam(':password', $password, PDO::PARAM_STR);
			$query-> bindParam(':course', $course, PDO::PARAM_STR);
			$query-> bindParam(':image', $image, PDO::PARAM_STR);
			$query->execute();
			echo "<script type='text/javascript'>alert('Registration Successful!');</script>";
			echo "<script type='text/javascript'> document.location = 'lecturers-login.php'; </script>";
        } else {
            $response = array(
                "type" => "error",
                "message" => "Error uploading image."
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

	
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
	<link rel="stylesheet" href="css/bootstrap-social.css">
	<link rel="stylesheet" href="css/bootstrap-select.css">
	<link rel="stylesheet" href="css/fileinput.min.css">
	<link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
	<link rel="stylesheet" href="css/style.css">
    	<script type="text/javascript">      
</script>
</head>

<body>
	<div class="login-page bk-img">
		<div class="form-content">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<h1 class="text-center text-bold mt-2x">Lecturer Registration</h1>
						<div class="hr-dashed"></div>
						<div class="well row pt-2x pb-3x bk-light text-center">
                         <form method="post" class="form-horizontal" enctype="multipart/form-data" name="regform" onSubmit="return validate();">
                            <div class="form-group">
				    
                            <label class="col-sm-1 control-label">Name<span style="color:red">*</span></label>
			    <div class="col-sm-5">
                            	<input type="text" name="name" class="form-control" required>
                            </div>
				    
                            <label class="col-sm-1 control-label">Email<span style="color:red">*</span></label>
                            <div class="col-sm-5">
                            	<input type="text" name="email" class="form-control" required>
                            </div>
                            </div>

                            <div class="form-group">
                            <label class="col-sm-1 control-label">Password<span style="color:red">*</span></label>
                            <div class="col-sm-5">
                            	<input type="password" name="password" class="form-control" id="password" required >
                            </div>

                            <label class="col-sm-1 control-label">Course<span style="color:red">*</span></label>
                            <div class="col-sm-5">
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
			    </div>
							
			    <div class="form-group">
                            <label class="col-sm-1 control-label">Picture<span style="color:red">*</span></label>
                            <div class="col-sm-5">
                            <div><input type="file" name="image" class="form-control"></div>
                            </div>
			    </div>
                            
			    <br>
                            <button class="btn btn-primary" name="submit" type="submit">Register</button>
                            </form>
			    
			    <br>
			    <?php if(!empty($response)) { ?>
			    <div class="response <?php echo $response["type"]; ?>
			    ">
			    <?php echo $response["message"]; ?>
			    </div>
			    <?php }?>
							
                            <br>
                            <br>
			    <p>Already Have Account? <a href="lecturers-login.php" >Signin</a></p>
			    <p>Are You A Student? <a href="register-students.php" >Signup as student</a></p>
							
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
