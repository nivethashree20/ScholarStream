<?php
/**
 * Helper to send consistent JSON responses
 */
function sendResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

/**
 * Handle unauthorized access
 */
function sendUnauthorized($message = "Unauthorized access") {
    sendResponse(false, $message, null, 401);
}

/**
 * Handle bad requests
 */
function sendBadRequest($message = "Bad request") {
    sendResponse(false, $message, null, 400);
}
?>
