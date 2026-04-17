<?php
require_once '../../backend/config/db_connect.php';
require_once '../utils/response.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    sendUnauthorized();
}

$student_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

try {
    $stmt = $pdo->prepare("SELECT DISTINCT rp.* 
        FROM research_papers rp 
        LEFT JOIN paper_coauthors pc ON rp.id = pc.paper_id 
        WHERE (rp.student_id = ? OR pc.student_email = (SELECT email FROM users WHERE id = ?)) 
        AND (rp.title LIKE ?) 
        ORDER BY rp.submitted_at DESC");
    $stmt->execute([$student_id, $student_id, $search]);
    $papers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(true, "Papers retrieved", $papers);
} catch (PDOException $e) {
    sendResponse(false, "Database error: " . $e->getMessage(), null, 500);
}
?>
