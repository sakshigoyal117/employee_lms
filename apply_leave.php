<?php
session_start();
require_once 'config/db.php';

$empid = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : 1; 
$message = "";

$column_name = 'leave_name'; 
$res = $conn->query("SHOW COLUMNS FROM `leave_types` WHERE Field IN ('leave_name', 'name', 'leave_type', 'type')");
if ($res && $res->num_rows > 0) {
    $detected = $res->fetch_assoc();
    $column_name = $detected['Field'];
}

$core_leaves = [
    ['name' => 'Sick Leave', 'max' => 15],
    ['name' => 'Paid Leave', 'max' => 20],
    ['name' => 'Unpaid Leave', 'max' => 30],
    ['name' => 'Urgent Leave', 'max' => 7]
];

$db_leaves = [];
$query = "SELECT * FROM `leave_types` ORDER BY `id` ASC";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $db_leaves[] = $row;
    }
}

$final_dropdown_list = [];
foreach ($core_leaves as $cl) {
    $status = 'Active';
    $check = $conn->query("SELECT `status`, `max_days` FROM `leave_types` WHERE `$column_name` = '{$cl['name']}'");
    if ($check && $check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $status = $row['status'];
        $cl['max'] = intval($row['max_days']);
    }
    if (strtolower($status) === 'active') {
        $final_dropdown_list[$cl['name']] = ['name' => $cl['name'], 'max' => $cl['max']];
    }
}

