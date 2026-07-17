<?php
session_start();
require_once 'config/db.php';

$message = "";
$emp_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $leave_type = mysqli_real_escape_string($conn, $_POST['leave_type']);
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    
    $current_date = date('Y-m-d');
    
    // Validations 1 & 2 & 4
    if ($start_date < $current_date) {
        $message = "<div class='alert alert-danger'>Start date cannot be earlier than today!</div>";
    } elseif ($end_date < $start_date) {
        $message = "<div class='alert alert-danger'>End date cannot be earlier than start date!</div>";
    } elseif (strlen($reason) < 10) {
        $message = "<div class='alert alert-danger'>Leave reason must contain at least 10 characters!</div>";
    } else {
        // Date math calculation logic
        $diff = strtotime($end_date) - strtotime($start_date);
        $duration = round($diff / (60 * 60 * 24)) + 1;
        
        // Validation 5: Overlapping Verification check
        $overlap = $conn->query("SELECT id FROM `leave_applications` 
                                 WHERE `employee_id` = '$emp_id' 
                                 AND `status` != 'Rejected'
                                 AND (('$start_date' BETWEEN `start_date` AND `end_date`) 
                                 OR ('$end_date' BETWEEN `start_date` AND `end_date` ))");
                                 
        if ($overlap && $overlap->num_rows > 0) {
            $message = "<div class='alert alert-danger'>Error: You have an overlapping leave request within this date range!</div>";
        } else {
            $empInfo = $conn->query("SELECT `email` FROM `Employe` WHERE `id` = '$emp_id'");
            $empName = ($empInfo && $empInfo->num_rows > 0) ? $empInfo->fetch_assoc()['email'] : "Employee";

            $sql = "INSERT INTO `leave_applications` (`employee_id`, `employee_name`, `leave_type`, `start_date`, `end_date`, `duration`, `reason`, `status`) 
                    VALUES ('$emp_id', '$empName', '$leave_type', '$start_date', '$end_date', '$duration', '$reason', 'Pending')";
            
            if ($conn->query($sql)) {
                $message = "<div class='alert alert-success'>Leave application submitted successfully for $duration days!</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Leave Request</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', sans-serif; }
        .form-card { background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100 py-5">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="form-card p-5 shadow-sm">
                <h3 class="fw-bold text-primary mb-3">Request Time Off</h3>
                <?php echo $message; ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Leave Classification</label>
                        <select class="form-select" name="leave_type" required>
                            <option value="Casual Leave">Casual Leave</option>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Paid Leave">Paid Leave</option>
                            <option value="Unpaid Leave">Unpaid Leave</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Start Date</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">End Date</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-secondary">Reason Details</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Explain the core reason for leave (min 10 characters)..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 mb-2">Submit Application</button>
                    <a href="employee_dashboard.php" class="btn btn-outline-secondary w-100 py-2.5 text-center d-block rounded-3">Dashboard</a>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>