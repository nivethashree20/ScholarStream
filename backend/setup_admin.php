<?php
require_once 'config/db_connect.php';

$email = 'scholarstream@gailm.com';
$password = password_hash('scholarstream123', PASSWORD_BCRYPT);
$name = 'System Administrator';
$role = 'admin';

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($user = $stmt->fetch()) {
        // Update existing
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = ? WHERE email = ?");
        $stmt->execute([$password, $role, $email]);
        echo "Admin user updated successfully.\n";
    } else {
        // Create new
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role]);
        echo "Admin user created successfully.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
