<?php
include("connect.php");

header('Content-Type: application/json');

$stmt = $db_pdo->prepare("SELECT `current_participants` FROM `halloween_event_participants` ORDER BY `Tme` DESC LIMIT 1");

if ($stmt->execute()) {
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentCount = $result ? (int) $result['current_participants'] : 0;

    echo json_encode([
        'status' => 'success',
        'message' => 'People count retrieved successfully',
        'currentParticipants' => $currentCount,
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve people count'
    ]);
}
?>