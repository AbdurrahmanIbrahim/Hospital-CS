<div class="sidebar-overlay" onclick="toggleSidebar()"></div>
<script src="<?=ROOT_URL?>js/sweetalert.min.js"></script>
<link rel="stylesheet" href="<?=ROOT_URL?>css/sweetalert2.min.css">
<script src="<?=ROOT_URL?>/js/jquery.min.js" type="text/javascript"></script>
    <script type="text/javascript">

     var error_msg = "<?=!empty($_SESSION['error']) ? $_SESSION['error'] : ''?>";
    var success_msg = "<?=!empty($_SESSION['success']) ? $_SESSION['success'] : ''?>";

    if(error_msg.trim() != ''){
      swal('error',error_msg,'warning');
    }else if(success_msg.trim() != ''){
     swal('success',success_msg,'success');
    }

<?php unset($_SESSION['error']); unset($_SESSION['success']); ?>

    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('open');
        document.querySelector('.sidebar-overlay').classList.toggle('active');
    }

  </script>
