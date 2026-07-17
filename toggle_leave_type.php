<?php
session_start();
require_once 'config/db.php';

if (isset($_POST['toggle_status'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $current_status = mysqli_real_escape_string($conn, $_POST['current_status']);
    $new_status = ($current_status === 'Active') ? 'Deactivated' : 'Active';
    
    $conn->query("UPDATE `leave_types` SET `status` = '$new_status' WHERE `id` = '$id'");
    header("Location: toggle_leave_type.php");
    exit();
}

$query = "SELECT * FROM `leave_types` ORDER BY `id` DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toggle Leave Status - Nexus Prime Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container py-5" style="max-width: 800px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-dark m-0">Toggle Leave Availabilities</h4>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm">Dashboard</a>
        </div>
        <div class="card border-light shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="px-4 py-3">Leave Type</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-end">Operation</th>
                        </tr>
                    </thead>
                    <tbody class="fs-6 text-dark">
                        <?php 
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) { 
                                $btn_theme = ($row['status'] === 'Active') ? 'btn-outline-danger' : 'btn-success';
                                $btn_label = ($row['status'] === 'Active') ? 'Deactivate' : 'Activate';
                        ?>
                        <tr>
                            <td class="px-4 py-3 fw-bold text-dark"><?php echo htmlspecialchars($row['type_name']); ?></td>
                            <td class="px-4 py-3">
                                <span class="badge <?php echo ($row['status'] === 'Active') ? 'text-bg-success' : 'text-bg-danger'; ?> rounded-pill">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <form action="toggle_leave_type.php" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="current_status" value="<?php echo $row['status']; ?>">
                                    <button type="submit" name="toggle_status" class="btn btn-sm <?php echo $btn_theme; ?> fw-medium px-3"><?php echo $btn_label; ?></button>
                                </form>
                            </td>
                        </tr>
                        <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='3' class='text-center text-muted py-4'>No leave matrices found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>