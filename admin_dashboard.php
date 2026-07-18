<?php
session_start();
require_once 'config/db.php';

$total_requests = 0; 
$pending = 0; 
$approved = 0; 
$rejected = 0; 
$total_emps = 0;

$res_emp = $conn->query("SHOW TABLES LIKE 'employess'");
if ($res_emp && $res_emp->num_rows > 0) {
    $total_emps = $conn->query("SELECT COUNT(*) as cnt FROM `employess`")->fetch_assoc()['cnt'];
}

$check_table = $conn->query("SHOW TABLES LIKE 'leave_applications'");
if ($check_table && $check_table->num_rows > 0) {
    $total_requests = $conn->query("SELECT COUNT(*) as cnt FROM `leave_applications`")->fetch_assoc()['cnt'];
    $pending = $conn->query("SELECT COUNT(*) as cnt FROM `leave_applications` WHERE `status` = 'Pending'")->fetch_assoc()['cnt'];
    $approved = $conn->query("SELECT COUNT(*) as cnt FROM `leave_applications` WHERE `status` = 'Approved'")->fetch_assoc()['cnt'];
    $rejected = $conn->query("SELECT COUNT(*) as cnt FROM `leave_applications` WHERE `status` = 'Rejected'")->fetch_assoc()['cnt'];
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $app_id = intval($_GET['id']);
    $status_update = ($action === 'approve') ? 'Approved' : (($action === 'reject') ? 'Rejected' : '');
    
    if (!empty($status_update)) {
        $conn->query("UPDATE `leave_applications` SET `status` = '$status_update' WHERE `id` = $app_id");
        header("Location: admin_dashboard.php");
        exit();
    }
}

$col_dur = 'duration';
$check_dur = $conn->query("SHOW COLUMNS FROM `leave_applications` WHERE Field IN ('duration', 'num_days', 'days')");
if ($check_dur && $check_dur->num_rows > 0) {
    $col_dur = $check_dur->fetch_assoc()['Field'];
}

