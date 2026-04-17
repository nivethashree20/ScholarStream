<?php
require_once '../utils/response.php';
session_start();
session_unset();
session_destroy();
sendResponse(true, "Logged out successfully");
?>