foreach ($db_leaves as $dl) {
    $name = isset($dl[$column_name]) ? $dl[$column_name] : '';
    $max = isset($dl['max_days']) ? intval($dl['max_days']) : 60; 
    $status = isset($dl['status']) ? $dl['status'] : 'Active';
    
    if (!empty($name) && strtolower($status) === 'active') {
        $final_dropdown_list[$name] = ['name' => $name, 'max' => $max];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_leave'])) {
    $leave_type = mysqli_real_escape_string($conn, $_POST['leave_type']);
    $reason = mysqli_real_escape_string($conn, trim($_POST['reason']));
    $from_date = mysqli_real_escape_string($conn, $_POST['from_date']);
    $to_date = mysqli_real_escape_string($conn, $_POST['to_date']);
    
    $today_ts = strtotime(date('Y-m-d'));
    $fd = strtotime($from_date);
    $td = strtotime($to_date);
    $days = (($td - $fd) / (60 * 60 * 24)) + 1;

    $limit_check = 60;
    foreach ($final_dropdown_list as $opt) {
        if ($opt['name'] === $_POST['leave_type']) {
            $limit_check = $opt['max'];
            break;
        }
    }
    
    $col_from = 'from_date';
    $col_to = 'to_date';
    $col_dur = 'duration';
    $extra_cols_str = "";
    $extra_vals_str = "";
    $emp_real_name = "System Employee";
    
    $check_cols = $conn->query("SHOW COLUMNS FROM `leave_applications`");
    if ($check_cols && $check_cols->num_rows > 0) {
        $existing_fields = [];
        while ($c = $check_cols->fetch_assoc()) {
            $raw_field = $c['Field'];
            $field_name = strtolower($raw_field);
            $existing_fields[] = $field_name;
            
            if ($field_name === 'employee_name' || $field_name === 'emp_name') {
                $extra_cols_str .= ", `$raw_field`";
                $extra_vals_str .= ", '$emp_real_name'";
            }
        }
        if (in_array('start_date', $existing_fields)) $col_from = 'start_date';
        elseif (in_array('from', $existing_fields)) $col_from = 'from';
        
        if (in_array('end_date', $existing_fields)) $col_to = 'end_date';
        elseif (in_array('to', $existing_fields)) $col_to = 'to';
        
        if (in_array('num_days', $existing_fields)) $col_dur = 'num_days';
        elseif (in_array('days', $existing_fields)) $col_dur = 'days';
    }

    $res_emp = $conn->query("SELECT `full_name` FROM `employess` WHERE `id` = '$empid'");
    if ($res_emp && $res_emp->num_rows > 0) {
        $emp_real_name = mysqli_real_escape_string($conn, $res_emp->fetch_assoc()['full_name']);
    }

    $overlap_query = "SELECT id FROM `leave_applications` WHERE `employee_id` = '$empid' AND `status` != 'Rejected' AND (
        (`$col_from` <= '$from_date' AND `$col_to` >= '$from_date') OR 
        (`$col_from` <= '$to_date' AND `$col_to` >= '$to_date') OR 
        ('$from_date' <= `$col_from` AND '$to_date' >= `$col_to`)
    )";
    $overlap_check = $conn->query($overlap_query);

    $gap_query = "SELECT id FROM `leave_applications` WHERE `employee_id` = '$empid' AND `status` != 'Rejected' AND (
        (ABS(DATEDIFF(`$col_from`, '$to_date')) < 10) OR 
        (ABS(DATEDIFF('$from_date', `$col_to`)) < 10)
    )";
    $gap_check = $conn->query($gap_query);

    if ($fd < $today_ts) {
        $message = "<div class='alert alert-danger shadow-sm'>Error: You cannot apply for a leave on a past date!</div>";
    } else if ($td < $fd) {
        $message = "<div class='alert alert-danger shadow-sm'>Error: Timeline structural error! End Date precedes Start Date.</div>";
    } else if ($overlap_check && $overlap_check->num_rows > 0) {
        $message = "<div class='alert alert-danger shadow-sm'>Error: Overlapping leave application detected for these target dates!</div>";
    } else if ($gap_check && $gap_check->num_rows > 0) {
        $message = "<div class='alert alert-danger shadow-sm'>Error: A mandatory 10-day functional operational gap is required between leaves!</div>";
    } else if ($days > $limit_check) {
        $message = "<div class='alert alert-danger shadow-sm'>Error: You cannot apply for more than {$limit_check} days for " . htmlspecialchars($leave_type) . "!</div>";
    } else if (strlen($reason) < 10) {
        $message = "<div class='alert alert-danger shadow-sm'>Error: Reason must be at least 10 characters long.</div>";
    } else {
        $insert_query = "INSERT INTO `leave_applications` (`employee_id`, `leave_type`, `$col_from`, `$col_to`, `$col_dur`, `reason`, `status` $extra_cols_str) 
                         VALUES ('$empid', '$leave_type', '$from_date', '$to_date', '$days', '$reason', 'Pending' $extra_vals_str)";
        
        if ($conn->query($insert_query)) {
            $message = "<div class='alert alert-success shadow-sm'>Leave request submitted successfully for {$days} day(s)!</div>";
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
    <title>Submit Leave Request</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; }
        .form-card { background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 650px;">
        <div class="form-card p-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-primary m-0" style="color: #2b44b8 !important;">Request Time Off</h3>
                <a href="employee_dashboard.php" class="btn btn-sm btn-outline-secondary rounded-3 fw-semibold px-3">Dashboard</a>
            </div>
            
            <?php echo $message; ?>
            
            <form method="POST" action="" onsubmit="return validateLeaveForm();">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary">From Date</label>
                        <input type="date" id="from_date" name="from_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>" onchange="calculateDuration();">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary">To Date</label>
                        <input type="date" id="to_date" name="to_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>" onchange="calculateDuration();">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Leave Classification</label>
                    <select id="leave_type" name="leave_type" class="form-select" required onchange="calculateDuration();">
                        <option value="" selected disabled>Choose category...</option>
                        <?php 
                        foreach ($final_dropdown_list as $opt) {
                            echo "<option value='".htmlspecialchars($opt['name'])."' data-max='{$opt['max']}'>".htmlspecialchars($opt['name'])." (Max: {$opt['max']} Days)</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="mb-3 d-none" id="duration_badge_box">
                    <span class="badge bg-secondary p-2 small fw-semibold">Calculated Duration: <span id="calculated_days">1</span> Day(s)</span>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary">Core Reason for Leave</label>
                    <textarea name="reason" class="form-control" rows="4" placeholder="Explain the core reason for leave (min 10 characters)..." required></textarea>
                </div>
                
                <button type="submit" name="submit_leave" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3" style="background-color: #2b44b8; border: none;">Submit Leave Request</button>
            </form>
        </div>
    </div>

    <script>
    function calculateDuration() {
        const fromDate = document.getElementById('from_date').value;
        const toDate = document.getElementById('to_date').value;
        const badgeBox = document.getElementById('duration_badge_box');
        const daysSpan = document.getElementById('calculated_days');

        if(fromDate && toDate) {
            const start = new Date(fromDate);
            const end = new Date(toDate);
            
            if(end >= start) {
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                daysSpan.innerText = diffDays;
                badgeBox.classList.remove('d-none');
                return diffDays;
            }
        }
        badgeBox.classList.add('d-none');
        return 0;
    }

    function validateLeaveForm() {
        const selectElement = document.getElementById('leave_type');
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        
        if(!selectedOption || selectElement.value === "") {
            alert("Please choose a valid leave category profile.");
            return false;
        }
        return true;
    }
    </script>
</body>
</html>