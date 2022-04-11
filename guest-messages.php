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
include('includes/guestConfig.php');
if(strlen($_SESSION['alogin'])==0)
    {   
header('location:index.php');
}
else{
    
    if(isset($_POST['submit']))
    {   
      $id=uniqid();
      $title=$_POST['title'];
      $description=$_POST['description'];
      $course=$_POST['course'];
      $user=$_SESSION['alogin'];
      $receiver='Lecturers, Admin , Guest';
  
      $sql="CALL guestCommentInfo(:id, :user, :receiver, :course, :title, :description)";
      $query = $dbh->prepare($sql);
      if ($_POST['anon'] == 'anonymous') {
		$query-> bindParam(':user', $anon, PDO::PARAM_STR);
	} else {
		$query-> bindParam(':user', $user, PDO::PARAM_STR);
	  }
      $query-> bindParam(':id', $id, PDO::PARAM_STR);
      $query-> bindParam(':user', $user, PDO::PARAM_STR);
      $query-> bindParam(':receiver', $receiver, PDO::PARAM_STR);
      $query-> bindParam(':course', $course, PDO::PARAM_STR);
      $query-> bindParam(':title', $title, PDO::PARAM_STR);
      $query-> bindParam(':description', $description, PDO::PARAM_STR);
      $query->execute(); 
      $msg="Feedback Sent";
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
    
    <title>Guest site</title>

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
    <?php include('includes/header-guest.php');?>

    <div class="ts-main-content">
        <?php include('includes/leftbar-guest.php');?>
        <div class="content-wrapper">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-md-12">

                        <h2 class="page-title">Messages for Course: ITF25019-1 22V Datasikkerhet i utvikling og drift</h2>

                        <!-- Zero Configuration Table -->
                        <div class="panel panel-default">
                            <div class="panel-heading">List Users</div>
                            <div class="panel-body">
                            <?php if($error){?><div class="errorWrap" id="msgshow"><?php echo htmlentities($error); ?> </div><?php } 
                else if($msg){?><div class="succWrap" id="msgshow"><?php echo htmlentities($msg); ?> </div><?php }?>
                                <table id="zctb" class="display table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                               <th>#</th>
                                                <th>User</th>
                                                <th>Message</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>

<?php 
$receiver = $_SESSION['alogin'];
$sql = "CALL guestMessageTable(:receiver)";
$query = $dbh -> prepare($sql);
$query-> bindParam(':receiver', $receiver, PDO::PARAM_STR);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $result)
{               ?>  
                                        <tr>
                                            <td><?php echo htmlentities($cnt);?></td>
                                            <td><?php echo htmlentities($result->sender);?></td>
                                            <td><?php echo htmlentities($result->feedbackdata);?></td>
                                        </tr>
                                        <?php $cnt=$cnt+1; }} ?>
                                        
                                    </tbody>
                                </table>
<div class="panel panel-default">
                                    <div class="panel-heading">Reply form</div>
<?php if($error){?><div class="errorWrap"><strong>ERROR</strong>:<?php echo htmlentities($error); ?> </div><?php } 
                else if($msg){?><div class="succWrap"><strong>SUCCESS</strong>:<?php echo htmlentities($msg); ?> </div><?php }?>

<?php

// validtation  //
// define variables and set to empty values//
$titleErr = $messageErr =  "";
$title = $message =  "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (empty($_POST["title"])) {
	  $titleErr = "Title is required";
	} else {
	  $title = test_input($_POST["title"]);
	  // check if name only contains letters and whitespace
	  if (!preg_match("/^[a-zA-Z-' ]*$/",$title)) {
		$titleErr = "Only letters and white space allowed";
	  }
	}
}
	if (empty($_POST["title"])) {
		$messageErr = "";
	} else {
		$message = test_input($_POST["title"]);
		// check if name only contains letters and whitespace
		if (!preg_match("/^[a-zA-Z \-\'\,\.\?\!\/\(\)\%\+\=\"\^\r?\n æøåÆØÅ 0-9]*$/",$message)) {
		$messageErr = "Only letters and white space allowed";
		}
	}
	function test_input($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	  }

?>

<div class="panel-body">
<label class="col-sm-2 control-label">Title<span style="color:red">*</span></label>
<div class="col-sm-4">
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
<input type="text" name="title"  class="form-control" required value="<?php echo $title;?>">
  <span class="error"> <?php echo $titleErr;?></span>
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
</div>
<br>
<br>
<div class="form-group">
    <label class="col-sm-2 control-label">Message<span style="color:red">*</span></label>
    <div class="col-sm-4">
    <textarea class="form-control" required rows="5" name="description"></textarea >
	<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
  	<span class="error"> <?php echo $messageErr;?></span>
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

