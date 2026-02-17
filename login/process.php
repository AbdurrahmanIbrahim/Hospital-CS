<?php 
   include '../functions.php';
     if(isset($_POST)){

     $email = sanitize($_POST['user_name']);
    $password = sanitize($_POST['password']);

   


   $sql = "SELECT * FROM users WHERE email = '$email' AND status = 1";
   	$run = $db->query($sql);
   	if($run->num_rows > 0){

   		$info = $run->fetch_assoc();
   		$hash = $info['password'];
   		if(password_verify($password, $hash)){
          $_SESSION['full_name'] = $info['name'];
          $_SESSION['email'] = $email;
          $_SESSION['phone'] = $phone_num;
          $_SESSION['type'] = $info['type'];
          $_SESSION['pic'] = $info['pic'];
          header('location:../index.php');
   		}else{
   		 $_SESSION['error'] = 'Invalid login details';
          echo "<script>window.history.back();</script>";
          exit;
   		}

   	}else{
   		  $_SESSION['error'] = 'Invalid login details';
          echo "<script>window.history.back();</script>";
          exit;

   	}
   }else{
      $_SESSION['error'] = 'Error occured,try again';
    header('location:index.php');
   }

 ?>