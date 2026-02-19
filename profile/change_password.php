<?php
 include '../functions.php';
  if(!isLoggedIn()){
     $_SESSION['error'] = 'Login to continue';
      header('location:../login/');
  }
$user_id = getId();

  if(isset($_POST)){
    $password = sanitize($_POST['old_password']);
    $new_password = sanitize($_POST['new_password']);
      $confirm_password = sanitize($_POST['confirm_password']);    
    
       $get_password = "SELECT password FROM users WHERE id = '$user_id'";
       $run = $db->query($get_password);
       $info = $run->fetch_assoc();
       $hash = $info['password'];

          if(password_verify($password, $hash)){
            if($new_password == '' || strlen($new_password) < 6){
                     $_SESSION['error'] = 'Enter new password (Must be 6 or more characters/numbers)';
                    echo "<script>window.history.back();</script>";
                     exit;
            }
            if($new_password != $confirm_password){
                     $_SESSION['error'] = 'Password does not match';
                    echo "<script>window.history.back();</script>";
                     exit;
            }
            $password = password_hash($new_password, PASSWORD_DEFAULT);
         }else{
                   $_SESSION['error'] = 'Old password incorrect';
                    echo "<script>window.history.back();</script>";
                     exit;
          }

      $update = "UPDATE users SET password = '$password' WHERE id = '$user_id'";
       $run = $db->query($update);     
     if($run){
          $_SESSION['success'] = 'Successfully updated password';
        echo "<script>window.location.href='index.php';</script>";
        exit;
     }else{
          $_SESSION['error'] = 'error occured,try again';
                    echo "<script>window.history.back();</script>";
                     exit;
    
     }    
 

  }
  

?>