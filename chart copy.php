<?php
session_start();
if (!$_SESSION['user']) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style/chart_style.css">
</head>

<body>
    <header class="header">
        <h1>Dashboard System</h1>
        <h1>
            Event:
            <?php echo $_SESSION['event_name']; ?>
        </h1>
    </header>

    <section class="filter-section" style="margin: 20px auto; width: 80%; text-align: right;">
        <form method="GET" action="chart.php">
            <label for="interval">View Data By: </label>
            <select name="interval" id="interval" onchange="this.form.submit()">
                <option value="minute" <?php if (isset($_GET['interval']) && $_GET['interval'] == 'minute')
                    echo 'selected'; ?>>Every Minute</option>
                <option value="hour" <?php if (!isset($_GET['interval']) || $_GET['interval'] == 'hour')
                    echo 'selected'; ?>>Every Hour</option>
            </select>
        </form>
    </section>

    <section class="filter-section" style="margin: 20px auto; width: 80%; text-align: right;">
        <form action="fetch_existing_event.php" method="POST">
            <label for="existing_event">Select Existing Event:</label>
            <select id="existing_event" name="existing_event" onchange="fetchEventData(this.value)">
                <option value="">Select existing event</option>
                <?php foreach ($existing_events as $event): ?>
                    <option value="<?php echo htmlspecialchars($event['name']); ?>">
                        <?php echo htmlspecialchars($event['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </section>

    <main class="chart-container">
        <canvas id="peopleCountChart"></canvas>
    </main>

    <footer class="footer">
        &copy; 2026 System Monitor
    </footer>
    <?php include 'chart_generation.php'; ?>
    <?php include 'fetch_existing_event.php'; ?>
</body>

</html>