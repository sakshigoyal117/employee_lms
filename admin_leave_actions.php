<?php
session_start();
require_once 'config/db.php';

$message = "";

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $app_id = intval($_GET['id']);
    $new_status = ($action === 'approve') ? 'Approved' : (($action === 'reject') ? 'Rejected' : '');

    if (!empty($new_status)) {
        $update_query = "UPDATE `leave_applications` SET `status` = '$new_status' WHERE `id` = $app_id";
        if ($conn->query($update_query)) {
            $message = "<div class='alert alert-success shadow-sm'>Application #$app_id status updated to <strong>$new_status</strong> successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger shadow-sm'>Error updating record: " . $conn->error . "</div>";
        }
    }
}


$col_dur = 'duration';
$check_dur = $conn->query("SHOW COLUMNS FROM `leave_applications` WHERE Field IN ('duration', 'num_days', 'days')");
if ($check_dur && $check_dur->num_rows > 0) {
    $col_dur = $check_dur->fetch_assoc()['Field'];
}

$all_applications = [];
$res = $conn->query("ORDER" !== "" ? "SELECT * FROM `leave_applications` ORDER BY `id` DESC" : "");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $all_applications[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Leave Management Control - Nexus Prime</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; }
        .admin-card { background: white; border-radius: 16px; padding: 30px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(148, 163, 184, 0.03); }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 1200px;">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark m-0">Admin Leave Control Matrix</h2>
                <div class="text-muted small">Review incoming employee time-off requests and manage approvals.</div>
            </div>
            <a href="employee_dashboard.php" class="btn btn-outline-primary fw-semibold rounded-3 px-4">View Employee Dashboard</a>
        </div>

        <?php echo $message; ?>

        <div class="admin-card">
            <h5 class="fw-bold text-dark mb-4">Incoming Requests Pipeline</h5>
            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead>
                        <tr class="text-secondary small fw-bold">
                            <th>ID</th>
                            <th>Emp ID / Name</th>
                            <th>Leave Type</th>
                            <th>Duration</th>
                            <th>Timeline Dates</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th class="text-center">Action Controls</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (!empty($all_applications)) {
                            foreach ($all_applications as $app) {
                                $badge = ($app['status'] === 'Pending') ? 'bg-warning text-dark' : (($app['status'] === 'Approved') ? 'bg-success text-white' : 'bg-danger text-white');
                                
                                $display_from = isset($app['from_date']) ? $app['from_date'] : (isset($app['start_date']) ? $app['start_date'] : (isset($app['from']) ? $app['from'] : 'now'));
                                $display_to = isset($app['to_date']) ? $app['to_date'] : (isset($app['end_date']) ? $app['end_date'] : (isset($app['to']) ? $app['to'] : 'now'));
                                $emp_identifier = isset($app['employee_name']) ? $app['employee_name'] : (isset($app['emp_name']) ? $app['emp_name'] : "Emp #" . $app['employee_id']);
                        ?>
                        <tr>
                            <td class="text-muted fw-bold">#<?php echo $app['id']; ?></td>
                            <td class="fw-semibold text-dark"><?php echo htmlspecialchars($emp_identifier); ?></td>
                            <td class="fw-bold text-primary"><?php echo htmlspecialchars($app['leave_type']); ?></td>
                            <td class="text-secondary small"><?php echo isset($app[$col_dur]) ? $app[$col_dur] : 1; ?> Day(s)</td>
                            <td class="small text-secondary">
                                <?php echo date('M d, Y', strtotime($display_from)) . ' to ' . date('M d, Y', strtotime($display_to)); ?>
                            </td>
                            <td class="small text-muted" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?php echo htmlspecialchars($app['reason']); ?>
                            </td>
                            <td><span class="badge <?php echo $badge; ?> px-2.5 py-1.5 rounded-2 fw-semibold"><?php echo $app['status']; ?></span></td>
                            <td class="text-center">
                                <?php if ($app['status'] === 'Pending') { ?>
                                    <a href="?action=approve&id=<?php echo $app['id']; ?>" class="btn btn-sm btn-success px-3 rounded-2 fw-semibold me-1">Approve</a>
                                    <a href="?action=reject&id=<?php echo $app['id']; ?>" class="btn btn-sm btn-danger px-3 rounded-2 fw-semibold">Reject</a>
                                <?php } else { ?>
                                    <span class="text-muted small fw-medium">Processed</span>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center text-muted py-5 smallfw-medium'>No applications registered in the pipeline yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>