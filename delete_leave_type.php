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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_leave'])) {
    if (isset($_POST['is_predefined']) && $_POST['is_predefined'] === 'true') {
        $leave_name = mysqli_real_escape_string($conn, $_POST['leave_name']);
        $conn->query("DELETE FROM `leave_types` WHERE `$column_name` = '$leave_name'");
        $message = "<div class='alert alert-success shadow-sm rounded-3'>Core configurations reset successfully!</div>";
    } else {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $delete = $conn->query("DELETE FROM `leave_types` WHERE `id` = '$id'");
        if ($delete) {
            $message = "<div class='alert alert-success shadow-sm rounded-3'>Leave configuration profile removed successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger shadow-sm rounded-3'>Error updating target data profile.</div>";
        }
    }
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
    ['name' => 'Sick Leave', 'desc' => 'Medical and health related leaves'],
    ['name' => 'Paid Leave', 'desc' => 'Standard annual allocated paid leaves'],
    ['name' => 'Unpaid Leave', 'desc' => 'Leaves taken outside paid quota'],
    ['name' => 'Urgent Leave', 'desc' => 'Emergency or short notice leaves']
];

$final_list = [];
$counter = 1;

foreach ($predefined_leaves as $pl) {
    $db_id = null;
    $exists_in_db = false;
    foreach ($db_leaves as $dl) {
        $dl_name = isset($dl[$column_name]) ? $dl[$column_name] : '';
        if (strtolower(trim($dl_name)) === strtolower(trim($pl['name']))) {
            $db_id = $dl['id'];
            $exists_in_db = true;
            break;
        }
    }
    $final_list[] = [
        'id' => $counter++,
        'db_id' => $db_id,
        'name' => $pl['name'],
        'is_predefined' => true,
        'has_custom_db' => $exists_in_db
    ];
}

foreach ($db_leaves as $dl) {
    $name = isset($dl[$column_name]) ? $dl[$column_name] : 'half day';
    
    $is_duplicate = false;
    foreach ($predefined_leaves as $pl) {
        if (strtolower(trim($pl['name'])) === strtolower(trim($name))) {
            $is_duplicate = true;
            break;
        }
    }
    
    if (!$is_duplicate) {
        $final_list[] = [
            'id' => $counter++,
            'db_id' => $dl['id'],
            'name' => $name,
            'is_predefined' => false,
            'has_custom_db' => true
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Leave Profiles - Nexus Prime Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; }
        .feature-block { background: white; border-radius: 16px; padding: 30px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(148, 163, 184, 0.05); }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 850px;">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark m-0">Remove Leave Profiles</h4>
                <div class="text-muted small">Manage and delete configurations from the active matrix.</div>
            </div>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3 px-3">Dashboard</a>
        </div>
        
        <?php echo $message; ?>
        
        <div class="feature-block">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr class="text-secondary small fw-bold">
                            <th style="width: 15%;">INDEX</th>
                            <th style="width: 60%;">LEAVE TYPE</th>
                            <th style="width: 25%; text-end" class="text-end">ACTION MATRIX</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($final_list as $row) { ?>
                        <tr>
                            <td class="fw-semibold text-secondary">#LV-<?php echo $row['id']; ?></td>
                            <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?> 
                                <?php if ($row['is_predefined']) { ?>
                                    <span class="badge bg-light text-secondary border ms-2 fw-normal small">System Core</span>
                                <?php } ?>
                            </td>
                            <td class="text-end">
                                <form action="delete_leave_type.php" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to remove/reset this leave profile?');">
                                    <input type="hidden" name="id" value="<?php echo $row['db_id']; ?>">
                                    <input type="hidden" name="leave_name" value="<?php echo htmlspecialchars($row['name']); ?>">
                                    <input type="hidden" name="is_predefined" value="<?php echo $row['is_predefined'] ? 'true' : 'false'; ?>">
                                    <button type="submit" name="delete_leave" class="btn btn-sm btn-danger px-3 fw-semibold rounded-3">Delete</button>
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