<?php
session_start();
error_reporting(0);
include '../includes/config.php';
$studentId = $_GET['id'];
if (empty($studentId)){
    die(json_encode([
        'status'=>false,
        'alert'=>"Student Id can't be empty. Please write student's id !"
    ]));
}

$studentId = intval($studentId);
if (isset($studentId) && $studentId == 0){
    die(json_encode([
        'status'=>false,
        'alert'=>"Only integer number allowed to write"
    ]));
}
$sql = "SELECT * FROM students where id=:id LIMIT 1";
$query = $dbh->prepare($sql);
$query-> bindParam(':id', $studentId, PDO::PARAM_INT);
$query-> execute();
$result=$query->fetch(PDO::FETCH_OBJ);
if (!empty($result)){
    die(json_encode([
        'status'=>true,
        'data'=>$result
    ]));
}else{
    die(json_encode([
        'status'=>false,
        'msg'=>'Record not found'
    ]));
}
