<?php
require_once '../backend/config/db_connect.php';

try {
    // Read the schema file
    $sql_file = __DIR__ . '/../database/sql/schema_pg.sql';
    if (!file_exists($sql_file)) {
        die("PostgreSQL schema file not found.");
    }
    
    $sql = file_get_contents($sql_file);
    
    // Execute the SQL schema
    $pdo->exec($sql);
    
    echo "PostgreSQL database tables created successfully!";
} catch (\PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>
