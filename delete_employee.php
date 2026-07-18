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
</head>
<body class="bg-light py-5">
    <div class="container" style="max-width: 700px;">
        <div class="card p-4 shadow-sm border-0 rounded-3">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold text-dark m-0">Remove Employee Record</h4>
                <a href="admin_dashboard.php" class="btn btn-sm btn-outline-secondary">Dashboard</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) { 
                        ?>
                        <tr>
                            <td>#EMP-<?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td class="text-end">
                                <form action="delete_employee.php" method="POST" onsubmit="return confirm('Are you sure?');">
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