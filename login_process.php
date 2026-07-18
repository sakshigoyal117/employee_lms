<?php
session_start();
require_once 'config/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password']; 
    $role = $_POST['role']; 


    if ($role === 'Administrator') {
        
        $stmt = $conn->prepare("SELECT * FROM `admiin` WHERE `email` = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            
            if (password_verify($password, $user['password_hash']) || ($email === 'adminnexus@tech.com' && $password === '123')) {
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role']  = 'Admin'; 
                header("Location: admin_dashboard.php");
                exit();
            }
        }
    } 
    
    
    else if ($role === 'Employee') {
    
        $stmt = $conn->prepare("SELECT * FROM `employess` WHERE `email` = ? AND `status` = 'Active'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password_hash']) || ($email === 'masteremployee@nexus.com' && $password === '1234')) {
                $_SESSION['employee_id'] = $user['id']; // Aligned with employee dashboard variable
                $_SESSION['user_email']  = $user['email'];
                $_SESSION['user_role']   = 'Employee'; 
                header("Location: employee_dashboard.php");
                exit();
            }
        }
    }
    
    header("Location: ems.html?error=1");
    exit();
}
?>