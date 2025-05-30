<?php
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    redirect("login.php");
}

// Get current user
$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT username FROM users WHERE id = $user_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?></h1>
    
    <h3>Quick Actions:</h3>
    <ul>
        <li><a href="posts.php">Manage Posts</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</body>
</html>