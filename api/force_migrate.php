<?php
require_once '../backend/config/db_connect.php';

try {
    $queries = [
        "ALTER TABLE research_papers ADD COLUMN IF NOT EXISTS version INT DEFAULT 1",
        "ALTER TABLE research_papers ADD COLUMN IF NOT EXISTS parent_id INT DEFAULT NULL",
        "ALTER TABLE research_papers ADD COLUMN IF NOT EXISTS is_latest BOOLEAN DEFAULT TRUE",
        "ALTER TABLE research_papers ADD COLUMN IF NOT EXISTS certificate_id VARCHAR(255) UNIQUE DEFAULT NULL",
        "ALTER TABLE research_papers ADD COLUMN IF NOT EXISTS admin_comments TEXT DEFAULT NULL"
    ];
    
    foreach ($queries as $sql) {
        $pdo->exec($sql);
        echo "Executed: " . $sql . "<br>";
    }
    
    // Prove it exists
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'research_papers'");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Current columns in research_papers: <br>";
    echo implode(', ', $cols);
    
} catch (\PDOException $e) {
    echo "Error updating tables: " . $e->getMessage();
}
?>
