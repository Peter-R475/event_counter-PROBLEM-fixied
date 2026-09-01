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
    <title>Menu</title>
    <link rel="stylesheet" href="style\menu_style.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>

<body>
    <div class="nav-bar">
        <a href="chart.php">
            <div class="icon-box">
                <ion-icon name="speedometer-outline"></ion-icon>
                <p>Chart</p>
            </div>
        </a>
        <a href="index.php">
            <div class="icon-box">
                <ion-icon name="camera-outline"></ion-icon>
                <p>Cam Detection</p>
            </div>
        </a>
        <a href="logout.php">
            <div class="icon-box">
                <ion-icon name="log-out-outline"></ion-icon>
                <p>Logout</p>
            </div>
        </a>
    </div>
</body>

</html>