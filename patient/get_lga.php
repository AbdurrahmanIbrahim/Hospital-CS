<?php
 include '../functions.php';


$userId = getId();



if(empty($_POST['state'])){
	  $response = array('status'=>'failed','msg'=>'An error occured');
      echo json_encode($response);
      exit();
}

$state = sanitize($_POST['state']);
$state = filter_var($state,FILTER_VALIDATE_INT);




$sql = "SELECT * FROM states WHERE id = '$state'";
$run = $db->query($sql);
if($run->num_rows == 0){
      $response = array('status'=>'failed','msg'=>'An error occured');
      echo json_encode($response);
      exit();
}



$lgas = [];

$sql= "SELECT * FROM lgas WHERE state_id = '$state'";
$run = $db->query($sql);
while ($row = $run->fetch_assoc())$lgas[] = $row;



if(!$run){
      $response = array('status'=>'failed','msg'=>'An error occured');
      echo json_encode($response);
      exit();
}




  $response = array('status'=>'success','msg'=>'','lgas'=>$lgas);
      echo json_encode($response);
      exit();





