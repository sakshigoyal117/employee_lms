<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_email']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: ems.html");
    exit();
}

if (isset($_POST['toggle_status'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $current_status = mysqli_real_escape_string($conn, $_POST['current_status']);
    $new_status = ($current_status === 'Active') ? 'Inactive' : 'Active';
    
    $conn->query("UPDATE `employess` SET `status` = '$new_status' WHERE `id` = '$id'");
    header("Location: toggle_employee.php");
    exit();
}

$query = "SELECT `id`, `full_name`, `department`, `status` FROM `employess` ORDER BY `id` DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toggle Employee Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fa; }
        .theme-heading { color: #2b44b8; font-weight: 700; }
        .table-theme-head th { background-color: #2b44b8 !important; color: white !important; }
        .main-card { border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 8px 24px rgba(148, 163, 184, 0.08); }
    </style>
</head>
<body>
    <div class="container py-5" style="max-width: 800px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="theme-heading m-0">Toggle Employee Accounts</h4>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm px-3">Back to Dashboard</a>
        </div>
        <div class="card main-card overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-theme-head text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="px-4 py-3">Full Name</th>
                            <th class="px-4 py-3">Department</th>
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
                            <td class="px-4 py-3 fw-medium"><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td class="px-4 py-3 text-muted"><?php echo htmlspecialchars($row['department']); ?></td>
                            <td class="px-4 py-3">
                                <span class="badge <?php echo ($row['status'] === 'Active') ? 'text-bg-success' : 'text-bg-danger'; ?> rounded-pill">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <form action="toggle_employee.php" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="current_status" value="<?php echo $row['status']; ?>">
                                    <button type="submit" name="toggle_status" class="btn btn-sm <?php echo $btn_theme; ?> fw-medium px-3"><?php echo $btn_label; ?></button>
                                </form>
                            </td>
                        </tr>
                        <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='4' class='text-center text-muted py-3'>No employee records found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

