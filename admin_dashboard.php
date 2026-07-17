<?php
session_start();
require_once 'config/db.php';

// Safe checking to check session if required later
// if (!isset($_SESSION['user_email']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
//    header("Location: ems.html");
//    exit();
// }

// FIX: Counter fetches data from correct table 'Employe' according to your schema
$empCountQuery = "SELECT COUNT(*) as total FROM `Employe`";
$empCountResult = $conn->query($empCountQuery);
$totalEmployees = ($empCountResult) ? $empCountResult->fetch_assoc()['total'] : 0;

$totalLeaveQuery = "SELECT COUNT(*) as total FROM `leave_applications`";
$totalLeaveResult = $conn->query($totalLeaveQuery);
$totalLeaves = ($totalLeaveResult) ? $totalLeaveResult->fetch_assoc()['total'] : 0;

$pendingLeaveQuery = "SELECT COUNT(*) as total FROM `leave_applications` WHERE `status` = 'Pending'";
$pendingLeaveResult = $conn->query($pendingLeaveQuery);
$pendingLeaves = ($pendingLeaveResult) ? $pendingLeaveResult->fetch_assoc()['total'] : 0;

$approvedLeaveQuery = "SELECT COUNT(*) as total FROM `leave_applications` WHERE `status` = 'Approved'";
$approvedLeaveResult = $conn->query($approvedLeaveQuery);
$approvedLeaves = ($approvedLeaveResult) ? $approvedLeaveResult->fetch_assoc()['total'] : 0;

