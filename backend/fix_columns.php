<?php
require_once 'config/db_connect.php';

try {
    // Check if column already exists
    $stmt = $pdo->query("PRAGMA table_info(research_papers)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $exists = false;
    foreach ($columns as $col) {
        if ($col['name'] === 'admin_comments') {
            $exists = true;
            break;
        }
    }

    if (!$exists) {
        $pdo->exec("ALTER TABLE research_papers ADD COLUMN admin_comments TEXT");
        echo "Successfully added 'admin_comments' column to research_papers table.\n";
    } else {
        echo "'admin_comments' column already exists.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
