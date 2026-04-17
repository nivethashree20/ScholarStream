<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /frontend/auth/index.php");
    exit();
}

if ($_SESSION['role'] === 'admin') {
    header("Location: /frontend/admin/dashboard.php");
} else {
    header("Location: /frontend/student/dashboard.php");
}
exit();
?>