$rejectedLeaveQuery = "SELECT COUNT(*) as total FROM `leave_applications` WHERE `status` = 'Rejected'";
$rejectedLeaveResult = $conn->query($rejectedLeaveQuery);
$rejectedLeaves = ($rejectedLeaveResult) ? $rejectedLeaveResult->fetch_assoc()['total'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Panel - Nexus Prime Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f5f9;
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .main-workspace {
            max-width: 1140px;
            margin: 0 auto;
            padding: 40px 15px;
        }
        .header-brand {
            color: #2b44b8;
            font-weight: 700;
            font-size: 2.2rem;
        }
        .caption-text {
            color: #64748b;
            font-size: 0.95rem;
        }
        .feature-block {
            background-color: #ffffff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 24px rgba(148, 163, 184, 0.08);
            border: 1px solid #e2e8f0;
            height: 100%;
        }
        .block-title {
            color: #334155;
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 20px;
        }
        .counter-badge {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
        }
        .counter-label {
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .counter-value {
            font-size: 1.75rem;
            font-weight: 700;
            margin-top: 4px;
        }
        .txt-primary { color: #2b44b8; }
        .txt-warning { color: #d97706; }
        .txt-success { color: #16a34a; }
        .txt-danger { color: #dc2626; }
        .txt-info { color: #0284c7; }

        .filter-control {
            width: 100%;
            padding: 12px 16px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 0.95rem;
            color: #1e293b;
            transition: all 0.2s ease-in-out;
        }
        .filter-control:focus {
            background-color: #ffffff;
            border-color: #2b44b8;
            outline: none;
            box-shadow: 0 0 0 3px rgba(43, 68, 184, 0.15);
        }

        .action-link {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 12px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            color: #475569;
            text-align: left;
            padding-left: 18px;
            display: block;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .action-link:hover {
            background-color: #ffffff;
            border-color: #2b44b8;
            color: #2b44b8;
            box-shadow: 0 4px 12px rgba(43, 68, 184, 0.06);
        }
        .action-link-main {
            background-color: #2b44b8;
            color: #ffffff;
            border: none;
        }
        .action-link-main:hover {
            background-color: #1e3296;
            color: #ffffff;
        }

        .data-grid {
            margin: 0;
        }
        .data-grid th {
            font-weight: 600;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.9rem;
            padding: 12px 8px;
        }
        .data-grid td {
            padding: 14px 8px;
            color: #334155;
            font-size: 0.95rem;
        }
        .state-tag {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.8rem;
            display: inline-block;
        }
        .tag-review { background-color: #fef9c3; color: #713f12; }
        .tag-verified { background-color: #dcfce7; color: #14532d; }
        .tag-rejected { background-color: #fee2e2; color: #991b1b; }

        .btn-terminate {
            background-color: transparent;
            color: #dc2626;
            border: 1px solid #dc2626;
            font-weight: 600;
            padding: 8px 22px;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        .btn-terminate:hover {
            background-color: #dc2626;
            color: #ffffff;
        }
    </style>
</head>
<body>

<div class="main-workspace">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <div class="header-brand mb-1">Nexus Prime Tech</div>
            <div class="caption-text">Welcome back, Administrator. Here is your dashboard summary.</div>
        </div>
       <a href="ems.html" class="btn btn-outline-danger px-4 fw-semibold rounded-3" onclick="return confirm('Are you sure you want to logout from your workspace?');">Logout</a>    </div>

    <div class="feature-block mb-4">
        <div class="block-title">Leave & Operational Overview</div>
        <div class="row g-3">
            <div class="col-6 col-md-2" style="width: 20%;">
                <div class="counter-badge">
                    <div class="counter-label">Total Employees</div>
                    <div class="counter-value txt-info"><?php echo $totalEmployees; ?></div>
                </div>
            </div>
            <div class="col-6 col-md-2" style="width: 20%;">
                <div class="counter-badge">
                    <div class="counter-label">Total Requests</div>
                    <div class="counter-value txt-primary"><?php echo $totalLeaves; ?></div>
                </div>
            </div>
            <div class="col-6 col-md-2" style="width: 20%;">
                <div class="counter-badge">
                    <div class="counter-label">Pending</div>
                    <div class="counter-value txt-warning"><?php echo $pendingLeaves; ?></div>
                </div>
            </div>
            <div class="col-6 col-md-2" style="width: 20%;">
                <div class="counter-badge">
                    <div class="counter-label">Approved</div>
                    <div class="counter-value txt-success"><?php echo $approvedLeaves; ?></div>
                </div>
            </div>
            <div class="col-6 col-md-2" style="width: 20%;">
                <div class="counter-badge">
                    <div class="counter-label">Rejected</div>
                    <div class="counter-value txt-danger"><?php echo $rejectedLeaves; ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="feature-block">
                <div class="block-title">Employee Management</div>
                <div class="mb-4">
                    <form action="view_employees.php" method="GET">
                        <input type="text" name="search" class="filter-control" placeholder="Search employee by name or email...">
                    </form>
                </div>
                <a href="add_employee.php" class="action-link action-link-main">Add New Employee</a>
                <a href="view_employees.php" class="action-link">View Employee Details</a>
                <a href="edit_employee.php" class="action-link">Edit Employee Information</a>
                <a href="toggle_employee.php" class="action-link">Activate or Deactivate Employee</a>
                <a href="delete_employee.php" class="action-link">Delete An Employee</a>
            </div>
        </div>

        <div class="col-md-6">
            <div class="feature-block">
                <div class="block-title">Leave System Management</div>
                <div class="action-link-group">
                    <a href="add_leave_type.php" class="action-link action-link-main">Add a New Leave Type</a>
                    <a href="view_leave_types.php" class="action-link">View All Available Leave Types</a>
                    <a href="edit_leave_type.php" class="action-link">Edit a Leave Configuration</a>
                    <a href="toggle_leave_type.php" class="action-link">Activate/Deactivate Leave Type</a>
                    <a href="delete_leave_type.php" class="action-link">Delete a Leave Type</a>
                </div>
            </div>
        </div>
    </div>

    <div class="feature-block">
        <div class="block-title">Recent Leave Applications</div>
        <div class="table-responsive">
            <table class="table data-grid align-middle">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Leave Type</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch recent applications using 'Employe' table relation safely
                    $recentLeavesQuery = "SELECT l.*, e.id as emp_db_id FROM `leave_applications` l 
                                          JOIN `Employe` e ON l.employee_id = e.id 
                                          ORDER BY l.id DESC LIMIT 5";
                    $recentResult = $conn->query($recentLeavesQuery);

                    if ($recentResult && $recentResult->num_rows > 0) {
                        while($row = $recentResult->fetch_assoc()) {
                            $statusTag = 'tag-review';
                            if ($row['status'] == 'Approved') $statusTag = 'tag-verified';
                            if ($row['status'] == 'Rejected') $statusTag = 'tag-rejected';
                            
                            echo "<tr>";
                            echo "<td class='fw-semibold'>#NPT-" . str_pad($row['emp_db_id'], 4, '0', STR_PAD_LEFT) . "</td>";
                            echo "<td>" . htmlspecialchars($row['employee_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['leave_type']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['duration']) . " Days</td>";
                            echo "<td><span class='state-tag {$statusTag}'>" . htmlspecialchars($row['status']) . "</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center text-muted py-3'>No recent leave applications found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>