<?php
session_start();
require_once 'config/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_employee'])) {
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $joining_date = mysqli_real_escape_string($conn, $_POST['joining_date']);
    $password = $_POST['password']; 
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strpos($email, '@') === false) {
        $message = "<div class='alert alert-danger shadow-sm'>Please enter a valid email address containing '@'!</div>";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $message = "<div class='alert alert-danger shadow-sm'>Phone number must be exactly 10 digits!</div>";
    } elseif (strlen($password) < 4) {
        $message = "<div class='alert alert-danger shadow-sm'>Password must be at least 4 characters long!</div>";
    } else {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $conn->prepare("INSERT INTO `employess` (`full_name`, `email`, `phone`, `department`, `joining_date`, `password_hash`, `status`) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
        $stmt->bind_param("ssssss", $full_name, $email, $phone, $department, $joining_date, $password_hash);
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success shadow-sm'>Employee record created successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger shadow-sm'>Error: Email already exists or system mismatch!</div>";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee Profile - Nexus Prime Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; }
        .form-card { background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 8px 24px rgba(148, 163, 184, 0.06); }
    </style>
</head>
<body class="bg-light py-5">
    <div class="container" style="max-width: 600px;">
        <div class="form-card p-5">
            <h3 class="fw-bold text-primary mb-4">Add Employee</h3>
            <?php echo $message; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Full Name</label>
                    <input type="text" name="full_name" class="form-control" placeholder="Enter full name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="name@company.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Phone</label>
                    <input type="text" name="phone" class="form-control" maxlength="10" placeholder="10-digit number" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Department</label>
                    <select name="department" class="form-select" required>
                        <option value="" selected disabled>Select Department...</option>
                        <option value="IT Department">IT Department</option>
                        <option value="HR Department">HR Department</option>
                        <option value="Operations">Operations</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Joining Date</label>
                    <input type="date" name="joining_date" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary">Account Password</label>
                    <input type="password" name="password" class="form-control" minlength="4" placeholder="Min 4 characters" required>
                </div>
                
                <button type="submit" name="save_employee" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 mb-2">Save Employee</button>
                <a href="admin_dashboard.php" class="btn btn-outline-secondary w-100 py-2.5 text-center d-block rounded-3 fw-semibold">Back to Dashboard</a>
            </form>
        </div>
    </div>
</body>
</html>