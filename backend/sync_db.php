<?php
require_once 'config/db_connect.php';

try {
    // Ensure admin_comments column exists
    $stmt = $pdo->query("PRAGMA table_info(research_papers)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
    
    if (!in_array('admin_comments', $columns)) {
        $pdo->exec("ALTER TABLE research_papers ADD COLUMN admin_comments TEXT");
    }
    
    if (!in_array('status', $columns)) {
        $pdo->exec("ALTER TABLE research_papers ADD COLUMN status VARCHAR(50) DEFAULT 'Pending'");
    }
    
    echo "Database sync completed successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
