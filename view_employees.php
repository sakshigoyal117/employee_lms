<?php
session_start();
require_once 'config/db.php';

$query = "SELECT `id`, `full_name`, `email`, `phone`, `department`, `joining_date`, `status` FROM `employess` ORDER BY `id` DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Employee Details - Nexus Prime Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; }
        .main-workspace { max-width: 1200px; margin: 0 auto; padding: 40px 15px; }
        .feature-block { background-color: #ffffff; border-radius: 20px; padding: 30px; border: 1px solid #e2e8f0; box-shadow: 0 8px 24px rgba(148, 163, 184, 0.05); }
        .data-grid th { font-weight: 600; color: #64748b; }
    </style>
</head>
<body>
<div class="main-workspace">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0">Employee Database Directory</h3>
            <div class="text-muted small">Overview of all active and registered corporate profiles.</div>
        </div>
        <a href="admin_dashboard.php" class="btn btn-outline-secondary px-4 fw-semibold rounded-3">Back to Dashboard</a>
    </div>

    <div class="feature-block">
        <div class="table-responsive">
            <table class="table data-grid align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email Address</th>
                        <th>Phone Number</th>
                        <th>Department</th>
                        <th>Joining Date</th>
                        <th>Account State</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $badge = ($row['status'] === 'Active') ? 'bg-success' : 'bg-danger';
                            echo "<tr>";
                            echo "<td class='fw-semibold text-secondary'>#EMP-{$row['id']}</td>";
                            echo "<td class='fw-bold text-dark'>".htmlspecialchars($row['full_name'])."</td>";
                            echo "<td>".htmlspecialchars($row['email'])."</td>";
                            echo "<td class='text-muted'>".htmlspecialchars($row['phone'])."</td>";
                            echo "<td><span class='badge bg-light text-dark border'>".htmlspecialchars($row['department'])."</span></td>";
                            echo "<td class='text-muted'>".htmlspecialchars($row['joining_date'])."</td>";
                            echo "<td><span class='badge {$badge}'>{$row['status']}</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center text-muted py-4'>No employee records available in directory.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>