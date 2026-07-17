<?php
session_start();
require_once 'config/db.php';

$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $query = "SELECT * FROM `employess` WHERE `full_name` LIKE '%$search%' OR `email` LIKE '%$search%' ORDER BY `id` DESC";
} else {
    $query = "SELECT * FROM `employess` ORDER BY `id` DESC";
}
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Employees</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Segoe UI', Arial, sans-serif;
            padding: 30px;
        }
        .main-container {
            background: #ffffff;
            padding: 30px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 8px 24px rgba(148, 163, 184, 0.08);
        }
        .theme-heading {
            color: #2b44b8;
            font-weight: 700;
        }
        .btn-theme-main {
            background-color: #2b44b8;
            color: #ffffff;
            border: none;
        }
        .btn-theme-main:hover {
            background-color: #1e3296;
            color: #ffffff;
        }
        .table-theme text-white {
            background-color: #2b44b8 !important;
            color: #ffffff !important;
        }
        .data-table th {
            background-color: #2b44b8 !important;
            color: white !important;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .data-table td {
            font-size: 0.95rem;
            color: #334155;
        }
    </style>
</head>
<body>

<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="theme-heading m-0">Employee Details Matrix</h2>
        <a href="admin_dashboard.php" class="btn btn-outline-secondary px-4 fw-semibold">Back to Dashboard</a>
    </div>

    <form action="view_employees.php" method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control py-2" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-theme-main px-4">Search</button>
            <?php if($search != "") { ?>
                <a href="view_employees.php" class="btn btn-outline-danger">Clear</a>
            <?php } ?>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>Phone No</th>
                    <th>Department</th>
                    <th>Joining Date</th>
                    <th>Password Hash</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td class='fw-semibold'>#EMP-" . $row['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['department']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['joining_date']) . "</td>";
                        echo "<td style='max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;' title='".htmlspecialchars($row['password_hash'])."'>" . htmlspecialchars($row['password_hash']) . "</td>";
                        
                        if ($row['status'] == 'Active') {
                            echo "<td><span class='badge bg-success rounded-pill px-3'>Active</span></td>";
                        } else {
                            echo "<td><span class='badge bg-danger rounded-pill px-3'>Inactive</span></td>";
                        }
                        
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center text-muted py-3'>No records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>