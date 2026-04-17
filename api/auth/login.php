<?php
require_once '../../backend/config/db_connect.php';
require_once '../utils/response.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendBadRequest("Method not allowed");
}

// Support both JSON raw body and standard POST
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$email = $input['email'] ?? '';
$password = $input['password'] ?? '';
$requested_role = $input['role'] ?? 'student';

if (empty($email) || empty($password)) {
    sendResponse(false, "Email and password are required", null, 400);
}

// Query user
$query = "SELECT * FROM users WHERE email = ?";
if ($requested_role === 'admin') {
    $query .= " AND role = 'admin'";
} else {
    $query .= " AND role = 'student'";
}

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Setup Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['organization'] = $user['organization'] ?? '';

        // Prepare User Data for return (security: remove password)
        $userData = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'organization' => $user['organization'] ?? ''
        ];

        sendResponse(true, "Login successful", $userData);
    } else {
        sendResponse(false, "Invalid credentials", null, 401);
    }
} catch (PDOException $e) {
    sendResponse(false, "Database error: " . $e->getMessage(), null, 500);
}
?>
