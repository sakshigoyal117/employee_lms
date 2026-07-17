<?php
session_start();
require_once 'config/db.php';

$conn->query("CREATE TABLE IF NOT EXISTS `leave_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type_name` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT,
    `status` ENUM('Active', 'Deactivated') DEFAULT 'Active'
)");

$query = "SELECT * FROM `leave_types` ORDER BY `id` DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Leave Types - Nexus Prime Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; min-height: 100vh; font-family: 'Segoe UI', system-ui, sans-serif; }
        .main-workspace { max-width: 1140px; margin: 0 auto; padding: 40px 15px; }
        .header-brand { color: #2b44b8; font-weight: 700; font-size: 2.2rem; }
        .feature-block { background-color: #ffffff; border-radius: 20px; padding: 30px; box-shadow: 0 8px 24px rgba(148, 163, 184, 0.08); border: 1px solid #e2e8f0; }
        .data-grid th { font-weight: 600; color: #64748b; border-bottom: 2px solid #e2e8f0; }
        .state-tag { padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.8rem; }
        .tag-verified { background-color: #dcfce7; color: #14532d; }
        .tag-rejected { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
<div class="main-workspace">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <div class="header-brand mb-1">Nexus Prime Tech</div>
            <div class="text-muted small">Available system leave configurations dashboard view.</div>
        </div>
        <a href="admin_dashboard.php" class="btn btn-outline-secondary px-4 fw-semibold rounded-3">Back to Dashboard</a>
    </div>

    <div class="feature-block">
        <div class="h5 fw-bold text-dark mb-4">System Leave Configurations</div>
        <div class="table-responsive">
            <table class="table data-grid align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Leave Type</th>
                        <th>Description</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $statusTag = ($row['status'] === 'Active') ? 'tag-verified' : 'tag-rejected';
                            echo "<tr>";
                            echo "<td class='fw-semibold'>#LV-{$row['id']}</td>";
                            echo "<td class='fw-bold text-dark'>".htmlspecialchars($row['type_name'])."</td>";
                            echo "<td class='text-muted'>".htmlspecialchars($row['description'])."</td>";
                            echo "<td><span class='state-tag {$statusTag}'>{$row['status']}</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center text-muted py-4'>No leave types found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>