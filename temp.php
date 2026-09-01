<?php
$dbfile = __DIR__ . "/data/detections_for_initial.db";

try {
    $db_pdo = new PDO("sqlite:$dbfile");
    $db_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db_pdo->beginTransaction();

    // 1. Get all user table names (excluding internal SQLite system tables)
    $stmt = $db_pdo->query("
        SELECT name FROM sqlite_master 
        WHERE type = 'table' AND name NOT LIKE 'sqlite_%'
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 2. Clear all rows from each table
    foreach ($tables as $table) {
        $db_pdo->exec("DELETE FROM `$table`");
    }

    // 3. Reset auto-increment sequence counters (if any exist)
    $db_pdo->exec("DELETE FROM sqlite_sequence");

    $db_pdo->commit();
    echo "All table data cleared successfully.";

} catch (PDOException $e) {
    if ($db_pdo->inTransaction()) {
        $db_pdo->rollBack();
    }
    error_log("Failed to clear database: " . $e->getMessage());
    die("Error: " . $e->getMessage());
}
?>