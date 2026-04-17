<?php
require_once '../../backend/config/db_connect.php';
require_once '../utils/response.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    sendUnauthorized();
}

try {
    // Fetch global stats
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total,
        COALESCE(SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END), 0) as approved,
        COALESCE(SUM(CASE WHEN status = 'Declined' THEN 1 ELSE 0 END), 0) as declined,
        COALESCE(SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END), 0) as pending,
        COALESCE(SUM(CASE WHEN status = 'Revision Required' THEN 1 ELSE 0 END), 0) as revision
    FROM research_papers");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch recent submissions (last 5)
    $stmt = $pdo->query("SELECT rp.*, u.name as student_name 
        FROM research_papers rp
        JOIN users u ON rp.student_id = u.id
        ORDER BY rp.submitted_at DESC
        LIMIT 5");
    $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(true, "Admin stats retrieved", [
        'stats' => $stats,
        'recent' => $recent
    ]);
} catch (PDOException $e) {
    sendResponse(false, "Database error: " . $e->getMessage(), null, 500);
}
?>
