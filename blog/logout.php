<?php
require_once __DIR__ . '/../config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Store logout message in a temporary variable
$logout_message = "Thanks for visiting my blog! Come back soon!";

// Destroy session
session_unset();
session_destroy();

// Start a new session to store the logout message
session_start();
$_SESSION['logout_message'] = $logout_message;

// Redirect to homepage
header("Location: ../index.php");
exit();
?>
