<?php
// migrate_to_pg.php
// This script creates the necessary tables in PostgreSQL.
// Run this once after deploying to Render by accessing [your-url]/backend/migrate_to_pg.php

require_once 'config/db_connect.php';

echo "<pre>";
echo "Starting migration to PostgreSQL...\n";

// Ensure we are using PostgreSQL
if (!isset($dsn) || strpos($dsn, 'pgsql') === false) {
    die("Error: This script must be run with a PostgreSQL connection (DATABASE_URL must be set).\n");
}

try {
    $schema_path = __DIR__ . '/../database/sql/schema_pg.sql';
    if (!file_exists($schema_path)) {
        die("Error: Schema file not found at $schema_path\n");
    }

    $sql = file_get_contents($schema_path);
    
    // Execute the schema
    $pdo->exec($sql);
    
    echo "Migration successful! Tables created and Admin user seeded.\n";
    echo "You can now delete this script for security.\n";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
echo "</pre>";
?>
