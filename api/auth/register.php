<?php
require_once '../../backend/config/db_connect.php';
require_once '../utils/response.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendBadRequest("Method not allowed");
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$name = $input['name'] ?? '';
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';
$organization = $input['organization'] ?? '';

if (empty($name) || empty($email) || empty($password) || empty($organization)) {
    sendResponse(false, "All fields are required", null, 400);
}

if ($password !== $confirm_password) {
    sendResponse(false, "Passwords do not match", null, 400);
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        sendResponse(false, "Email already registered", null, 409);
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, organization) VALUES (?, ?, ?, 'student', ?)");
    
    if ($stmt->execute([$name, $email, $hashed_password, $organization])) {
        $userId = $pdo->lastInsertId();
        
        // Return account created status
        sendResponse(true, "Registration successful", [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'role' => 'student'
        ], 201);
    } else {
        sendResponse(false, "Registration failed", null, 500);
    }
} catch (PDOException $e) {
    sendResponse(false, "Database error: " . $e->getMessage(), null, 500);
}
?>
