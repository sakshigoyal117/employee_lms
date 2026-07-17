<?php
session_start();
require_once 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; 
    $role = mysqli_real_escape_string($conn, $_POST['role']); 
    
    $password_hash = hash('sha256', $password);

    if ($role === 'Administrator') {
        $query = "SELECT * FROM `adminn` WHERE `Email` = '$email'";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if ($password_hash === $user['password_hash']) {
                $_SESSION['user_email'] = $user['Email'];
                $_SESSION['user_role']  = 'Admin'; 
                
                header("Location: admin_dashboard.php");
                exit();
            } else {
                header("Location: ems.html?error=1");
                exit();
            }
        } else {
            header("Location: ems.html?error=1");
            exit();
        }
    } else {
        $query = "SELECT * FROM `Employe` WHERE `email` = '$email'";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if ($password_hash === $user['password_hash']) {
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role']  = 'Employee'; 
                
                header("Location: employee_dashboard.php");
                exit();
            } else {
                header("Location: ems.html?error=1");
                exit();
            }
        } else {
            header("Location: ems.html?error=1");
            exit();
        }
    }
} else {
    header("Location: ems.html");
    exit();
}
?>