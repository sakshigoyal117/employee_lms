<?php
session_start();
require_once 'config/db.php';

$column_name = 'leave_name'; 
$res = $conn->query("SHOW COLUMNS FROM `leave_types` WHERE Field IN ('leave_name', 'name', 'leave_type', 'type')");
if ($res && $res->num_rows > 0) {
    $detected = $res->fetch_assoc();
    $column_name = $detected['Field'];
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $leave_id = mysqli_real_escape_string($conn, $_GET['id']);
    $action = $_GET['action'];
    
    $new_status = 'Inactive';
    $res_enum = $conn->query("SHOW COLUMNS FROM `leave_types` LIKE 'status'");
    if ($res_enum && $res_enum->num_rows > 0) {
        $col_info = $res_enum->fetch_assoc();
        $type_str = $col_info['Type'];
        if (strpos(strtolower($type_str), 'deactivated') !== false) {
            $new_status = ($action === 'activate') ? 'Active' : 'Deactivated';
        } else {
            $new_status = ($action === 'activate') ? 'Active' : 'Inactive';
        }
    }

    if (is_numeric($leave_id)) {
        $conn->query("UPDATE `leave_types` SET `status` = '$new_status' WHERE `id` = '$leave_id'");
    } else {
        $leave_name_db = mysqli_real_escape_string($conn, $_GET['name']);
        $check_exist = $conn->query("SELECT id FROM `leave_types` WHERE `$column_name` = '$leave_name_db'");
        if ($check_exist && $check_exist->num_rows > 0) {
            $conn->query("UPDATE `leave_types` SET `status` = '$new_status' WHERE `$column_name` = '$leave_name_db'");
        } else {
            $conn->query("INSERT INTO `leave_types` (`$column_name`, `status`, `max_days`) VALUES ('$leave_name_db', '$new_status', 10)");
        }
    }
    header("Location: view_leave_types.php");
    exit();
}

$core_leaves = [
    ['name' => 'Sick Leave', 'desc' => 'Medical and health related leaves'],
    ['name' => 'Paid Leave', 'desc' => 'Standard annual allocated paid leaves'],
    ['name' => 'Unpaid Leave', 'desc' => 'Leaves taken outside paid quota'],
    ['name' => 'Urgent Leave', 'desc' => 'Emergency or short notice leaves']
];

$final_list = [];
$counter = 1;

foreach ($core_leaves as $cl) {
    $status = 'Active';
    $db_id = 'core_' . strtolower(str_replace(' ', '_', $cl['name']));
    
    $check = $conn->query("SELECT id, `status` FROM `leave_types` WHERE `$column_name` = '{$cl['name']}'");
    if ($check && $check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $db_id = $row['id'];
        $status = $row['status'];
    }
    $final_list[] = ['id' => $counter++, 'db_id' => $db_id, 'name' => $cl['name'], 'desc' => $cl['desc'], 'status' => $status];
}

$query = "SELECT * FROM `leave_types` ORDER BY `id` ASC";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $name = isset($row[$column_name]) ? $row[$column_name] : '';
        $desc = isset($row['description']) ? $row['description'] : (isset($row['desc']) ? $row['desc'] : 'for urgent work');
        
        $is_core = false;
        foreach ($core_leaves as $cl) {
            if (strtolower(trim($cl['name'])) == strtolower(trim($name))) {
                $is_core = true;
                break;
            }
        }
        
        if (!$is_core && !empty($name)) {
            $final_list[] = ['id' => $counter++, 'db_id' => $row['id'], 'name' => $name, 'desc' => $desc, 'status' => $row['status']];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Leave Types - Nexus Prime Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; }
        .feature-block { background: white; border-radius: 16px; padding: 30px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(148, 163, 184, 0.05); }
    </style>
</head>
<body>
<div class="container-fluid px-5 py-5" style="max-width: 1200px;">
    
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="fw-bold text-primary m-0" style="font-size: 2.2rem; color: #2b44b8 !important;">Nexus Prime Tech</h1>
            <p class="text-muted small mt-1 mb-0">Available system leave configurations dashboard view.</p>
        </div>
        <div>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary px-4 rounded-3 fw-semibold" style="border-color: #cbd5e1; color: #475569;">Back to Dashboard</a>
        </div>
    </div>

    <div class="feature-block">
        <h5 class="fw-bold text-dark mb-4">System Leave Configurations</h5>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr class="text-secondary small fw-bold">
                        <th style="width: 10%;">ID</th>
                        <th style="width: 20%;">Leave Type</th>
                        <th style="width: 40%;">Description</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 15%; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($final_list as $row) {
                        $is_active = (strtolower($row['status']) === 'active');
                        $badgeClass = $is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle';
                    ?>
                    <tr>
                        <td class="text-secondary fw-semibold">#LV-<?php echo $row['id']; ?></td>
                        <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></td>
                        <td class="text-muted"><?php echo htmlspecialchars($row['desc']); ?></td>
                        <td><span class="badge <?php echo $badgeClass; ?> px-3 py-1.5 rounded-2"><?php echo $row['status']; ?></span></td>
                        <td style="text-align: right;">
                            <?php if ($is_active) { ?>
                                <a href="?action=deactivate&id=<?php echo $row['db_id']; ?>&name=<?php echo urlencode($row['name']); ?>" class="btn btn-sm btn-warning px-2.5 py-1 rounded-2 fw-semibold">Deactivate</a>
                            <?php } else { ?>
                                <a href="?action=activate&id=<?php echo $row['db_id']; ?>&name=<?php echo urlencode($row['name']); ?>" class="btn btn-sm btn-success px-2.5 py-1 rounded-2 fw-semibold">Activate</a>
                            <?php } ?>
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