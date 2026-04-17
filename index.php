<?php
if (file_exists(__DIR__ . '/index.html')) {
    readfile(__DIR__ . '/index.html');
} else {
    header("Location: /frontend/index.php");
}
exit();
?>
