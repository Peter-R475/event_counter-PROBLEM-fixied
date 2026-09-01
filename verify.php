<?php
session_start();
include 'connect.php';
 
// Restrict processing to POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}
 
if (!empty($_POST['username']) && !empty($_POST['password'])) {
    $username = $_POST['username'];
    // Hash incoming password to match database format
    $hashed_password = md5($_POST['password']);
 
    // Query both username and MD5 hashed password using bound parameters
    $stmt = $db_staff_pdo->prepare('SELECT `staff_id` FROM `staff_info` WHERE `staff_id` = :user AND `staff_pass` = :password');
    $stmt->bindParam(':user', $username);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
 
    if ($result) {
        // Initialize session variable to authenticate user across pages
        $_SESSION['user'] = $result['staff_id'];
        header('Location: event_form.php');
        exit();
    } else {
        echo "<script>alert('Invalid username or password'); window.location.href='login.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Username or password not set'); window.location.href='login.php';</script>";
    exit();
}
?>