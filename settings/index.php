<?php
   include '../functions.php';
  if(!isLoggedIn() or $_SESSION['type']!=0){
    $_SESSION['error'] = 'Login To Continue';
      echo "<script>window.location.href='../login/index.php'</script>";
      exit;
   }

   $location = 'admin';

     $action = 'Add';

    $h = getId();
    $sql = "SELECT * FROM hospital_details WHERE user_id = '$h'";
    $run = $db->query($sql);
    // if($run->num_rows == 0){
    //     $_SERVER['error'] = 'Hospital Details Not Found';
    //        echo "<script>window.history.back()</script>";
    //         exit;
    // }
    $hospital= $run->fetch_assoc();
    $action = 'Update';




$hospitalImg =(!empty($hospital['logo'])) ? '../images/logos/'.$hospital['logo']  : '';


 ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=APP_NAME?> | Hospital Management</title>
    <link rel="stylesheet" type="text/css" href="../styles/styles.css">
    <style type="text/css">
 
    </style>
</script>
</head>
<body>

    <!-- Sidebar -->
     
     <?php include '../includes/side_nav.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
          

          <?php include '../includes/header.php' ?>

        <div class="content-scroll">

                    <div class="card">
            <div class="card-header">
                <h2>Hospital Information</h2>
            </div>
            <div class="card-body" style="padding: 32px;">
                <form id="hospitalForm" method="POST" action = "process.php" enctype="multipart/form-data">
                    <!-- Hospital Name -->
                    <div class="form-group">
                        <label for="hospitalName">Hospital Name *</label>
                        <input type="text" id="hospitalName" name="hospital_name" placeholder="Enter hospital name" value="<?php echo isset($hospital['name']) ? htmlspecialchars($hospital['name']) : ''; ?>" required>
                    </div>

                    <!-- Contact Information Row -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="hospitalEmail">Email Address *</label>
                            <input type="email" id="hospitalEmail" name="hospital_email" placeholder="contact@hospital.com" value="<?php echo isset($hospital['email']) ? htmlspecialchars($hospital['email']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="hospitalPhone">Phone Number *</label>
                            <input type="tel" id="hospitalPhone" name="hospital_phone" placeholder="+1 (555) 123-4567" value="<?php echo isset($hospital['phone']) ? htmlspecialchars($hospital['phone']) : ''; ?>" required>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="form-group">
                        <label for="hospitalAddress">Full Address *</label>
                        <textarea id="hospitalAddress" name="hospital_address" rows="3" placeholder="123 Medical Street, City, State, ZIP Code" required><?php echo isset($hospital['address']) ? htmlspecialchars($hospital['address']) : ''; ?></textarea>
                    </div>

                    <!-- Additional Information -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="hospitalWebsite">Website</label>
                            <input type="url" id="hospitalWebsite" name="hospital_website" placeholder="https://www.hospital.com" value="<?php echo isset($hospital['website']) ? htmlspecialchars($hospital['website']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="establishedYear">Established Year</label>
                            <input type="number" id="establishedYear" name="established_year" placeholder="1990" min="1800" max="<?php echo date('Y'); ?>" value="<?php echo isset($hospital['year']) ? htmlspecialchars($hospital['year']) : ''; ?>">
                        </div>

                        <input type="hidden" name="id" value="<?php echo isset($hospital['id']) ? htmlspecialchars($hospital['id']) : ''; ?>">
                        <!-- Hospital Logo -->
<div class="form-group">
    <label for="hospitalLogo">Hospital Logo</label>

    <input 
        type="file" 
        id="hospitalLogo" 
        name="hospital_logo" 
        accept="image/*"
        onchange="previewLogo(event)"
    >

    <!-- Preview Area -->
    <div style="margin-top: 12px;">
        <img 
            id="logoPreview"
            src="<?= !empty($hospitalImg) ? $hospitalImg : '' ?>"
            alt="Hospital Logo Preview"
            style="
                max-width: 180px;
                max-height: 180px;
                border: 1px solid #ddd;
                padding: 6px;
                border-radius: 8px;
                display: <?= !empty($hospitalImg) ? 'block' : 'none' ?>;
            "
        >
    </div>
</div>


                    </div>

                    <div class="form-row">
                                        <!-- Submit Button -->
                <div class="form-group" style="margin-top: 24px; text-align: right;">
                    <button type="submit" class="btn btn-primary">
                        Save Hospital Information
                    </button>
                </div>
                </div>
            </form>

                    </div>
                </form>
            </div>
        </div>

           
        </div>
            <?php include '../includes/footer.php'; ?>
            <script>
function previewLogo(event) {
    const input = event.target;
    const preview = document.getElementById('logoPreview');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };

        reader.readAsDataURL(input.files[0]);
    }
}
</script>

    </main>

</body>
</html>