<?php
session_start();
require_once 'config/db.php';

if (isset($_POST['delete_emp'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $conn->query("DELETE FROM `employess` WHERE `id` = '$id'");
    header("Location: delete_employee.php");
    exit();
}

$query = "SELECT `id`, `email` FROM `employess` ORDER BY `id` DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Employee Record</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <h4 class="theme-heading m-0">Remove Employee Record</h4>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm px-3">Dashboard</a>
        </div>
        
        <div class="card main-card overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-theme-head text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Email Address</th>
                            <th class="px-4 py-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="fs-6 text-dark">
                        <?php 
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) { 
                        ?>
                        <tr>
                            <td class="px-4 py-3 fw-semibold">#EMP-<?php echo $row['id']; ?></td>
                            <td class="px-4 py-3 text-muted"><?php echo htmlspecialchars($row['email']); ?></td>
                            <td class="px-4 py-3 text-end">
                                <form action="delete_employee.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_emp" class="btn btn-sm btn-danger px-3">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='3' class='text-center text-muted py-3'>No employee records found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>