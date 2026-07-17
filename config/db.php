<?php
$host = "localhost";
$user = "root";
$password = "2414";
$dbname = "leave_management_db";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>