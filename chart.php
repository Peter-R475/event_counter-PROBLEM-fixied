<?php
session_start();
if (!$_SESSION['user']) {
    header("Location: login.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['existing_event']) && $_POST['existing_event'] !== '') {
    $_SESSION['event_name'] = $_POST['existing_event'];
}
include 'fetch_existing_event.php';
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
        <form action="chart.php" method="POST">
            <label for="existing_event">Current Event:</label>
            <select id="existing_event" name="existing_event" onchange="this.form.submit()">
                <?php foreach ($existing_events as $event): ?>
                    <option value="<?php echo htmlspecialchars($event['name']); ?>" <?php if (isset($_SESSION['event_name']) && $_SESSION['event_name'] == $event['name'])
                           echo 'selected'; ?>>
                        <?php echo htmlspecialchars($event['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </section>

    <section class="filter-section" style="margin: 20px auto; width: 80%; text-align: right;">
        <form method="GET" action="export_excel.php">
            <!-- Pass existing filter context -->
            <input type="hidden" name="interval" value="<?php echo htmlspecialchars($_GET['interval'] ?? 'hour'); ?>">
            <button type="submit" name="export" value="xlsx">Export to XLSX</button>
        </form>
    </section>

    <main class="chart-container">
        <canvas id="peopleCountChart"></canvas>
    </main>

    <footer class="footer">
        &copy; 2026 System Monitor
    </footer>
    <?php include 'chart_generation.php'; ?>
</body>

</html>