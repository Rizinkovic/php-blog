<?php
require_once __DIR__ . '/config.php';
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_redirect'] = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: blog/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['article_id'], $_POST['content'])) {
    $article_id = intval($_POST['article_id']);
    $user_id = $_SESSION['user_id'];
    $content = trim($_POST['content']);
    
    if (!empty($content)) {
        $stmt = $conn->prepare("INSERT INTO comments (article_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $article_id, $user_id, $content);
        $stmt->execute();
    }
}

header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
exit();
?>