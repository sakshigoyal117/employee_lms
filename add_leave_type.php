<?php
session_start();
require_once 'config/db.php';

$message = "";

// Auto-create leave_types table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS `leave_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type_name` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT,
    `status` ENUM('Active', 'Deactivated') DEFAULT 'Active'
)");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type_name = mysqli_real_escape_string($conn, $_POST['type_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $check = $conn->query("SELECT id FROM `leave_types` WHERE `type_name` = '$type_name'");
    if ($check && $check->num_rows > 0) {
        $message = "<div class='alert alert-danger shadow-sm'>Error: Leave type already exists!</div>";
    } else {
        $sql = "INSERT INTO `leave_types` (`type_name`, `description`, `status`) VALUES ('$type_name', '$description', 'Active')";
        if ($conn->query($sql)) {
            $message = "<div class='alert alert-success shadow-sm'>Leave Type added successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger shadow-sm'>Database Error: " . $conn->error . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Leave Type - Nexus Prime Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', sans-serif; }
        .form-card { background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 8px 24px rgba(148, 163, 184, 0.08); }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100 py-5">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="form-card p-5">
                <h3 class="fw-bold text-primary mb-3">Add New Leave Type</h3>
                <?php echo $message; ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Leave Type Name</label>
                        <input type="text" name="type_name" class="form-control" placeholder="e.g., Sick Leave, Casual Leave" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-secondary">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Describe the leave scope..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 mb-2">Save Leave Type</button>
                    <a href="admin_dashboard.php" class="btn btn-outline-secondary w-100 py-2.5 fw-semibold rounded-3 text-decoration-none text-center d-block">Back to Dashboard</a>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>