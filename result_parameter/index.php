<?php
include '../functions.php';

/* =========================
   AUTHORIZATION
========================= */
if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 2)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'lab';

/* =========================
   VALIDATE REQUEST
========================= */
if (empty($_GET['id'])) {
    $_SESSION['error'] = 'Invalid request';
    echo "<script>window.history.back()</script>";
    exit;
}

/* =========================
   DECODE TEST ID
========================= */
$encodedId = sanitize($_GET['id']);
$encodedId = filter_var($encodedId, FILTER_VALIDATE_INT);

if (!$encodedId) {
    $_SESSION['error'] = 'Invalid test ID';
    echo "<script>window.history.back()</script>";
    exit;
}

$test_id = $encodedId - 1200;

/* =========================
   FETCH TEST INFO
========================= */
$sql = "SELECT * FROM tests WHERE id = '$test_id' AND status = 1";
$run = $db->query($sql);

if (!$run || $run->num_rows == 0) {
    $_SESSION['error'] = 'Test not found';
    echo "<script>window.history.back()</script>";
    exit;
}

$test_info = $run->fetch_assoc();

/* =========================
   FETCH TEST PARAMETERS
========================= */
$parameters = [];

$sql = "SELECT * FROM result_parameters 
        WHERE test_id = '$test_id' AND status = 1
        ORDER BY id ASC";

$run = $db->query($sql);

