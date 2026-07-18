<?php
session_start();
require_once 'config/db.php';

if (isset($_POST['process_action'])) {
    $leave_id = mysqli_real_escape_string($conn, $_POST['leave_id']);
    $status_decision = mysqli_real_escape_string($conn, $_POST['status_decision']);
    $admin_comment = mysqli_real_escape_string($conn, $_POST['admin_comment']);

    $conn->query("UPDATE `leave_applications` SET `status` = '$status_decision', `admin_comment` = '$admin_comment' WHERE `id` = '$leave_id'");
    header("Location: review_leaves.php");
    exit();
}

$query = "SELECT * FROM `leave_applications` WHERE `status` = 'Pending' ORDER BY `id` DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review System Leaves</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', sans-serif; }
        .review-card { background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; padding: 30px; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="container py-5" style="max-width: 900px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0">Pending Leave Requests Review</h4>
        <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm">Dashboard</a>
    </div>

    <?php 
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) { 
    ?>
    <div class="card review-card shadow-sm">
        <h5 class="fw-bold text-primary mb-1"><?php echo htmlspecialchars($row['employee_name']); ?></h5>
        <p class="text-muted small mb-2">Applied for <strong><?php echo htmlspecialchars($row['leave_type']); ?></strong> | Duration: <?php echo $row['duration']; ?> Days (<?php echo $row['start_date']; ?> to <?php echo $row['end_date']; ?>)</p>
        <div class="bg-light p-3 rounded-3 mb-3 text-dark small">
            <strong>Reason:</strong> <?php echo htmlspecialchars($row['reason']); ?>
        </div>
        
        <form action="review_leaves.php" method="POST">
            <input type="hidden" name="leave_id" value="<?php echo $row['id']; ?>">
            <div class="mb-3">
                <input type="text" name="admin_comment" class="form-control form-control-sm" placeholder="Add administrative notes/remarks here..." required>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="status_decision" value="Approved" class="btn btn-sm btn-success px-3">Approve</button>
                <button type="submit" name="status_decision" value="Rejected" class="btn btn-sm btn-danger px-3">Reject</button>
            </div>
            <input type="hidden" name="process_action" value="1">
        </form>
    </div>
    <?php 
        }
    } else {
        echo "<div class='alert alert-info text-center'>No pending leave applications to review!</div>";
    }
    ?>
</div>
</body>
</html>