<?php
session_start();
include 'connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['event_name'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$event_name = $_SESSION['event_name'];

// 2. Use a placeholder (:username) instead of direct variable insertion
$query = "SELECT `Time`, `total_participants`
            FROM `event_records`
            WHERE `event_name` = :event_name
            ORDER BY `id` DESC
            LIMIT 1";

try {
    $stmt = $db_pdo->prepare($query);
    $stmt->bindParam(':event_name', $event_name, PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

$total_participants = 0;
if (!empty($data) && isset($data[0]['total_participants'])) {
    $total_participants = (int) $data[0]['total_participants'];
}

// Return the session data as a JSON object
echo json_encode([
    'total_participants' => $total_participants
]);
?>