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
$result = "";

if (isset($_GET["i"]) && isset($_GET["h"])) {
  
  // (B) CHECK IF VALID REQUEST
  $stmt = $dbh->prepare("SELECT * FROM `password_reset` WHERE `id`=?");
  $stmt->execute([$_GET["i"]]);
  $request = $stmt->fetch();
  if (is_array($request)) {
    if ($request["reset_hash"] != $_GET["h"]) { $result = "Invalid request"; }
  } else { $result = "<p>Invalid request.</p>"; }
  
  // (C) CHECK EXPIRED
 $prvalid = 400;
  if ($result=="") {
    $now = strtotime("now");
    $expire = strtotime($request["reset_time"]) + $prvalid;
    if ($now >= $expire) { $result = "<p>Request expired</p>"; }
  }


    // UPDATE PASSWORD
  if ($result=="") {
    $password=password_hash("password", PASSWORD_DEFAULT);
    $newpassword=password_hash("newpassword", PASSWORD_DEFAULT);
    $id=$_GET["i"];
    $sql ="SELECT Password FROM lecturers WHERE id=:id";
    $query= $dbh -> prepare($sql);
    $query-> bindParam(':id', $id, PDO::PARAM_STR);
    $query-> execute();
    $results = $query -> fetchAll(PDO::FETCH_OBJ);
  
    if($query -> rowCount() > 0)
    {
    $con="update lecturers set password=:newpassword where id=:id";
    $chngpwd1 = $dbh->prepare($con);
    $chngpwd1-> bindParam(':id', $id, PDO::PARAM_STR);
    $chngpwd1-> bindParam(':newpassword', $newpassword, PDO::PARAM_STR);
    $chngpwd1->execute();
    $msg="<p>Your Password succesfully changed.</p>";
    $logger->info('En bruker har endret passordet sitt'); // logging
    }
  else {
    $error="<p>Enter password.</p>"; 
  }
  }
}
 
// (E) INVALID REQUEST
else { $result = "<p>Invalid request</p>"; }
 
// (F) OUTPUT RESULTS
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
          <div class="col-md-6 col-md-offset-3">
            <h1 class="text-center text-bold mt-4x">Change Password</h1>
            <div class="well row pt-2x pb-3x bk-light">
              <div class="col-md-8 col-md-offset-2">
                <form method="post" name="chngpwd" class="form-horizontal" onSubmit="return valid();">
                    

                      
                      <div class="form-group">
                        <label class="col-sm-4 control-label">New Password</label>
                        <div class="col-sm-8">
                          <input type="password" class="form-control" name="newpassword" id="newpassword" required>
                        </div>
                      </div>
                      <div class="hr-dashed"></div>

                      <div class="form-group">
                        <label class="col-sm-4 control-label">Confirm Password</label>
                        <div class="col-sm-8">
                          <input type="password" class="form-control" name="confirmpassword" id="confirmpassword" required>
                        </div>
                      </div>
                      <?=$result?>
                      <?=$error?>
                      <?=$msg?>
                      <div class="hr-dashed"></div>
                    
                
                      
                      <div class="form-group">
                        <div class="col-sm-8 col-sm-offset-4">
                
                          <button class="btn btn-primary" name="submit" type="submit">Save changes</button>
                </form>
                <br>
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
  <script type="text/javascript"></script>



</body>

</html>
