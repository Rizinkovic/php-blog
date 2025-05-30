<?php
// Database config
$db_host = "sql211.infinityfree.com";
$db_user = "if0_39077665";
$db_pass = "salemx1234"; // Empty for XAMPP/WAMP
$db_name = "if0_39077665_myblog";

// Connect to database first (no session dependency)
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Session handling with proper error suppression
if (!headers_sent()) {
    // Set cookie params BEFORE starting session
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => 'localhost',
        'secure' => isset($_SERVER['HTTPS']), // Auto-detect HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    
    // Start session only if not active
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

// Simple redirect function
function redirect($url) {
    if (!headers_sent()) {
        header("Location: $url");
        exit();
    }
    // Fallback for when headers already sent
    echo "<script>window.location.href='$url';</script>";
    exit();
}

// CSRF token handling
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        if (function_exists('random_bytes')) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } else {
            $_SESSION['csrf_token'] = md5(uniqid(rand(), true));
        }
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Error reporting (development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>