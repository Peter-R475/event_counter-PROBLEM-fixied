<?php
// Path to your SQLite database file
$db_path = './data/detections.db';
 
// SQLite Data Source Name (DSN)
$dsn = "sqlite:$db_path";
 
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
 
try {
    $db_pdo = new PDO($dsn, null, null, $options);
    $db_staff_pdo = $db_pdo;
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Database connection failed: " . $e->getMessage());
}
?>