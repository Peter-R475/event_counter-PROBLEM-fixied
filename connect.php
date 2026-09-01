<?php
$dbfile = __DIR__ . "/data/detections.db";

try {
    $dsn = "sqlite:$dbfile";
    $db_pdo = new PDO($dsn);
    $db_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set WAL mode for concurrency
    $db_pdo->exec("PRAGMA journal_mode = WAL;");
    $db_pdo->exec("PRAGMA synchronous = NORMAL;");

    $db_staff_pdo = $db_pdo;
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Database connection failed: " . $e->getMessage());
}
?>