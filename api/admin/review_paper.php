<?php
require_once '../../backend/config/db_connect.php';
require_once '../utils/response.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    sendUnauthorized();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendBadRequest("Method not allowed");
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$paper_id = $input['paper_id'] ?? null;
$status = $input['status'] ?? '';
$comments = $input['comments'] ?? '';

if (!$paper_id || !$status) {
    sendResponse(false, "Paper ID and status are required", null, 400);
}

try {
    $stmt = $pdo->prepare("UPDATE research_papers SET status = ?, admin_comments = ? WHERE id = ?");
    if ($stmt->execute([$status, $comments, $paper_id])) {
        sendResponse(true, "Paper status updated successfully");
    } else {
        sendResponse(false, "Failed to update paper status", null, 500);
    }
} catch (PDOException $e) {
    sendResponse(false, "Database error: " . $e->getMessage(), null, 500);
}
?>
