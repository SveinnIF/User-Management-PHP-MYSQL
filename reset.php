<?php
session_start();
error_reporting(0);
include('includes/config.php');
$result = "";

if (isset($_GET["i"]) && isset($_GET["h"])) {
  
  // (B) CHECK IF VALID REQUEST
  $stmt = $dbh->prepare("SELECT * FROM `password_reset` WHERE `id`=?");
  $stmt->execute([$_GET["i"]]);
  $request = $stmt->fetch();
  if (is_array($request)) {
    if ($request["reset_hash"] != $_GET["h"]) { $result = "Invalid request"; }
  } else { $result = "Invalid request"; }
  
  // (C) CHECK EXPIRED
 $prvalid = 400;
  if ($result=="") {
    $now = strtotime("now");
    $expire = strtotime($request["reset_time"]) + $prvalid;
    if ($now >= $expire) { $result = "Request expired"; }
  }


    // UPDATE PASSWORD
  if ($result=="") {
    $password=md5($_POST['password']);
    $newpassword=md5($_POST['newpassword']);
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
    $msg="Your Password succesfully changed";
    }
  else {
    $error="Enter password."; 
  }
  }
}
 
// (E) INVALID REQUEST
else { $result = "Invalid request"; }
 
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
<script type="text/javascript">
function valid()
{
if(document.chngpwd.newpassword.value!= document.chngpwd.confirmpassword.value)
{
alert("New Password and Confirm Password Field do not match  !!");
document.chngpwd.confirmpassword.focus();
return false;
}
return true;
}
</script>
</script>
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
  <div class="login-page bk-img">
    <div class="form-content">
      <div class="container">
        <div class="row">
          <div class="col-md-6 col-md-offset-3">
            <h1 class="text-center text-bold mt-4x">Change Password</h1>
            <div class="well row pt-2x pb-3x bk-light">
              <div class="col-md-8 col-md-offset-2">
                <form method="post" name="chngpwd" class="form-horizontal" onSubmit="return valid();">
                    
                      
                <?php if($error){?><div class="errorWrap"><strong>ERROR</strong>:<?php echo htmlentities($error); ?> </div><?php } 
        else if($msg){?><div class="succWrap"><strong>SUCCESS</strong>:<?php echo htmlentities($msg); ?> </div><?php }?>
                      
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