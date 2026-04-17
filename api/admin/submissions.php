<?php
require_once '../../backend/config/db_connect.php';
require_once '../utils/response.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    sendUnauthorized();
}

$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

try {
    $query = "SELECT rp.*, u.name as student_name, u.email as student_email 
              FROM research_papers rp
              JOIN users u ON rp.student_id = u.id
              WHERE (rp.title LIKE ? OR u.name LIKE ?)";
    
    $params = [$search, $search];

    if ($status !== 'all') {
        $query .= " AND rp.status = ?";
        $params[] = $status;
    }

    $query .= " ORDER BY rp.submitted_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(true, "Submissions retrieved", $submissions);
} catch (PDOException $e) {
    sendResponse(false, "Database error: " . $e->getMessage(), null, 500);
}
?>
