<?php
session_start();
require_once 'config/db.php';

$column_name = 'leave_name'; 
$res = $conn->query("SHOW COLUMNS FROM `leave_types` WHERE Field IN ('leave_name', 'name', 'leave_type', 'type')");
if ($res && $res->num_rows > 0) {
    $detected = $res->fetch_assoc();
    $column_name = $detected['Field'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_status'])) {
    $leave_id = mysqli_real_escape_string($conn, $_POST['id']);
    $current_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $new_status = 'Inactive';
    $res_enum = $conn->query("SHOW COLUMNS FROM `leave_types` LIKE 'status'");
    if ($res_enum && $res_enum->num_rows > 0) {
        $col_info = $res_enum->fetch_assoc();
        $type_str = $col_info['Type'];
        if (strpos(strtolower($type_str), 'deactivated') !== false) {
            $new_status = (strtolower($current_status) === 'active') ? 'Deactivated' : 'Active';
        } else {
            $new_status = (strtolower($current_status) === 'active') ? 'Inactive' : 'Active';
        }
    }

    if (is_numeric($leave_id)) {
        $conn->query("UPDATE `leave_types` SET `status` = '$new_status' WHERE `id` = '$leave_id'");
    } else {
        $leave_name_db = mysqli_real_escape_string($conn, $_POST['leave_name_hidden']);
        $check_exist = $conn->query("SELECT id FROM `leave_types` WHERE `$column_name` = '$leave_name_db'");
        if ($check_exist && $check_exist->num_rows > 0) {
            $conn->query("UPDATE `leave_types` SET `status` = '$new_status' WHERE `$column_name` = '$leave_name_db'");
        } else {
            $conn->query("INSERT INTO `leave_types` (`$column_name`, `status`, `max_days`) VALUES ('$leave_name_db', '$new_status', 10)");
        }
    }
    header("Location: status_leave_type.php?success=1");
    exit();
}

$core_leaves = ['Sick Leave', 'Paid Leave', 'Unpaid Leave', 'Urgent Leave'];
$final_list = [];

foreach ($core_leaves as $cl) {
    $status = 'Active';
    $db_id = 'core_' . strtolower(str_replace(' ', '_', $cl));
    
    $check = $conn->query("SELECT id, `status` FROM `leave_types` WHERE `$column_name` = '$cl'");
    if ($check && $check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $db_id = $row['id'];
        $status = $row['status'];
    }
    $final_list[] = ['id' => $db_id, 'name' => $cl, 'status' => $status];
}

$query = "SELECT * FROM `leave_types` ORDER BY `id` ASC";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $name = isset($row[$column_name]) ? $row[$column_name] : '';
        if (!in_array($name, $core_leaves) && !empty($name)) {
            $final_list[] = ['id' => $row['id'], 'name' => $name, 'status' => $row['status']];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Status Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; }
        .feature-block { background: white; border-radius: 16px; padding: 30px; border: 1px solid #e2e8f0; }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 800px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-dark m-0">Activate or Deactivate Leave Types</h4>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3 px-3">Dashboard</a>
        </div>
        
        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success mb-4">Leave configuration status updated successfully!</div>
        <?php } ?>
        
        <div class="feature-block">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Leave Name</th>
                            <th>Current Status</th>
                            <th class="text-end">Action Panel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($final_list as $row) {
                            $is_active = (strtolower($row['status']) === 'active');
                            $btnClass = $is_active ? 'btn-warning' : 'btn-success';
                            $btnText = $is_active ? 'Deactivate' : 'Activate';
                            $badgeClass = $is_active ? 'bg-success' : 'bg-danger';
                        ?>
                        <tr>
                            <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $row['status']; ?></span></td>
                            <td class="text-end">
                                <form action="status_leave_type.php" method="POST" class="m-0">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="status" value="<?php echo $row['status']; ?>">
                                    <input type="hidden" name="leave_name_hidden" value="<?php echo htmlspecialchars($row['name']); ?>">
                                    <button type="submit" name="toggle_status" class="btn btn-sm <?php echo $btnClass; ?> px-3 fw-semibold rounded-3"><?php echo $btnText; ?></button>
                                </form>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>