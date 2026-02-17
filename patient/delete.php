<?php 
include '../functions.php';
if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type']!=5)) {
    $_SESSION['error'] = 'Login To Continue';
    header('Location: ../login/index.php');
    exit;
}

if(empty($_GET['id'])){
    $_SESSION['error'] = "Invalid request";
    echo "<script>window.history.back()</script>";
    exit;
}


$patient = sanitize($_GET['id']);

$sql = "UPDATE users SET status = -1 WHERE id = '$patient' AND type = 1";
$run = $db->query($sql);
if(!$run){
  $_SESSION['error'] = "Invalid request";
    echo "<script>window.history.back()</script>";
    exit;
}

    $_SESSION['success'] = "Staff deleted successfully!";
    header('location:view.php');
    exit;
