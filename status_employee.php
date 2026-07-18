<?php
session_start();
require_once 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_status'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $current_status = mysqli_real_escape_string($conn, $_POST['status']);
    $new_status = ($current_status === 'Active') ? 'Deactivated' : 'Active';
    
    $conn->query("UPDATE `employess` SET `status` = '$new_status' WHERE `id` = '$id'");
    header("Location: status_employee.php?success=1");
    exit();
}

$query = "SELECT `id`, `full_name`, `email`, `status` FROM `employess` ORDER BY `id` DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Status Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; }
        .feature-block { background: white; border-radius: 16px; padding: 30px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(148, 163, 184, 0.05); }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 800px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-dark m-0">Activate or Deactivate Employee</h4>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm fw-semibold rounded-3 px-3">Dashboard</a>
        </div>
        
        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success shadow-sm rounded-3 mb-4">Employee status changed successfully!</div>
        <?php } ?>
        
        <div class="feature-block">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) { 
                                $btnClass = ($row['status'] === 'Active') ? 'btn-warning' : 'btn-success';
                                $btnText = ($row['status'] === 'Active') ? 'Deactivate' : 'Activate';
                                $badgeClass = ($row['status'] === 'Active') ? 'bg-success' : 'bg-danger';
                        ?>
                        <tr>
                            <td class="fw-semibold text-dark"><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td class="text-muted small"><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $row['status']; ?></span></td>
                            <td class="text-end">
                                <form action="status_employee.php" method="POST" class="m-0">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="status" value="<?php echo $row['status']; ?>">
                                    <button type="submit" name="toggle_status" class="btn btn-sm <?php echo $btnClass; ?> px-3 fw-semibold rounded-3"><?php echo $btnText; ?></button>
                                </form>
                            </td>
                        </tr>
                        <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='4' class='text-center text-muted py-4'>No employee profiles registered yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>