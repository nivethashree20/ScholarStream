<?php
require_once 'config/db_connect.php';
try {
    $stmt = $pdo->query("PRAGMA table_info(research_papers)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($columns, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
