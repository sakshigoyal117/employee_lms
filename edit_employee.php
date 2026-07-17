<?php
session_start();
require_once 'config/db.php';

$selected_id = "";
$emp_data = null;
$message = "";

if (isset($_GET['id'])) {
    $selected_id = mysqli_real_escape_string($conn, $_GET['id']);
    $res = $conn->query("SELECT * FROM `employess` WHERE `id` = '$selected_id'");
    if ($res && $res->num_rows > 0) {
        $emp_data = $res->fetch_assoc();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_employee'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    
    $update_query = "UPDATE `employess` SET `full_name`='$full_name', `email`='$email', `phone`='$phone', `department`='$department' WHERE `id`='$id'";
    
    if ($conn->query($update_query)) {
        header("Location: edit_employee.php?id=" . $id . "&success=1");
        exit();
    } else {
        $message = "<div class='alert alert-danger'>Update Error: " . $conn->error . "</div>";
    }
}

if (isset($_GET['success'])) {
    $message = "<div class='alert alert-success shadow-sm'>Employee details updated successfully!</div>";
}

$all_employees = $conn->query("SELECT `id`, `email` FROM `employess` ORDER BY `id` DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', sans-serif; min-height: 100vh; }
        .edit-container { max-width: 650px; margin: 50px auto; padding: 35px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; box-shadow: 0 8px 24px rgba(148, 163, 184, 0.08); }
        .form-label { font-weight: 600; color: #475569; font-size: 0.9rem; }
        .theme-heading { color: #2b44b8; font-weight: 700; }
        .btn-theme-main { background-color: #2b44b8; color: #ffffff; border: none; }
        .btn-theme-main:hover { background-color: #1e3296; color: #ffffff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="edit-container shadow-sm">
            <h3 class="mb-4 text-center theme-heading">Modify Employee Records</h3>
            
            <?php echo $message; ?>
            
            <form action="edit_employee.php" method="GET" class="mb-4 border-bottom pb-4">
                <label class="form-label">Select Employee Account</label>
                <div class="input-group">
                    <select name="id" class="form-select" required>
                        <option value="" selected disabled>Choose Employee Account...</option>
                        <?php 
                        if ($all_employees && $all_employees->num_rows > 0) {
                            while($row = $all_employees->fetch_assoc()) {
                                $selected = ($selected_id == $row['id']) ? 'selected' : '';
                                echo '<option value="'.$row['id'].'" '.$selected.'>#EMP-'.$row['id'].' ('.$row['email'].')</option>';
                            } 
                        }
                        ?>
                    </select>
                    <button type="submit" class="btn btn-theme-main px-4">Load Data</button>
                </div>
            </form>

            <?php if ($emp_data) { ?>
            <form action="edit_employee.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $emp_data['id']; ?>">
                <input type="hidden" name="update_employee" value="1">
                
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($emp_data['full_name'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($emp_data['email']); ?>" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($emp_data['phone'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($emp_data['department'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-theme-main w-100 py-2.5 fw-bold rounded-3 mt-2">Update Employee Profile</button>
            </form>
            <?php } ?>
            
            <a href="admin_dashboard.php" class="btn btn-outline-secondary w-100 mt-3 py-2 fw-semibold rounded-3 text-decoration-none text-center d-block">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>