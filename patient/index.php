<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type']!=5)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'patient';
$action = 'Add';
$patient = [];

/* =========================
   Edit Mode
========================= */
if (!empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM users WHERE id='$id' AND type=1";
    $run = $db->query($sql);

    if ($run->num_rows == 0) {
        $_SESSION['error'] = 'Staff Not Found';
        echo "<script>window.history.back()</script>";
        exit;
    }

    $patient = $run->fetch_assoc();
    $action = 'Update';
}

$file_types = $scheme_types = $families = [];
$sql = "SELECT * FROM file_types WHERE status = 1";
$run = $db->query($sql);
while($row = $run->fetch_assoc())$file_types[] = $row;


$sql = "SELECT * FROM schemes WHERE status = 1";
$run = $db->query($sql);
while($row = $run->fetch_assoc())$scheme_types[] = $row;



$sql = "SELECT * FROM families WHERE status = 1";
$run = $db->query($sql);
while($row = $run->fetch_assoc())$families[] = $row;


$states = $lgas = [];

$sql= "SELECT * FROM states";
$run = $db->query($sql);
while ($row = $run->fetch_assoc())$states[] = $row;

$state_id = !(empty($patient['state'])) ? $patient['state'] : 0;

$sql= "SELECT * FROM lgas WHERE state_id = '$state_id'";
$run = $db->query($sql);
while ($row = $run->fetch_assoc())$lgas[] = $row;




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?> | Patient Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles.css">
    <style>
    .card-body h3 {
        color: var(--primary-dark);
        font-weight: 600;
        padding-bottom: 8px;
        margin: 32px 0 20px 0;
        border-bottom: 2px solid var(--primary);
        position: relative;
        font-size: 1.3rem;
        letter-spacing: 0.5px;
    }
    
    .card-body h3:first-child {
        margin-top: 0;
    }
    
    .card-body h3:before {
        content: '';
        position: absolute;
        left: 0;
        bottom: -2px;
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: 3px 3px 0 0;
    }
</style>
</head>

<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

<div class="card">
    <div class="card-header">
        <h2><?= $action ?> Patient</h2>
    </div>

    <div class="card-body" style="padding:32px;">
        <form method="POST" action="process.php">

            <!-- ================= BASIC INFO ================= -->
            <h3>Basic Information</h3>

            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" required
                    value="<?= $patient['name'] ?? '' ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required
                        value="<?= $patient['email'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Phone *</label>
                    <input type="tel" name="phone" required
                        value="<?= $patient['phone'] ?? '' ?>">
                </div>
            </div>

     
            <!-- ================= PERSONAL INFO ================= -->
            <h3>Personal Information</h3>

            <div class="form-row">
                <div class="form-group">
                    <label>Date of Birth *</label>
                    <input type="date" name="dob" required
                        value="<?= $patient['dob'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender" required>
                        <option value="">-- Select --</option>
                        <option value="Male" <?= ($patient['gender'] ?? '')=='Male'?'selected':'' ?>>Male</option>
                        <option value="Female" <?= ($patient['gender'] ?? '')=='Female'?'selected':'' ?>>Female</option>
                        <option value="Other" <?= ($patient['gender'] ?? '')=='Other'?'selected':'' ?>>Other</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                  <div class="form-group">
                    <label>State *</label>
                    <select name="state" onchange="get_lga(this)" id="state" required>
                        <option value="">-- Select --</option>
                          <?php foreach($states as $state){
                             $selected = ($state['id'] == $state_id) ? 'selected' : '';
                              ?>
                              <option value="<?=$state['id']?>" <?=$selected?>><?=$state['name']?></option>
                         <?php  } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>LGA *</label>
                    <select name="lga" id="lga-option" required>
                        <option value="">-- Select --</option>
                                <?php foreach($lgas as $lga){
                                 $selected = (!empty($patient['lga']) and $patient['lga'] == $lga['id']) ? 'selected' : '';
                              ?>
                              <option value="<?=$lga['id']?>" <?=$selected?>><?=$lga['name']?></option>
                         <?php  } ?>
                    </select>
                </div>
                  <div class="form-group">
                <label>Tribe *</label>
                <input type="text" name="tribe" required
                    value="<?= $patient['tribe'] ?? '' ?>">
            </div>
              <div class="form-group">
                <label>Address *</label>
                <textarea name="address" required><?= $patient['address'] ?? '' ?></textarea>
            </div>
            </div>


