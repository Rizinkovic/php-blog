<?php
require_once __DIR__ . '/../config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set logout message
$_SESSION['logout_message'] = "Thanks for visiting my blog! Come back soon!";

// Destroy session
session_unset();
session_destroy();

// Redirect to homepage
header("Location: ../index.php");
exit();
?>