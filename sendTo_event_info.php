<?php
session_start();
include 'connect.php';

// Check user authorization
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST["existing_event"])) {
    $_SESSION['event_name'] = $_POST['existing_event'];
    header('Location: menu.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Submit'])) {

    if (
        !empty($_POST['event_name']) &&
        !empty($_POST['event_location']) &&
        !empty($_POST['event_date']) &&
        !empty($_POST['event_time']) &&
        !empty($_POST['event_description'])
    ) {
        $event_name = $_POST['event_name'];
        $event_location = $_POST['event_location'];
        $event_date = $_POST['event_date'];
        $event_time = $_POST['event_time'];
        $event_description = $_POST['event_description'];
        $staff_id = $_SESSION['user'];

        try {
            $sql = 'INSERT INTO "event_info" ("name", "location", "date", "time", "description", "staff_id") 
                    VALUES (:name, :location, :date, :time, :description, :staff)';

            $stmt = $db_staff_pdo->prepare($sql);

            $stmt->execute([
                ':name' => $event_name,
                ':location' => $event_location,
                ':date' => $event_date,
                ':time' => $event_time,
                ':description' => $event_description,
                ':staff' => $staff_id
            ]);

            $_SESSION['event_name'] = $event_name;
            header("Location: menu.php");
            exit();

        } catch (PDOException $e) {
            error_log($e->getMessage());
            header("Location: event_form.php");
            exit();
        }
    } else {
        error_log("Form submission failed: Empty required fields.");
        header("Location: event_form.php");
        exit();
    }

} else {
    header("Location: event_form.php");
    exit();
}