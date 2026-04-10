<?php
require_once 'config/db_connect.php';

try {
    // Delete test papers
    $stmt = $pdo->prepare("DELETE FROM research_papers WHERE title LIKE 'Test Paper%' OR abstract LIKE 'This is a test%'");
    $stmt->execute();
    $papers_deleted = $stmt->rowCount();

    // Delete test users (students)
    $stmt = $pdo->prepare("DELETE FROM users WHERE role = 'student' AND (email LIKE 'test%' OR name LIKE 'Test%')");
    $stmt->execute();
    $users_deleted = $stmt->rowCount();

    echo "Cleanup successful:\n";
    echo "- Test papers deleted: $papers_deleted\n";
    echo "- Test users deleted: $users_deleted\n";

} catch (\PDOException $e) {
    echo "Cleanup failed: " . $e->getMessage() . "\n";
}
?>
