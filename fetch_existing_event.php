<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'connect.php';

if (!isset($_SESSION['user'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$stmt = $db_staff_pdo->prepare("SELECT `name` FROM `event_info` WHERE `staff_id` = :staff_id");
$stmt->bindParam(':staff_id', $_SESSION['user']);
$stmt->execute();
$existing_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>