$requests_list = [];
if ($check_table && $check_table->num_rows > 0) {
    $res_list = $conn->query("SELECT * FROM `leave_applications` ORDER BY `id` DESC");
    if ($res_list && $res_list->num_rows > 0) {
        while ($r = $res_list->fetch_assoc()) {
            $requests_list[] = $r;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Panel - Nexus Prime Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; }
        .feature-block { background: white; border-radius: 16px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(148, 163, 184, 0.03); }
        .metric-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; text-align: center; }
        .btn-menu-primary { display: block; width: 100%; padding: 12px; font-weight: bold; border-radius: 8px; text-align: center; border: none; text-decoration: none; margin-bottom: 12px; color: white; }
        .btn-menu-secondary { display: block; width: 100%; padding: 12px; font-weight: 500; border-radius: 8px; text-align: left; background-color: #f8fafc; border: 1px solid #e2e8f0; color: #475569; text-decoration: none; margin-bottom: 8px; transition: background 0.2s; }
        .btn-menu-secondary:hover { background-color: #f1f5f9; color: #1e293b; }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 1200px;">
        
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="fw-bold text-dark m-0" style="color: #1e3a8a;">Nexus Prime Tech</h1>
                <div class="text-muted small">Welcome back, Administrator. Here is your dashboard summary.</div>
            </div>
            <a href="logout.php" onclick="return confirm('Are you sure you want to logout from the admin dashboard ?');" class="btn btn-outline-danger fw-bold px-4 rounded-3" style="border-color: #fca5a5; color: #dc2626;">Logout</a>      </div>
    
        <div class="feature-block mb-4">
            <h5 class="fw-bold text-dark mb-3">Leave & Operational Overview</h5>
            <div class="row g-3">
                <div class="col">
                    <div class="metric-card">
                        <div class="text-secondary small fw-bold">TOTAL EMPLOYEES</div>
                        <h2 class="fw-bold text-primary m-0 mt-1"><?php echo $total_emps; ?></h2>
                    </div>
                </div>
                <div class="col">
                    <div class="metric-card">
                        <div class="text-secondary small fw-bold">TOTAL REQUESTS</div>
                        <h2 class="fw-bold text-primary m-0 mt-1"><?php echo $total_requests; ?></h2>
                    </div>
                </div>
                <div class="col">
                    <div class="metric-card">
                        <div class="text-secondary small fw-bold">PENDING</div>
                        <h2 class="fw-bold text-warning m-0 mt-1"><?php echo $pending; ?></h2>
                    </div>
                </div>
                <div class="col">
                    <div class="metric-card">
                        <div class="text-secondary small fw-bold">APPROVED</div>
                        <h2 class="fw-bold text-success m-0 mt-1"><?php echo $approved; ?></h2>
                    </div>
                </div>
                <div class="col">
                    <div class="metric-card">
                        <div class="text-secondary small fw-bold">REJECTED</div>
                        <h2 class="fw-bold text-danger m-0 mt-1"><?php echo $rejected; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="feature-block h-100">
                    <h5 class="fw-bold text-dark mb-3">Employee Management</h5>
                    <div class="mb-3">
                        <input type="text" class="form-control" placeholder="Search employee by name or email...">
                    </div>
                    <a href="add_employee.php" class="btn-menu-primary" style="background-color: #2b44b8;">Add New Employee</a>
                    <a href="view_employees.php" class="btn btn-menu-secondary">View Employee Details</a>
                    <a href="edit_employee.php" class="btn btn-menu-secondary">Edit Employee Information</a>
                    <a href="status_employee.php" class="btn btn-menu-secondary">Activate or Deactivate Employee</a>
                    <a href="delete_employee.php" class="btn btn-menu-secondary">Delete An Employee</a>
                </div>
            </div>

            <div class="col-md-6">
                <div class="feature-block h-100">
                    <h5 class="fw-bold text-dark mb-3">Leave System Management</h5>
                    <a href="add_leave_type.php" class="btn-menu-primary" style="background-color: #2b44b8;">Add a New Leave Type</a>
                    <a href="view_leave_types.php" class="btn btn-menu-secondary">View All Available Leave Types</a>
                    <a href="edit_leave_type.php" class="btn btn-menu-secondary">Edit a Leave Configuration</a>
                    <a href="status_leave_type.php" class="btn btn-menu-secondary">Activate/Deactivate Leave Type</a>
                    <a href="delete_leave_type.php" class="btn btn-menu-secondary">Delete a Leave Type</a>
                </div>
            </div>
        </div>

        <div class="feature-block">
            <h5 class="fw-bold text-dark mb-3">Incoming Leave Requests Pipeline</h5>
            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead>
                        <tr class="text-secondary small fw-bold">
                            <th>Leave Type</th>
                            <th>Duration</th>
                            <th>Timeline Dates</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (!empty($requests_list)) {
                            foreach ($requests_list as $rl) {
                                $badge = ($rl['status'] === 'Pending') ? 'bg-warning text-dark' : (($rl['status'] === 'Approved') ? 'bg-success text-white' : 'bg-danger text-white');
                                
                                $display_from = isset($rl['from_date']) ? $rl['from_date'] : (isset($rl['start_date']) ? $rl['start_date'] : (isset($rl['from']) ? $rl['from'] : 'now'));
                                $display_to = isset($rl['to_date']) ? $rl['to_date'] : (isset($rl['end_date']) ? $rl['end_date'] : (isset($rl['to']) ? $rl['to'] : 'now'));
                        ?>
                        <tr>
                            <td class="fw-bold text-dark"><?php echo htmlspecialchars($rl['leave_type']); ?></td>
                            <td class="text-muted fw-semibold small"><?php echo isset($rl[$col_dur]) ? $rl[$col_dur] : 1; ?> Day(s)</td>
                            <td class="small text-secondary"><?php echo date('M d, Y', strtotime($display_from)) . ' to ' . date('M d, Y', strtotime($display_to)); ?></td>
                            <td><span class="badge <?php echo $badge; ?> px-2.5 py-1.5 rounded-2 fw-semibold"><?php echo $rl['status']; ?></span></td>
                            <td class="text-end">
                                <?php if ($rl['status'] === 'Pending') { ?>
                                    <a href="?action=approve&id=<?php echo $rl['id']; ?>" class="btn btn-sm btn-success px-2.5 py-1 rounded-2 fw-bold me-1">✓ Approve</a>
                                    <a href="?action=reject&id=<?php echo $rl['id']; ?>" class="btn btn-sm btn-danger px-2.5 py-1 rounded-2 fw-bold">✕ Reject</a>
                                <?php } else { ?>
                                    <span class="text-muted small fw-medium">Processed</span>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center text-muted py-4 small fw-medium'>No active leave entries found in the database table pipeline.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>