<?php
session_start();
require_once 'config/db.php';

$emp_id = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : 1;
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : "";

if (isset($_POST['delete_leave'])) {
    $leave_id = mysqli_real_escape_string($conn, $_POST['id']);
    $conn->query("DELETE FROM `leave_applications` WHERE `id` = '$leave_id' AND `employee_id` = '$emp_id' AND `status` = 'Pending'");
    header("Location: leave_history.php?success=1");
    exit();
}

$col_duration = 'duration'; 
$col_start = 'start_date'; 
$col_end = 'end_date';

$check_dur = $conn->query("SHOW COLUMNS FROM `leave_applications` WHERE Field IN ('duration', 'num_days', 'days')");
if ($check_dur && $check_dur->num_rows > 0) { $col_duration = $check_dur->fetch_assoc()['Field']; }

$check_start = $conn->query("SHOW COLUMNS FROM `leave_applications` WHERE Field IN ('start_date', 'from_date', 'from')");
if ($check_start && $check_start->num_rows > 0) { $col_start = $check_start->fetch_assoc()['Field']; }

$check_end = $conn->query("SHOW COLUMNS FROM `leave_applications` WHERE Field IN ('end_date', 'to_date', 'to')");
if ($check_end && $check_end->num_rows > 0) { $col_end = $check_end->fetch_assoc()['Field']; }

$query = "SELECT * FROM `leave_applications` WHERE `employee_id` = '$emp_id'";
if ($filter_status != "") { $query .= " AND `status` = '$filter_status'"; }
$query .= " ORDER BY `id` DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Leave Management Profiles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', sans-serif; }
        .main-workspace { max-width: 1000px; margin: 40px auto; padding: 0 15px; }
        .state-tag { padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 0.8rem; }
        .tag-review { background-color: #fef9c3; color: #713f12; }
        .tag-verified { background-color: #dcfce7; color: #14532d; }
        .tag-rejected { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
<div class="main-workspace">
    
    <?php if (isset($_GET['success'])) { ?>
        <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4">Leave application cancelled and removed successfully.</div>
    <?php } ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0">Leave History Logs</h4>
        <a href="employee_dashboard.php" class="btn btn-outline-secondary btn-sm fw-semibold px-3 rounded-3">Workspace Dashboard</a>
    </div>

    <div class="card p-4 mb-4 border-0 shadow-sm rounded-4">
        <form method="GET" action="" class="row g-3 align-items-center">
            <div class="col-md-8">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Show All Applications</option>
                    <option value="Pending" <?php if($filter_status == 'Pending') echo 'selected'; ?>>Pending Reviews</option>
                    <option value="Approved" <?php if($filter_status == 'Approved') echo 'selected'; ?>>Approved Records</option>
                    <option value="Rejected" <?php if($filter_status == 'Rejected') echo 'selected'; ?>>Rejected Profiles</option>
                </select>
            </div>
            <div class="col-md-4 text-muted small">Auto-filtering matching fields.</div>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary text-uppercase small">
                    <tr>
                        <th class="px-4">Type</th>
                        <th>Duration</th>
                        <th>Range</th>
                        <th>Status</th>
                        <th class="text-end px-4">Modifications</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $tag = ($row['status'] == 'Approved') ? 'tag-verified' : (($row['status'] == 'Rejected') ? 'tag-rejected' : 'tag-review');
                            
                            $val_duration = isset($row[$col_duration]) ? $row[$col_duration] : 1;
                            $val_start = isset($row[$col_start]) ? date('M d, Y', strtotime($row[$col_start])) : 'N/A';
                            $val_end = isset($row[$col_end]) ? date('M d, Y', strtotime($row[$col_end])) : 'N/A';
                            
                            echo "<tr>
                                    <td class='px-4 fw-semibold'>" . htmlspecialchars($row['leave_type']) . "</td>
                                    <td class='fw-bold text-primary'>" . $val_duration . " Days</td>
                                    <td class='small text-muted'>" . $val_start . " to " . $val_end . "</td>
                                    <td><span class='state-tag {$tag}'>" . $row['status'] . "</span></td>
                                    <td class='text-end px-4'>";
                            
                            if ($row['status'] === 'Pending') {
                                echo "<form method='POST' action='' style='display:inline-block;' onsubmit='return confirm(\"Confirm request removal?\");'>
                                        <input type='hidden' name='id' value='{$row['id']}'>
                                        <button type='submit' name='delete_leave' class='btn btn-sm btn-outline-danger py-1 px-3 rounded-3'>Cancel</button>
                                      </form>";
                            } else {
                                echo "<span class='badge bg-light text-secondary border py-1.5 px-3 rounded-pill'>Locked</span>";
                            }
                            echo "</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No corresponding application history matches.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>