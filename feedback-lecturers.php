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
	
	<title>Manage Feedback</title>

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
	<?php include('includes/header-lecturers.php');?>

	<div class="ts-main-content">
		<?php include('includes/leftbar-lecturers.php');?>
		<div class="content-wrapper">
			<div class="container-fluid">

				<div class="row">
					<div class="col-md-12">

						<h2 class="page-title">Manage Feedback</h2>

						<!-- Zero Configuration Table -->
						<div class="panel panel-default">
							<div class="panel-heading">Feedback</div>
						</div>
							
							<div class="panel-body">
							<?php if($error){?><div class="errorWrap" id="msgshow"><?php echo htmlentities($error); ?> </div><?php } 
				else if($msg){?><div class="succWrap" id="msgshow"><?php echo htmlentities($msg); ?> </div><?php }?>
								<table id="zctb" class="display table table-striped table-bordered table-hover" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>#</th>
											<th>Course</th>
											<th>Title</th>
											<th>Feedback</th>
											<th>Reply</th>	
										</tr>
									</thead>
									<tbody>

<?php
$emailforcourse = $_SESSION['alogin'];
$receiver = 'Lecturers';
$sql = "CALL lecturerFeedbackInfo(:emailforcourse, :receiver)";
$query = $dbh -> prepare($sql);
$query-> bindParam(':emailforcourse', $emailforcourse, PDO::PARAM_STR);
$query-> bindParam(':receiver', $receiver, PDO::PARAM_STR);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;

if($query->rowCount() > 0)
{
foreach($results as $result)
{				?>	
										<tr>
											<td><?php echo $cnt;?></td>
											<td>
												<?php 
												if ($result->course == "3474") {
													echo '<p>.NET</p>';
												}
												else if($result->course == "8473") {
													echo '<p>Algoritmer og datastrukturer</p>';
												}
												else if($result->course == "1273") {
													echo '<p>Datasikkerhet i utvikling og drift</p>';
												}
												else if($result->course == "8674") {
													echo '<p>Bildeanalyse</p>';
												}
												else if($result->course == "9375") {
													echo '<p>Lineær algebra og integraltransformer</p>';
												}
												else if($result->course == "7573") {
													echo '<p>Autonome kjøretøy</p>';
												}
												?></td>
											<td><?php echo htmlentities($result->title);?></td>
                                            <td><?php echo htmlentities($result->feedbackdata);?></td>
											<td>
										
											<?php
											if($result->answered == 0) {
												echo '<a href=sendreply-lecturers.php?reply=';
												echo htmlentities($result->sender);
												echo '&id=';
												echo htmlentities($result->id);
												echo '>&nbsp; <i class="fa fa-mail-reply"></i></a>&nbsp;&nbsp;';
											}?>
											
											</td>
										</tr>
											<?php $cnt=$cnt+1; }} ?>
									</tbody>
								</table>
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
