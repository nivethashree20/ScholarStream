<?php
require_once '../../backend/config/db_connect.php';
require_once '../utils/response.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    sendUnauthorized("Not logged in");
}

try {
    $stmt = $pdo->prepare("SELECT id, name, email, role, organization FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        sendResponse(true, "User session active", $user);
    } else {
        session_destroy();
        sendUnauthorized("User not found");
    }
} catch (PDOException $e) {
    sendResponse(false, "Database error: " . $e->getMessage(), null, 500);
}
?>
