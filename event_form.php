<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
include 'fetch_existing_event.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Form</title>
    <link rel="stylesheet" href="style/event_F.css">
</head>

<body>

    <div class="main-wrapper">

        <button id="toggle-view-btn" onclick="toggleContainers()">Switch to Existing Event</button>

        <div id="existing-container" class="form-container" style="display: none;">
            <h1>Existing Event</h1>

            <form action="sendTo_event_info.php" method="POST">
                <label for="existing_event">Select Existing Event (Optional)</label>
                <select id="existing_event" name="existing_event" onchange="fetchEventData(this.value)">
                    <option value="">Select existing event</option>
                    <?php foreach ($existing_events as $event): ?>
                        <option value="<?php echo htmlspecialchars($event['name']); ?>">
                            <?php echo htmlspecialchars($event['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" name="Submit" value="Submit">
            </form>
        </div>

        <div id="form-container" class="form-container">
            <form action="sendTo_event_info.php" method="POST">
                <h1>Please Fill Your Event Information</h1>
                <label for="event_name">Event Name</label>
                <input type="text" id="event_name" name="event_name" required>

                <label for="event_location">Event Location</label>
                <input type="text" id="event_location" name="event_location" required>

                <label for="event_date">Event Date</label>
                <input type="date" id="event_date" name="event_date" required>

                <label for="event_time">Event Time</label>
                <input type="time" id="event_time" name="event_time" required>

                <label for="event_description">Event Description</label>
                <input type="text" id="event_description" name="event_description" required>

                <input type="submit" name="Submit" value="Submit">
            </form>
        </div>
    </div>

    <script>
        function toggleContainers() {
            const formContainer = document.getElementById('form-container');
            const existingContainer = document.getElementById('existing-container');
            const button = document.getElementById('toggle-view-btn');

            if (formContainer.style.display === 'none') {
                formContainer.style.display = 'block';
                existingContainer.style.display = 'none';
                button.textContent = 'Switch to Existing Event';
            } else {
                formContainer.style.display = 'none';
                existingContainer.style.display = 'block';
                button.textContent = 'Switch to Create Event';
            }
        }
    </script>
</body>

</html>