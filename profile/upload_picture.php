<?php 
 include '../functions.php';
  if(!isLoggedIn()){
     $_SESSION['error'] = 'Login to continue';
      header('location:../login/');
  }
   $user_id = getId();

   $sql = "SELECT pic FROM users WHERE id = '$user_id'";
   $run = $db->query($sql);
   $user = $run->fetch_assoc();
 
  function uploadFile($file_name,$location,$file_n){

  

   $allowed_file_types = array('.jpeg','.png','.bmp','.jpg');
  if(isset($_FILES[$file_name.'']['name'])){
         $filename = $_FILES[$file_name.'']["name"];
         $file_basename = substr($filename, 0, strripos($filename, '.'));
         $file_ext = substr($filename, strripos($filename, '.'));
         $filesize = $_FILES[$file_name.'']["size"];
          if(!empty($filename)){
                 if ($filesize > 2000000) {

                       $_SESSION['error'] = $file_name." File is too large, Max: 2MB";
                       echo "<script>window.history.back();</script>";
                        exit;
                    

                }
                if (!in_array($file_ext,$allowed_file_types)){ 

                       $_SESSION['error'] = "Please select a file with any of these format;  \"pdf\".";
                       echo "<script>window.history.back();</script>";
                        exit;
   
                     
                }

                $new_file_name = $file_n.$file_ext;
                $_SESSION['pic'] = $new_file_name;
                $location = $location.$new_file_name;
                $temp = $_FILES[$file_name.'']['tmp_name'];

                  if(move_uploaded_file($temp, $location) ){
                        return $new_file_name;
                  }else{
                       return '';                      

                  }




            }

  }else{
       return '';
  }

}

if(isset($_POST)){
	$file_name =   empty($user['pic']) ?  random_characters(6).($user_id*2900) : explode('.',$user['pic'])[0];
	$uploaded = uploadFile('user_pic','../images/dp/',$file_name);
	if(!empty($uploaded)){

		$sql = "UPDATE users SET pic = '$uploaded' WHERE id = '$user_id'";
		$run = $db->query($sql);
	    if($run){
              $_SESSION['success'] = 'Successfully uploaded dp...';
             echo "<script>window.location.href='index.php';</script>";
             exit;
	    }else{
	        $_SESSION['error'] = 'error eccured while uploading dp,try again';
            echo "<script>window.history.back();</script>";
            exit;
	    }


	}else{
        $_SESSION['error'] = 'error eccured while uploading dp,try again';
		echo "<script>window.history.back();</script>";
        exit;
	}
}





 ?>