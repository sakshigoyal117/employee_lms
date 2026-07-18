<?php
session_start();
require_once 'config/db.php';

$message = "";

$column_name = 'leave_name'; 
$res = $conn->query("SHOW COLUMNS FROM `leave_types` WHERE Field IN ('leave_name', 'name', 'leave_type', 'type')");
if ($res && $res->num_rows > 0) {
    $detected = $res->fetch_assoc();
    $column_name = $detected['Field'];
}

$desc_col = 'description';
$res_desc = $conn->query("SHOW COLUMNS FROM `leave_types` WHERE Field IN ('description', 'desc', 'leave_desc')");
if ($res_desc && $res_desc->num_rows > 0) {
    $detected_desc = $res_desc->fetch_assoc();
    $desc_col = $detected_desc['Field'];
}


$db_leaves = [];
$query = "SELECT * FROM `leave_types` ORDER BY `id` ASC";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $db_leaves[] = $row;
    }
}


$predefined_leaves = [
    ['id' => 'pre-1', 'name' => 'Sick Leave', 'desc' => 'Medical and health related leaves'],
    ['id' => 'pre-2', 'name' => 'Paid Leave', 'desc' => 'Standard annual allocated paid leaves'],
    ['id' => 'pre-3', 'name' => 'Unpaid Leave', 'desc' => 'Leaves taken outside paid quota'],
    ['id' => 'pre-4', 'name' => 'Urgent Leave', 'desc' => 'Emergency or short notice leaves']
];


$final_dropdown_list = [];
foreach ($predefined_leaves as $pl) {
    $final_dropdown_list[] = [
        'id' => $pl['id'],
        'name' => $pl['name'],
        'desc' => $pl['desc'],
        'is_predefined' => true
    ];
}

foreach ($db_leaves as $dl) {
    $name = isset($dl[$column_name]) ? $dl[$column_name] : 'half day';
    $desc = isset($dl[$desc_col]) ? $dl[$desc_col] : 'for urgent work';
    
    
    $is_duplicate = false;
    foreach ($predefined_leaves as $pl) {
        if (strtolower(trim($pl['name'])) === strtolower(trim($name))) {
            $is_duplicate = true;
            break;
        }
    }
    if (!$is_duplicate) {
        $final_dropdown_list[] = [
            'id' => $dl['id'],
            'name' => $name,
            'desc' => $desc,
            'is_predefined' => false
        ];
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_config'])) {
    $target_id = $_POST['leave_id'];
    $new_name = mysqli_real_escape_string($conn, trim($_POST['leave_name']));
    $new_desc = mysqli_real_escape_string($conn, trim($_POST['description']));
    
    if (strpos($target_id, 'pre-') === 0) {
        
        $check = $conn->query("SELECT * FROM `leave_types` WHERE `$column_name` = '$new_name'");
        if ($check && $check->num_rows > 0) {
            $conn->query("UPDATE `leave_types` SET `$desc_col` = '$new_desc' WHERE `$column_name` = '$new_name'");
        } else {
            $conn->query("INSERT INTO `leave_types` (`$column_name`, `$desc_col`, `status`) VALUES ('$new_name', '$new_desc', 'Active')");
        }
        $message = "<div class='alert alert-success shadow-sm'>Default leave configuration modified successfully!</div>";
    } else {
        
        $safe_id = mysqli_real_escape_string($conn, $target_id);
        $update = $conn->query("UPDATE `leave_types` SET `$column_name` = '$new_name', `$desc_col` = '$new_desc' WHERE `id` = '$safe_id'");
        if ($update) {
            $message = "<div class='alert alert-success shadow-sm'>Leave configuration updated successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger shadow-sm'>Error updating record database profile.</div>";
        }
    }
    
    
    echo "<script>setTimeout(function(){ window.location.href='edit_leave_type.php'; }, 1000);</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Leave Configuration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; }
        .form-card { background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 8px 24px rgba(148, 163, 184, 0.06); }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 650px;">
        <div class="form-card p-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-primary m-0">Edit Leave Configuration</h3>
                <a href="admin_dashboard.php" class="btn btn-sm btn-outline-secondary rounded-3 fw-semibold px-3">Dashboard</a>
            </div>
            
            <?php echo $message; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Select Target Leave Type to Edit</label>
                    <select name="leave_id" class="form-select" id="leaveSelect" required onchange="populateFields()">
                        <option value="" selected disabled>Choose configuration...</option>
                        <?php 
                        $idx = 1;
                        foreach ($final_dropdown_list as $item) {
                            echo "<option value='{$item['id']}' data-name='".htmlspecialchars($item['name'])."' data-desc='".htmlspecialchars($item['desc'])."'>#LV-{$idx} - ".htmlspecialchars($item['name'])."</option>";
                            $idx++;
                        }
                        ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Leave Name Type</label>
                    <input type="text" name="leave_name" id="leaveName" class="form-control" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary">Operational Description</label>
                    <textarea name="description" id="leaveDesc" class="form-control" rows="3" required></textarea>
                </div>
                
                <button type="submit" name="update_config" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3">Update Configuration</button>
            </form>
        </div>
    </div>

    <script>
    function populateFields() {
        var select = document.getElementById("leaveSelect");
        var selectedOption = select.options[select.selectedIndex];
        
        if(selectedOption.value !== "") {
            document.getElementById("leaveName").value = selectedOption.getAttribute("data-name");
            document.getElementById("leaveDesc").value = selectedOption.getAttribute("data-desc");
        }
    }
    </script>
</body>
</html>