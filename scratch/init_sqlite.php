<?php
require_once __DIR__ . '/../backend/config/db_connect.php';

try {
    $schema = file_get_contents(__DIR__ . '/../database/sql/schema_sqlite.sql');
    $pdo->exec($schema);
    echo "Database initialized successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
