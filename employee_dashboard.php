<?php
session_start();
require_once 'config/db.php';

$empid = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : 1;

$conn->query("CREATE TABLE IF NOT EXISTS `leave_applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `leave_type` VARCHAR(100) NOT NULL,
    `from_date` DATE NOT NULL,
    `to_date` DATE NOT NULL,
    `duration` INT NOT NULL,
    `reason` TEXT NOT NULL,
    `status` VARCHAR(50) DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$col_duration = 'duration'; $col_start = 'start_date'; $col_end = 'end_date';

$check_dur = $conn->query("SHOW COLUMNS FROM `leave_applications` WHERE Field IN ('duration', 'num_days', 'days')");
if ($check_dur && $check_dur->num_rows > 0) { $col_duration = $check_dur->fetch_assoc()['Field']; }

$check_start = $conn->query("SHOW COLUMNS FROM `leave_applications` WHERE Field IN ('start_date', 'from_date', 'from')");
if ($check_start && $check_start->num_rows > 0) { $col_start = $check_start->fetch_assoc()['Field']; }

$check_end = $conn->query("SHOW COLUMNS FROM `leave_applications` WHERE Field IN ('end_date', 'to_date', 'to')");
if ($check_end && $check_end->num_rows > 0) { $col_end = $check_end->fetch_assoc()['Field']; }

$total_apps = $conn->query("SELECT COUNT(*) as cnt FROM `leave_applications` WHERE `employee_id` = '$empid'")->fetch_assoc()['cnt'];
$pending_reviews = $conn->query("SELECT COUNT(*) as cnt FROM `leave_applications` WHERE `employee_id` = '$empid' AND `status` = 'Pending'")->fetch_assoc()['cnt'];
$approved_grants = $conn->query("SELECT COUNT(*) as cnt FROM `leave_applications` WHERE `employee_id` = '$empid' AND `status` = 'Approved'")->fetch_assoc()['cnt'];
$rejected_requests = $conn->query("SELECT COUNT(*) as cnt FROM `leave_applications` WHERE `employee_id` = '$empid' AND `status` = 'Rejected'")->fetch_assoc()['cnt'];

$recent_leaves = [];
$res_recent = $conn->query("SELECT * FROM `leave_applications` WHERE `employee_id` = '$empid' ORDER BY `id` DESC LIMIT 5");
if ($res_recent && $res_recent->num_rows > 0) {
    while ($r = $res_recent->fetch_assoc()) {
        $recent_leaves[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Workspace - Nexus Prime Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; }
        .feature-block { background: white; border-radius: 16px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(148, 163, 184, 0.03); }
        .metric-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; text-align: center; }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 1100px;">
        
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h2 class="fw-bold text-dark m-0">Employee Workspace</h2>
                <div class="text-muted small">Monitor metrics states and process time off request allocations.</div>
            </div>
            <a href="logout.php" onclick="return confirm('Are you sure you want to logout from employee workspace ?');" class="btn btn-outline-danger fw-bold px-4 rounded-3" style="border-color: #fca5a5; color: #dc2626;">Logout</a>
        </div>
   
        <div class="feature-block mb-4">
            <h5 class="fw-bold text-dark mb-3">Your Leave Track Metrics</h5>
            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <div class="metric-card">
                        <div class="text-secondary small fw-bold">TOTAL APPLICATIONS</div>
                        <h2 class="fw-bold text-primary m-0 mt-1"><?php echo $total_apps; ?></h2>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="metric-card">
                        <div class="text-secondary small fw-bold">PENDING REVIEWS</div>
                        <h2 class="fw-bold text-warning m-0 mt-1"><?php echo $pending_reviews; ?></h2>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="metric-card">
                        <div class="text-secondary small fw-bold">APPROVED GRANTS</div>
                        <h2 class="fw-bold text-success m-0 mt-1"><?php echo $approved_grants; ?></h2>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="metric-card">
                        <div class="text-secondary small fw-bold">REJECTED REQUESTS</div>
                        <h2 class="fw-bold text-danger m-0 mt-1"><?php echo $rejected_requests; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-block h-100">
                    <h5 class="fw-bold text-dark mb-3">Quick Actions</h5>
                    <a href="apply_leave.php" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 mb-2" style="background-color: #2b44b8; border:none;">Apply For Leave</a>
                    <a href="employee_leave_history.php" class="btn btn-outline-secondary w-100 py-2.5 fw-semibold rounded-3" style="border-color:#cbd5e1; color:#475569;">View Leave History Matrix</a>
                </div>
            </div>

            <div class="col-md-8">
                <div class="feature-block h-100">
                    <h5 class="fw-bold text-dark mb-3">Recent Leave Status Updates</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr class="text-secondary small fw-bold">
                                    <th>Leave Type</th>
                                    <th>Duration</th>
                                    <th>Timeline</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (!empty($recent_leaves)) {
                                    foreach ($recent_leaves as $rl) {
                                        $badge = ($rl['status'] === 'Pending') ? 'bg-warning text-dark' : (($rl['status'] === 'Approved') ? 'bg-success text-white' : 'bg-danger text-white');
                                        
                                        $val_duration = isset($rl[$col_duration]) ? $rl[$col_duration] : 1;
                                        $val_start = isset($rl[$col_start]) ? $rl[$col_start] : 'now';
                                        $val_end = isset($rl[$col_end]) ? $rl[$col_end] : 'now';
                                ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($rl['leave_type']); ?></td>
                                    <td class="text-muted fw-semibold small"><?php echo $val_duration; ?> Day(s)</td>
                                    <td class="small text-secondary"><?php echo date('M d, Y', strtotime($val_start)) . ' to ' . date('M d, Y', strtotime($val_end)); ?></td>
                                    <td><span class="badge <?php echo $badge; ?> px-2.5 py-1.5 rounded-2 fw-semibold"><?php echo $rl['status']; ?></span></td>
                                </tr>
                                <?php 
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center text-muted py-4 small fw-medium'>No entries recorded yet.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</body>
</html>