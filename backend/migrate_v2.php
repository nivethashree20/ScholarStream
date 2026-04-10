<?php
require_once 'config/db_connect.php';

try {
    $sql = file_get_contents(__DIR__ . '/../database/sql/update_v2.sql');
    $pdo->exec($sql);
    echo "Migration v2 successful!\n";
} catch (\PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
