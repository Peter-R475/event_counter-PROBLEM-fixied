<?php
session_start();
include 'connect.php';

header('Content-Type: application/json');

$db_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

$timeInput = $data['Time'] ?? $data['Tme'] ?? null;
$eventName = $_SESSION['event_name'] ?? null;
$current = $data['current_participants'] ?? null;
$total = $data['totalParticipants'] ?? null;

// Validate that required values are strictly present (allows 0)
if ($current === null || $timeInput === null || $total === null) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing data payload']);
    exit;
}

if (!$eventName) {
    echo json_encode(['status' => 'error', 'message' => 'Session event_name is not set']);
    exit;
}

$current_participants = (int) $current;

try {
    $stmt = $db_pdo->prepare("INSERT INTO `event_records` (`Time`, `total_participants`, `current_participants`, `event_name`) VALUES (:hourr, :total, :current, :event_name)");

    $stmt->bindParam(':current', $current_participants, PDO::PARAM_INT);
    $stmt->bindParam(':hourr', $timeInput, PDO::PARAM_STR);
    $stmt->bindParam(':event_name', $eventName, PDO::PARAM_STR);
    $stmt->bindParam(':total', $total, PDO::PARAM_INT);

    $stmt->execute();
    echo json_encode(['status' => 'success', 'message' => 'People count updated successfully']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>