if ($run) {
    while ($row = $run->fetch_assoc()) {
        $parameters[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= APP_NAME ?> | Drug Categories</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../styles/styles.css">
<style>
.status-active { color:#0a7a0a; font-weight:600; }
.status-inactive { color:#c0392b; font-weight:600; }
.table-actions a { margin-right:8px; }
   .status-active {
            color: #0a7a0a;
            font-weight: 600;
        }
        .status-inactive {
            color: #c0392b;
            font-weight: 600;
        }
        .table-actions a {
            margin-right: 8px;
        }

        /* No staff message styling */
.card-body > p {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
    font-size: 16px;
    background-color: #f8f9fa;
    border-radius: 8px;
    border: 1px dashed #dee2e6;
    margin: 20px 0;
}

/* Edit button styling */
.table-actions a[href*="index.php"],.modal_btn {
    display: inline-block;
    padding: 6px 12px;
    background-color: #28a745;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid #28a745;
}

.table-actions a[href*="index.php"]:hover {
    background-color: #218838;
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.modal_btn{
    background-color: blue;
}

/* Delete button styling */
 .delete-btn {
    display: inline-block;
    padding: 6px 12px;
    background-color: #dc3545;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid #dc3545;
}

 .delete-btn:hover {
    background-color: #c82333;
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

    .search-section {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .search-form {
        width: 100%;
    }
    
    .search-filters {
        display: flex;
        align-items: flex-start;
        gap: 20px;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
    }
    
    .search-input-wrapper {
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .search-btn:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }
    
    .reset-btn:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .search-filters {
            flex-direction: column;
            gap: 16px;
        }
        
        .filter-group {
            margin-left: 0 !important;
            width: 100%;
        }
        
        .filter-group:first-child {
            width: 100%;
        }
        
        #staffType {
            width: 100% !important;
        }
        
        .search-input-wrapper {
            flex-direction: column;
        }
        
        #searchQuery {
            border-radius: 6px !important;
            border-right: 1px solid #ddd !important;
            margin-bottom: 8px;
        }
        
        .search-btn {
            border-radius: 6px !important;
            width: 100%;
        }
        
        .reset-btn {
            width: 100%;
            text-align: center;
        }
    }
    
    /* Focus states */
    #staffType:focus,
    #searchQuery:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
    }
    
    /* Placeholder styling */
    #searchQuery::placeholder {
        color: #6c757d;
        opacity: 0.8;
    }
</style>
</head>

<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

<div class="card">

    <!-- CARD HEADER -->
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h2>Set Result Parameters</h2>
        <a href="view.php" class="btn btn-primary">← Back to Tests</a>
    </div>

    <div class="card-body" style="padding:24px;">

        <!-- TEST INFO -->
        <table class="table" style="max-width:600px;margin-bottom:24px;">
            <tr>
                <th>Test Name</th>
                <td><?= htmlspecialchars($test_info['name']) ?></td>
            </tr>
            <tr>
                <th>Specimen Required</th>
                <td><?= htmlspecialchars(get("name","specimen",$test_info['specimen'])) ?></td>
            </tr>
        </table>

        <?php if (count($parameters) == 0 && empty($_SESSION['parameter_count'])): ?>

        <!-- PARAMETER COUNT FORM -->
        <form method="POST" action="process.php" style="max-width:500px;">
            <div class="form-group">
                <label>Number of Parameters *</label>
                <input type="number" name="count" class="form-control" required>
            </div>

            <input type="hidden" name="test_id" value="<?= $test_id ?>">

            <div style="text-align:right;margin-top:16px;">
                <button type="submit" class="btn btn-primary">Add Parameters</button>
            </div>
        </form>

        <?php else: ?>

        <!-- PARAMETERS TABLE -->
        <div class="table-responsive">
            <table class="table" id="my_table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Parameter</th>
                        <th>Unit</th>
                        <th>Reference Range</th>
                        <th colspan="2">Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (!empty($_SESSION['parameter_count'])): 
                    $count = $_SESSION['parameter_count'];
                ?>

                <form method="POST" action="add_parameters.php">
                    <input type="hidden" name="test_id" value="<?= $test_id ?>">
                    <input type="hidden" name="parameter_count" value="<?= $count ?>">

                    <?php for ($i = 1; $i <= $count; $i++): ?>
                    <tr>
                        <td><?= $i ?></td>
                        <td><input type="text" name="parameter_<?= $i ?>" class="form-control" required></td>
                        <td><input type="text" name="unit_<?= $i ?>" class="form-control"></td>
                        <td><input type="text" name="range_<?= $i ?>" class="form-control"></td>
                        <td colspan="2"></td>
                    </tr>
                    <?php endfor; ?>

                </tbody>
            </table>

            <div style="text-align:right;margin-top:16px;">
                <button type="submit" class="btn btn-primary">Save Parameters</button>
            </div>
            </form>

                <?php elseif (count($parameters) > 0): 
                    $counter = 1;
                    foreach ($parameters as $parameter):
                ?>

                <tr>
                    <form method="POST" action="edit.php">
                        <input type="hidden" name="test_id" value="<?= $test_id ?>">
                        <input type="hidden" name="result" value="<?= $parameter['id'] ?>">

                        <td><?= $counter++ ?></td>
                        <td><input type="text" name="parameter" class="form-control" value="<?= htmlspecialchars($parameter['name']) ?>"></td>
                        <td><input type="text" name="unit" class="form-control" value="<?= htmlspecialchars($parameter['unit']) ?>"></td>
                        <td><input type="text" name="range" class="form-control" value="<?= htmlspecialchars($parameter['para_range']) ?>"></td>

                        <td class="table-actions">
                            <button type="submit" class="btn btn-primary">Edit</button>
                        </td>
                        <td class="table-actions">
                            <a onclick="del_result(<?= $parameter['id'] ?>)" class="delete-btn">Delete</a>
                        </td>
                    </form>
                </tr>

                <?php endforeach; ?>

                </tbody>
            </table>

            <div style="margin-top:16px;">
                <button class="btn btn-secondary" onclick="add_parameter()">+ Add Parameter</button>
            </div>

                <?php endif; ?>
        </div>

        <?php endif; ?>

    </div>
</div>

</div>

<!-- HIDDEN FORM -->
<form method="POST" action="add.php" id="my_form">
    <input type="hidden" name="test_id" value="<?= $test_id ?>">
    <input type="hidden" name="parameter">
    <input type="hidden" name="unit">
    <input type="hidden" name="range">
</form>


<?php include '../includes/footer.php'; ?>
</main>

<script>

function del_result(result_id) {
    swal("Are you sure?", "This result parameter will be deleted.", "warning", {
        buttons: ["Cancel", "Yes, delete"]
    }).then((ok) => {
        if (ok) {
            window.location.href =
                "del.php?result=" + result_id + "&test_id=<?= $test_id ?>";
        }
    });
}

  function add_parameter(){
    var my_table = document.querySelector('#my_table');
    var my_count = document.querySelectorAll('#my_table form').length;
    my_count++;


    $('#my_table').append('<tr id="my_form_'+my_count+'"><td>#</td> <td><input type="text" id="parameter_'+my_count+'" class="form-control" ></td><td><input type="text" id="unit_'+my_count+'" class="form-control"></td><td><input type="text" id="range_'+my_count+'" class="form-control"></td><th colspan=""><input type="submit" name="" value="Add" class="btn btn-primary" onclick="submit_form('+my_count+')"></th><th colspan=""><a class="delete-btn" onclick="remove_row('+my_count+')">remove</a></th></tr>');
  }

    function remove_row(my_count){
     $('#my_form_'+my_count).remove();
    
   }

    function submit_form(my_count){
       var name = $('#parameter_'+my_count).val();
       var unit = $('#unit_'+my_count).val();
       var range =  $('#range_'+my_count).val();


       $('input[name="parameter"]').val(name);
       $('input[name="unit"]').val(unit);
       $('input[name="range"]').val(range);


       $('#my_form').submit();




   }
</script>

</body>
</html>
