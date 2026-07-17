<?php
session_start();
require_once 'config/db.php';

// Verification block - Use hardcoded employee ID 1 if session tracking is empty for now
$emp_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; 

$totalQuery = $conn->query("SELECT COUNT(*) as total FROM `leave_applications` WHERE `employee_id` = '$emp_id'");
$totalLeaves = ($totalQuery) ? $totalQuery->fetch_assoc()['total'] : 0;

$pendingQuery = $conn->query("SELECT COUNT(*) as total FROM `leave_applications` WHERE `employee_id` = '$emp_id' AND `status` = 'Pending'");
$pendingLeaves = ($pendingQuery) ? $pendingQuery->fetch_assoc()['total'] : 0;

$approvedQuery = $conn->query("SELECT COUNT(*) as total FROM `leave_applications` WHERE `employee_id` = '$emp_id' AND `status` = 'Approved'");
$approvedLeaves = ($approvedQuery) ? $approvedQuery->fetch_assoc()['total'] : 0;

$rejectedQuery = $conn->query("SELECT COUNT(*) as total FROM `leave_applications` WHERE `employee_id` = '$emp_id' AND `status` = 'Rejected'");
$rejectedLeaves = ($rejectedQuery) ? $rejectedQuery->fetch_assoc()['total'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Workspace - Nexus Prime Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; min-height: 100vh; font-family: 'Segoe UI', system-ui, sans-serif; }
        .main-workspace { max-width: 1140px; margin: 0 auto; padding: 40px 15px; }
        .header-brand { color: #2b44b8; font-weight: 700; font-size: 2.2rem; }
        .feature-block { background-color: #ffffff; border-radius: 20px; padding: 30px; box-shadow: 0 8px 24px rgba(148, 163, 184, 0.08); border: 1px solid #e2e8f0; height: 100%; }
        .block-title { color: #334155; font-weight: 700; font-size: 1.25rem; margin-bottom: 20px; }
        .counter-badge { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; text-align: center; }
        .counter-label { color: #64748b; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .counter-value { font-size: 1.75rem; font-weight: 700; margin-top: 4px; }
        .txt-primary { color: #2b44b8; } .txt-warning { color: #d97706; } .txt-success { color: #16a34a; } .txt-danger { color: #dc2626; } .txt-info { color: #0284c7; }
        .action-link { width: 100%; padding: 12px; border-radius: 10px; font-weight: 600; font-size: 0.95rem; margin-bottom: 12px; border: 1px solid #e2e8f0; background-color: #f8fafc; color: #475569; display: block; text-decoration: none; padding-left: 18px; }
        .action-link:hover { border-color: #2b44b8; color: #2b44b8; background-color: #ffffff; }
        .action-link-main { background-color: #2b44b8; color: #ffffff; border: none; }
        .action-link-main:hover { background-color: #1e3296; color: #ffffff; }
        .data-grid th { font-weight: 600; color: #64748b; border-bottom: 2px solid #e2e8f0; font-size: 0.9rem; }
        .state-tag { padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.8rem; display: inline-block; }
        .tag-review { background-color: #fef9c3; color: #713f12; }
        .tag-verified { background-color: #dcfce7; color: #14532d; }
        .tag-rejected { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
<div class="main-workspace">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <div class="header-brand mb-1">Nexus Prime Tech</div>
            <div class="text-muted small">Employee Leave Console System portal.</div>
        </div>
        <a href="ems.html" class="btn btn-outline-danger px-4 fw-semibold rounded-3" onclick="return confirm('Are you sure you want to logout from your workspace?');">Logout</a> </div>

    <div class="feature-block mb-4">
        <div class="block-title">Your Leave Track Metrics</div>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="counter-badge">
                    <div class="counter-label">Total Applications</div>
                    <div class="counter-value txt-info"><?php echo $totalLeaves; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="counter-badge">
                    <div class="counter-label">Pending Reviews</div>
                    <div class="counter-value txt-warning"><?php echo $pendingLeaves; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="counter-badge">
                    <div class="counter-label">Approved Grants</div>
                    <div class="counter-value txt-success"><?php echo $approvedLeaves; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="counter-badge">
                    <div class="counter-label">Rejected Requests</div>
                    <div class="counter-value txt-danger"><?php echo $rejectedLeaves; ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="feature-block">
                <div class="block-title">Quick Actions</div>
                <a href="apply_leave.php" class="action-link action-link-main text-center">Apply For Leave</a>
                <a href="employee_leave_history.php" class="action-link text-center">View Leave History Matrix</a>
            </div>
        </div>
        <div class="col-md-8">
            <div class="feature-block">
                <div class="block-title">Recent Leave Status Updates</div>
                <div class="table-responsive">
                    <table class="table data-grid align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Leave Type</th>
                                <th>Duration</th>
                                <th>Timeline</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent = $conn->query("SELECT * FROM `leave_applications` WHERE `employee_id` = '$emp_id' ORDER BY `id` DESC LIMIT 3");
                            if ($recent && $recent->num_rows > 0) {
                                while($row = $recent->fetch_assoc()) {
                                    $tag = ($row['status'] == 'Approved') ? 'tag-verified' : (($row['status'] == 'Rejected') ? 'tag-rejected' : 'tag-review');
                                    echo "<tr>
                                            <td class='fw-semibold text-dark'>{$row['leave_type']}</td>
                                            <td>{$row['duration']} Days</td>
                                            <td class='small text-muted'>{$row['start_date']} to {$row['end_date']}</td>
                                            <td><span class='state-tag {$tag}'>{$row['status']}</span></td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center py-3 text-muted'>No entries recorded yet.</td></tr>";
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