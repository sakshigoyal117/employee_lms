<?php
session_start();
require_once 'config/db.php';

$selected_id = "";
$leave_data = null;

if (isset($_GET['id'])) {
    $selected_id = mysqli_real_escape_string($conn, $_GET['id']);
    $res = $conn->query("SELECT * FROM `leave_types` WHERE `id` = '$selected_id'");
    if ($res && $res->num_rows > 0) {
        $leave_data = $res->fetch_assoc();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_record'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $type_name = mysqli_real_escape_string($conn, $_POST['type_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    if ($conn->query("UPDATE `leave_types` SET `type_name`='$type_name', `description`='$description' WHERE `id`='$id'")) {
        header("Location: edit_leave_type.php?id=" . $id . "&success=1");
        exit();
    }
}

$all_types = $conn->query("SELECT id, type_name FROM `leave_types` ORDER BY type_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Leave Type - Nexus Prime Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fa; }
        .edit-container { max-width: 600px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="edit-container mx-auto p-4 shadow-sm">
            <h4 class="mb-4 fw-bold text-dark text-center">Modify Leave Configurations</h4>
            
            <?php if(isset($_GET['success'])) { echo '<div class="alert alert-success">Leave configuration dynamically updated.</div>'; } ?>
            
            <form action="edit_leave_type.php" method="GET" class="mb-4 border-bottom pb-4">
                <label class="form-label text-dark fw-semibold small">Select Leave Configuration</label>
                <div class="input-group">
                    <select name="id" class="form-select" required>
                        <option value="" selected disabled>Choose Leave Type...</option>
                        <?php 
                        if ($all_types) {
                            while($row = $all_types->fetch_assoc()) {
                                $sel = ($selected_id == $row['id']) ? 'selected' : '';
                                echo '<option value="'.$row['id'].'" '.$sel.'>'.htmlspecialchars($row['type_name']).'</option>';
                            } 
                        }
                        ?>
                    </select>
                    <button type="submit" class="btn btn-dark">Load Config</button>
                </div>
            </form>

            <?php if ($leave_data) { ?>
            <form action="edit_leave_type.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $leave_data['id']; ?>">
                <input type="hidden" name="update_record" value="1">
                <div class="mb-3">
                    <label class="form-label text-dark fw-semibold small">Leave Type Name</label>
                    <input type="text" name="type_name" class="form-control" value="<?php echo htmlspecialchars($leave_data['type_name']); ?>" required>
                </div>
                <div class="mb-4">
                    <label class="form-label text-dark fw-semibold small">Description</label>
                    <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($leave_data['description']); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Update Leave Matrix</button>
            </form>
            <?php } ?>
            
            <a href="admin_dashboard.php" class="btn btn-light w-100 mt-2 border text-secondary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>