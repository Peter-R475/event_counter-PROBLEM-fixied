<?php
session_start();
include 'connect.php';
header("Content-Type: application/json");
// Read the raw POST data from the request body
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);
$staff_id = $_SESSION['user'];
$event_name = $_SESSION['event_name'];
// Extract variables
$command = $data['Command'] ?? 'null';
// Process data (e.g., business logic or database operations)
if ($command === "Reset") {
    $stmt = $db_staff_pdo->prepare("UPDATE `event_records` SET `current_participants` = 0, `total_participants` = 0 WHERE `event_name` = :event_name");
    $stmt->bindParam(":event_name", $event_name);
    $stmt->execute();
}

$response = [
    "status" => "success",
    "message" => "Hello, " . $command . ". PHP successfully processed your request."
];

// Return the response as JSON
echo json_encode($response);
exit;
?>