<div class="form-row">
          

            <div class="form-group">
                <label>Marital Status</label>
                <select name="marital_status">
                    <option value="">-- Select --</option>
                    <option value="Single" <?= ($patient['marital_status'] ?? '')=='Single'?'selected':'' ?>>Single</option>
                    <option value="Married" <?= ($patient['marital_status'] ?? '')=='Married'?'selected':'' ?>>Married</option>
                    <option value="Divorced" <?= ($patient['marital_status'] ?? '')=='Divorced'?'selected':'' ?>>Divorced</option>
                </select>
            </div>
    </div>

            <!-- ================= MEDICAL INFO ================= -->
            <h3>Medical Information</h3>

            <div class="form-row">
                <div class="form-group">
                    <label>Blood Group *</label>
                    <select name="blood_group">
                        <option value="">-- Select --</option>
                        <?php
                        $bg = $patient['blood_group'] ?? '';
                        foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $b){
                            echo "<option ".($bg==$b?'selected':'').">$b</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Genotype *</label>
                    <select name="genotype">
                        <option value="">-- Select --</option>
                        <?php
                        $gt = $patient['genotype'] ?? '';
                        foreach(['AA','AS','SS','AC'] as $g){
                            echo "<option ".($gt==$g?'selected':'').">$g</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Known Allergies</label>
                <textarea name="allergies"><?= $patient['allergies'] ?? '' ?></textarea>
            </div>

            <div class="form-group">
                <label>Chronic Conditions</label>
                <textarea name="chronic_conditions"><?= $patient['chronic_condition'] ?? '' ?></textarea>
            </div>

            <!-- ================= NEXT OF KIN ================= -->
            <h3>Next of Kin</h3>

            <div class="form-row">
                <div class="form-group">
                    <label>Next of Kin *</label>
                    <input type="text" name="next_of_kin" required
                        value="<?= $patient['kin'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Next of Kin Phone *</label>
                    <input type="tel" name="next_of_kin_phone" required
                        value="<?= $patient['kin_phone'] ?? '' ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Relationship *</label>
                <input type="text" name="relationship" required
                    value="<?= $patient['kin_relationship'] ?? '' ?>">
            </div>

            <!-- ================= HOSPITAL INFO ================= -->
            <h3>Hospital Information</h3>
                 <div class="form-row">
                <div class="form-group">
                    <label>File Type *</label>
                    <select name="file_type">
                        <option value="">-- Select --</option>
                        <?php
                        $patient_file = $patient['file_type'] ?? '';
                        foreach($file_types as $ft){
                            echo "<option ".($patient_file==$ft['id']?'selected':'')." value='".$ft['id']."'>".$ft['name']."</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Scheme Type *</label>
                    <select name="scheme_type" required>
                        <option value="">-- Select --</option>
                        <?php
                        $st = $patient['scheme_type'] ?? '';
                        foreach($scheme_types as $s){
                            echo "<option ".($st==$s['id']?'selected':'')." value='".$s['id']."'>".$s['name']."</option>";
                        }
                        ?>
                    </select>
                </div>

                  <div class="form-group">
                    <label>Family *</label>
                    <select name="family">
                        <option value="">-- Select --</option>
                        <?php
                        $f = $patient['family'] ?? '';
                        foreach($families as $family){
                            echo "<option ".($f==$family['id']?'selected':'')." value='".$family['id']."'>".$family['name']."</option>";
                        }
                        ?>
                    </select>
                </div>

            </div>

            <!-- Hidden -->
            <input type="hidden" name="type" value="1">
            <?php if (!empty($patient['id'])): ?>
                <input type="hidden" name="id" value="<?= $patient['id'] ?>">
            <?php endif; ?>

            <!-- Submit -->
            <div class="form-group" style="text-align:right;margin-top:30px;">
                <button type="submit" class="btn btn-primary">
                    <?= $action ?> Patient
                </button>
            </div>

        </form>
    </div>
</div>

</div>

<?php include '../includes/footer.php'; ?>
<script type="text/javascript">
    
             function get_lga(x){


              var state_id = $(x).val();

           


              if(state_id!=''){
                $.ajax({
                type:'POST',
                url:'get_lga.php',
                data:'state='+state_id,
                 beforeSend:function(){
                  
                },

                success:function(result){
                    // alert(result);
                    // return;
                    // $('#loader').css('display','none');
                    result = JSON.parse(result);
                  
                   if(result.status == 'success'){
                     var lgas_elements = "";
                     var lgas = result.lgas;

                     for(i=0;i<lgas.length;i++){
                        lgas_elements+="<option value='"+lgas[i].id+"' data-price='"+lgas[i].price+"'>"+lgas[i].name+"</option>";
                     }


                     $('#lga-option').html(lgas_elements)
                   

                   }else{
                      swal('error',result.msg,'warning');
                   }
                  
                },

                  error: function(xhr, status, error) { // Callback function for error
                        // Handle errors
                      
                        console.error('AJAX error: ' + error);
                    }
            }); 
         }
      }
</script>
</main>

</body>
</html>
