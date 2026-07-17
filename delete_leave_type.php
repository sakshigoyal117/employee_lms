
<?php
session_start();
require_once 'config/db.php';

if (isset($_POST['execute_delete'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $conn->query("DELETE FROM `leave_types` WHERE `id` = '$id'");
    header("Location: delete_leave_type.php");
    exit();
}

$query = "SELECT id, type_name FROM `leave_types` ORDER BY id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Leave Configuration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container py-5" style="max-width: 800px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-dark m-0">Remove Leave Profiles</h4>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm">Dashboard</a>
        </div>
        <div class="card border-light shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="px-4 py-3">Leave Type</th>
                            <th class="px-4 py-3 text-end">Action Matrix</th>
                        </tr>
                    </thead>
                    <tbody class="fs-6 text-dark">
                        <?php 
                        if($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) { 
                        ?>
                        <tr>
                            <td class="px-4 py-3 fw-semibold text-dark"><?php echo htmlspecialchars($row['type_name']); ?></td>
                            <td class="px-4 py-3 text-end">
                                <form action="delete_leave_type.php" method="POST" onsubmit="return confirm('Delete this leave category?');">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="execute_delete" class="btn btn-sm btn-danger px-3">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='2' class='text-center text-muted py-3'>No leave configurations present.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>