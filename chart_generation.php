<?php
include 'connect.php';

$interval = $_GET['interval'] ?? 'hour';
$eventName = $_SESSION['event_name'] ?? '';

// Format in DB: DD:MM:YYYY:HH:MM:SS (Length: 22 chars fixed if padded)
// Positions: 
// Day (1-2 or padded): sub-string before 1st ':'
// Month: between 1st and 2nd ':'
// Year: between 2nd and 3rd ':' (always index -17 to -14 if length fixed, or split by ':')

// Native SQLite string transformation to ISO 8601 (YYYY-MM-DD HH:MM:SS):
if ($interval === 'minute') {
    // Truncates SS -> YYYY-MM-DD HH:MM:00
    $minuteFormat = "%Y-%m-%d %H:%M:00";
    $timeGroupExpr = "strftime('$minuteFormat', 
        substr(`Time`, 7, 4) || '-' || 
        substr(`Time`, 4, 2) || '-' || 
        substr(`Time`, 1, 2) || ' ' || 
        substr(`Time`, 12, 8))";
} else {
    // Truncates MM:SS -> YYYY-MM-DD HH:00:00
    $hourFormat = "%Y-%m-%d %H:00:00";
    $timeGroupExpr = "strftime('$hourFormat', 
        substr(`Time`, 7, 4) || '-' || 
        substr(`Time`, 4, 2) || '-' || 
        substr(`Time`, 1, 2) || ' ' || 
        substr(`Time`, 12, 8))";
}

$query = "SELECT 
            {$timeGroupExpr} AS TimeGroup, 
            AVG(`current_participants`) AS current_participants, 
            MAX(`total_participants`) AS max_total_participants 
          FROM event_records 
          WHERE `event_name` = :event_name 
          GROUP BY TimeGroup 
          ORDER BY TimeGroup ASC";

$stmt = $db_pdo->prepare($query);
$stmt->bindParam(":event_name", $eventName, PDO::PARAM_STR);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$latest_stmt = $db_pdo->prepare('SELECT `Time`, `current_participants`, `total_participants` FROM event_records WHERE `event_name` = :event_name ORDER BY `id` DESC LIMIT 1');
$latest_stmt->bindParam(":event_name", $eventName, PDO::PARAM_STR);
$latest_stmt->execute();
$latest_data = $latest_stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$labels = [];
$current_values = [];

foreach ($data as $row) {
    $labels[] = $row['TimeGroup'];
    $current_values[] = (int) $row['current_participants'];
}
?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const currentElem = document.getElementById('currentParticipants');
        const totalElem = document.getElementById('totalParticipants');

        if (currentElem) currentElem.innerText = <?php echo json_encode($latest_data['current_participants'] ?? 0); ?>;
        if (totalElem) totalElem.innerText = <?php echo json_encode($latest_data['total_participants'] ?? 0); ?>;

        const chartLabels = <?php echo json_encode($labels); ?>;
        const currentParticipantsData = <?php echo json_encode($current_values); ?>;
        const ctx = document.getElementById('peopleCountChart').getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Current Participants',
                    data: currentParticipantsData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    tension: 0.1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    });
</script>