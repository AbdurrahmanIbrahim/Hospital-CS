<?php
 include '../functions.php';
  if(!isLoggedIn()){
     $_SESSION['error'] = 'Login to continue';
      header('location:../login/');
  }
$user_id = getId();
if(isset($_POST)){
   $full_name = sanitize($_POST['full_name']);
   $email = sanitize($_POST['email']);
   $phone = sanitize($_POST['phone']);

   // $difficulty = sanitize($_POST['level']);
   // $interest = sanitize($_POST['interest']);
   // $language = sanitize($_POST['language']);
   // $goals = sanitize($_POST['goals']);
   if(empty($full_name)){

        $_SESSION['error'] = 'Enter valid Name';
        echo "<script>window.history.back();</script>";
        exit;


   }



    if(empty($email) OR !filter_var($email,FILTER_VALIDATE_EMAIL)){
        $_SESSION['error'] = 'Enter valid email';
        echo "<script>window.history.back();</script>";
        exit;
     }

     $sql = "SELECT id FROM users WHERE email = '$email' AND id != '$user_id'";
     $run = $db->query($sql);
     if($run->num_rows > 0){
       $_SESSION['error'] = 'This Email already exist';
        echo "<script>window.history.back();</script>";
        exit;
    }

     if(empty($phone)){
        $_SESSION['error'] = 'Enter valid phone number';
        echo "<script>window.history.back();</script>";
        exit;
     }

     $sql = "SELECT id FROM users WHERE phone = '$phone' AND id != '$user_id'";
     $run = $db->query($sql);
     if($run->num_rows > 0){
       $_SESSION['error'] = 'This Phone number already exist';
        echo "<script>window.history.back();</script>";
        exit;
    }

    
    
    $sql = "UPDATE users SET name = '$full_name',email='$email',phone = '$phone' WHERE id = '$user_id'";
    $run = $db->query($sql);
    if($run){
          $_SESSION['full_name'] = $full_name;
          $_SESSION['email'] = $email;
          $_SESSION['phone'] = $phone;
          $_SESSION['success'] = 'Successfully updated profile...';
        echo "<script>window.location.href='index.php';</script>";
        exit;
    }else{
         $_SESSION['error'] = 'error occured';
        echo "<script>window.history.back();</script>";
        exit;
    }





}




?>