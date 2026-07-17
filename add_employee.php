<?php
session_start();
require_once 'config/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_employee'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $joining_date = mysqli_real_escape_string($conn, $_POST['joining_date']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $status = "Active";

    $check_email = $conn->query("SELECT `id` FROM `employess` WHERE `email` = '$email'");
    if ($check_email && $check_email->num_rows > 0) {
        $message = "<div class='alert alert-danger'>Email address already exists!</div>";
    } else {
        $insert_query = "INSERT INTO `employess` (`full_name`, `email`, `phone`, `department`, `joining_date`, `password_hash`, `status`) 
                         VALUES ('$full_name', '$email', '$phone', '$department', '$joining_date', '$password_hash', '$status')";
        
        if ($conn->query($insert_query)) {
            $message = "<div class='alert alert-success'>Employee added successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', sans-serif; min-height: 100vh; }
        .form-container { max-width: 550px; margin: 40px auto; padding: 35px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; box-shadow: 0 8px 24px rgba(148, 163, 184, 0.08); }
        .form-label { font-weight: 600; color: #475569; font-size: 0.9rem; }
        .theme-heading { color: #2b44b8; font-weight: 700; }
        .btn-theme-main { background-color: #2b44b8; color: #ffffff; border: none; }
        .btn-theme-main:hover { background-color: #1e3296; color: #ffffff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h3 class="mb-4 text-center theme-heading">Add New Employee</h3>
            
            <?php echo $message; ?>
            
            <form action="add_employee.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" placeholder="Enter full name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="username@nexusprime.tech" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="Enter phone number" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <select name="department" class="form-select" required>
                            <option value="" selected disabled>Select Department...</option>
                            <option value="IT Department">IT Department</option>
                            <option value="HR Department">HR Department</option>
                            <option value="Operations">Operations</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Joining Date</label>
                        <input type="date" name="joining_date" class="form-control" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Account Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Create password" required>
                </div>
                
                <button type="submit" name="save_employee" class="btn btn-theme-main w-100 py-2.5 fw-bold rounded-3">Save Employee</button>
            </form>
            
            <a href="admin_dashboard.php" class="btn btn-outline-secondary w-100 mt-3 py-2 fw-semibold rounded-3 text-decoration-none text-center d-block">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>