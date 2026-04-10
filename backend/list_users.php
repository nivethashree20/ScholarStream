<?php
require_once 'config/db_connect.php';
$stmt = $pdo->query("SELECT email, role FROM users");
while ($row = $stmt->fetch()) {
    echo $row['email'] . " - " . $row['role'] . "\n";
}
?>
