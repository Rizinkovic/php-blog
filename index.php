<?php
require_once 'config.php';
require_once './includes/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'like') {
    // Clear any previous output
    if (ob_get_level()) {
    ob_clean();
}

    
    // Set proper header first
    header('Content-Type: application/json');
    
    if (!isLoggedIn()) {
        die(json_encode(['error' => 'Please log in to like articles']));
    }
    
    $article_id = intval($_POST['article_id']);
    $user_id = $_SESSION['user_id'];
    
    try {
        $conn->begin_transaction();
        
        // Check if like exists
        $check_like = $conn->prepare("SELECT id FROM article_likes WHERE article_id = ? AND user_id = ?");
        $check_like->bind_param("ii", $article_id, $user_id);
        $check_like->execute();
        $check_like->store_result();
        
        if ($check_like->num_rows > 0) {
            // Unlike the article
            $delete_like = $conn->prepare("DELETE FROM article_likes WHERE article_id = ? AND user_id = ?");
            $delete_like->bind_param("ii", $article_id, $user_id);
            $delete_like->execute();
            
            $update_article = $conn->prepare("UPDATE articles SET like_count = like_count - 1 WHERE id = ?");
            $update_article->bind_param("i", $article_id);
            $update_article->execute();
            
            $conn->commit();
            echo json_encode(['status' => 'unliked']);
        } else {
            // Like the article
            $insert_like = $conn->prepare("INSERT INTO article_likes (article_id, user_id) VALUES (?, ?)");
            $insert_like->bind_param("ii", $article_id, $user_id);
            $insert_like->execute();
            
            $update_article = $conn->prepare("UPDATE articles SET like_count = like_count + 1 WHERE id = ?");
            $update_article->bind_param("i", $article_id);
            $update_article->execute();
            
            $conn->commit();
            echo json_encode(['status' => 'liked']);
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    exit(); // Terminate script after handling the like
}

// Display logout message if exists
if (isset($_SESSION['logout_message'])) {
    echo '<div class="logout-message" style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 10px; border-radius: 4px; text-align: center;">';
    echo htmlspecialchars($_SESSION['logout_message']);
    echo '</div>';
    unset($_SESSION['logout_message']);
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('<p style="color:red;">Database connection error</p>');
}

// Check if necessary tables/columns exist
$check_tables = [
    "SHOW COLUMNS FROM comments LIKE 'content'",
    "SHOW COLUMNS FROM articles LIKE 'like_count'",
    "SHOW COLUMNS FROM articles LIKE 'view_count'",
    "SHOW TABLES LIKE 'article_likes'"
];

foreach ($check_tables as $query) {
    $result = $conn->query($query);
    if ($result->num_rows === 0) {
        // Create missing tables/columns
        if (strpos($query, "SHOW COLUMNS FROM comments") !== false) {
            $conn->query("ALTER TABLE comments ADD COLUMN content TEXT NOT NULL AFTER user_id");
        } elseif (strpos($query, "SHOW COLUMNS FROM articles LIKE 'like_count'") !== false) {
            $conn->query("ALTER TABLE articles ADD COLUMN like_count INT DEFAULT 0");
        } elseif (strpos($query, "SHOW COLUMNS FROM articles LIKE 'view_count'") !== false) {
            $conn->query("ALTER TABLE articles ADD COLUMN view_count INT DEFAULT 0");
        } elseif (strpos($query, "SHOW TABLES LIKE 'article_likes'") !== false) {
            $conn->query("CREATE TABLE IF NOT EXISTS article_likes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                article_id INT NOT NULL,
                user_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_like (article_id, user_id)
            )");
        }
    }
}

require_once './includes/header.php';

// Add test article creation if none exist
$test_article_check = $conn->query("SELECT id FROM articles LIMIT 1");
if ($test_article_check->num_rows === 0) {
    $test_title = "Welcome to Our Blog!";
    $test_content = "This is a sample article to test the commenting system. Register an account and leave a comment!";
    
    $stmt = $conn->prepare("INSERT INTO articles (title, content, author_id, like_count, view_count) VALUES (?, ?, ?, 0, 0)");
    $author_id = 1;
    $stmt->bind_param("ssi", $test_title, $test_content, $author_id);
    $stmt->execute();
}

// Handle like action
// Handle like action - PUT THIS AT THE TOP OF YOUR FILE RIGHT AFTER session_start()


// Function to handle comments
function handleComments($conn, $article_id) {
    $output = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_text'])) {
        if (!isLoggedIn()) {
            $_SESSION['login_redirect'] = "?post=$article_id";
            header("Location: blog/login.php");
            exit();
        }
        
        $comment = trim($_POST['comment_text']);
        $user_id = $_SESSION['user_id'] ?? 0;
        
        if (!empty($comment)) {
            $stmt = $conn->prepare("INSERT INTO comments (article_id, user_id, content) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $article_id, $user_id, $comment);
            
            if ($stmt->execute()) {
                header("Location: ?post=$article_id");
                exit();
            } else {
                $output .= '<div class="alert error" style="color:red;padding:10px;margin:10px 0;">Failed to post comment: '.htmlspecialchars($conn->error).'</div>';
            }
            $stmt->close();
        }
    }
    
    $stmt = $conn->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.article_id = ? ORDER BY c.created_at DESC");
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $comments = $stmt->get_result();
    
    $output .= '<div class="comments-section" style="margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px;">';
    $output .= '<h3>Comments</h3>';
    
    if (isLoggedIn()) {
        $output .= '<form method="POST" style="margin-bottom: 20px;">
            <div class="form-group">
                <textarea name="comment_text" class="form-control" rows="3" placeholder="Write your comment..." required style="width: 100%; padding: 10px;"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 8px 15px; background: #007bff; color: white; border: none;">Post Comment</button>
        </form>';
    } else {
        $output .= '<div class="comment-notice" style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <p>Please <a href="blog/login.php">log in</a> or <a href="blog/register.php">register</a> to post comments</p>
        </div>';
    }
    
    if ($comments->num_rows > 0) {
        while ($comment = $comments->fetch_assoc()) {
            $output .= '<div class="comment" style="border-bottom: 1px solid #eee; padding: 15px 0;">
                <div style="font-weight: bold; margin-bottom: 5px;">
                    '.htmlspecialchars($comment['username'] ?? 'Unknown').'
                    <small style="color: #666; font-weight: normal;">'.date('M j, Y g:i a', strtotime($comment['created_at'])).'</small>
                </div>
                <div style="margin-top: 8px;">
                    '.nl2br(htmlspecialchars($comment['content'] ?? '')).'
                </div>
            </div>';
        }
    } else {
        $output .= '<p>No comments yet. Be the first to comment!</p>';
    }
    
    $output .= '</div>';
    return $output;
}

// Display user status
echo '<div class="user-status" style="text-align: left; padding: 10px; background: #fff; margin-top: -70px;">';
if (isLoggedIn()) {
    $username = $_SESSION['username'] ?? 'User';
    echo 'Welcome, <strong>' . htmlspecialchars($username) . '</strong>! | ';
    echo '<a href="blog/logout.php" style="cursor: pointer;">Logout</a>';
} else {
    echo '<a href="blog/login.php">Login</a> | ';
    echo '<a href="blog/register.php">Register</a>';
}
echo '</div>';

// Get single article if requested
if (isset($_GET['post'])) {
    $post_id = intval($_GET['post']);
    
    // Increment view count
    $conn->query("UPDATE articles SET view_count = view_count + 1 WHERE id = $post_id");
    
    $article_query = "SELECT a.*, u.username as author_name FROM articles a LEFT JOIN users u ON a.author_id = u.id WHERE a.id = ?";
    $stmt = $conn->prepare($article_query);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $article_result = $stmt->get_result();
    
    if ($article_result->num_rows > 0) {
        $article = $article_result->fetch_assoc();
        
        // Check if current user liked the article
        $is_liked = false;
        if (isLoggedIn()) {
            $user_id = $_SESSION['user_id'];
            $check_like = $conn->prepare("SELECT id FROM article_likes WHERE article_id = ? AND user_id = ?");
            $check_like->bind_param("ii", $post_id, $user_id);
            $check_like->execute();
            $check_like->store_result();
            $is_liked = $check_like->num_rows > 0;
        }
        ?>
        <div class="main-layout">
            <main class="content-area">
                <article class="single-post">
                    <h2><?php echo htmlspecialchars($article['title'] ?? ''); ?></h2>
                    <div class="post-meta">
                        Posted by <?php echo htmlspecialchars($article['author_name'] ?? $article['author'] ?? 'Admin'); ?> on 
                        <?php echo date('F j, Y', strtotime($article['created_at'] ?? 'now')); ?>
                        <span class="view-count" style="margin-left: 15px;">
                            <i class="fas fa-eye"></i> <?php echo intval($article['view_count'] ?? 0); ?> views
                        </span>
                    </div>
                    <div class="post-content">
                        <?php if (!empty($article['featured_image'])): ?>
                            <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" 
                                 class="featured-image" 
                                 style="max-width:100%;height:auto;margin-bottom:20px;"
                                 onerror="this.style.display='none'">
                        <?php endif; ?>
                        <?php 
                        $allowed_tags = '<p><a><strong><em><br><ul><ol><li><h2><h3><h4>';
                        echo strip_tags($article['content'] ?? '', $allowed_tags);
                        ?>
                    </div>
                    
                    <div class="post-actions" style="margin: 20px 0; display: flex; align-items: center;">
                        <form id="like-form" method="POST" style="margin-right: 20px;">
                            <input type="hidden" name="action" value="like">
                            <input type="hidden" name="article_id" value="<?php echo $post_id; ?>">
                            <button type="button" id="like-button" style="background: none; border: none; cursor: pointer;">
                                <i class="<?php echo $is_liked ? 'fas' : 'far'; ?> fa-heart" style="color: <?php echo $is_liked ? 'red' : '#333'; ?>; font-size: 1.2em;"></i>
                                <span id="like-count"><?php echo intval($article['like_count'] ?? 0); ?></span>
                            </button>
                        </form>
                        
                        <div class="social-share" style="display: flex; gap: 10px;">
                            <span>Share:</span>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>" 
                               target="_blank" 
                               title="Share on Facebook"
                               style="color: #3333ff;">
                                <i class="fa-brands fa-facebook"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>&text=<?php echo urlencode($article['title'] ?? ''); ?>" 
                               target="_blank" 
                               title="Share on Twitter"
                               style="color: #000000;">
                                <i class="fa-brands fa-x-twitter"></i>
                            </a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>&title=<?php echo urlencode($article['title'] ?? ''); ?>" 
                               target="_blank" 
                               title="Share on LinkedIn"
                               style="color: #0077B5;">
                                <i class="fa-brands fa-linkedin"></i>
                            </a>
                            <a href="mailto:?subject=<?php echo rawurlencode($article['title'] ?? ''); ?>&body=Check out this article: <?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>" 
                               title="Share via Email"
                               style="color: #333333;">
                                <i class="fa-solid fa-envelope"></i>
                            </a>
                        </div>
                    </div>
                    
                    <?php echo handleComments($conn, $article['id']); ?>
                </article>
            </main>
            <aside class="sidebar">
                <?php require_once 'includes/sidebar.php'; ?>
            </aside>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
    const likeButton = document.getElementById('like-button');
    
    likeButton.addEventListener('click', async function() {
        try {
            const form = document.getElementById('like-form');
            const response = await fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(new FormData(form))
            });
            
            // Check if response is OK
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            // Try to parse JSON
            const data = await response.json();
            
            // Update UI
            const likeIcon = likeButton.querySelector('i');
            const likeCount = document.getElementById('like-count');
            
            if (data.status === 'liked') {
                likeIcon.classList.replace('far', 'fas');
                likeIcon.style.color = 'red';
                likeCount.textContent = parseInt(likeCount.textContent) + 1;
            } else if (data.status === 'unliked') {
                likeIcon.classList.replace('fas', 'far');
                likeIcon.style.color = '#333';
                likeCount.textContent = parseInt(likeCount.textContent) - 1;
            } else if (data.error) {
                alert(data.error);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to process like: ' + error.message);
        }
    });
});
        </script>
        <?php
    } else {
        echo '<p>Article not found.</p>';
    }
} else {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $where = '';
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $where = "WHERE a.title LIKE ? OR a.content LIKE ?";
        $search_term = "%$search%";
        $params = [$search_term, $search_term];
        $types = "ss";
    }
    
    $query = "SELECT a.*, u.username as author_name FROM articles a LEFT JOIN users u ON a.author_id = u.id $where ORDER BY a.created_at DESC";
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        ?>
        <div class="main-layout">
            <main class="content-area">
                <div class="article-list">
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <article class="post-excerpt">
                        <h2><a href="index.php?post=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['title'] ?? ''); ?></a></h2>
                        <div class="post-meta">
                            Posted by <?php echo htmlspecialchars($row['author_name'] ?? $row['author'] ?? 'Admin'); ?> on 
                            <?php echo date('F j, Y', strtotime($row['created_at'] ?? 'now')); ?>
                            <span class="post-stats" style="margin-left: 15px;">
                                <i class="far fa-heart"></i> <?php echo intval($row['like_count'] ?? 0); ?>
                                <i class="fas fa-eye" style="margin-left: 10px;"></i> <?php echo intval($row['view_count'] ?? 0); ?>
                            </span>
                        </div>
                        <div class="post-content">
                            <?php if (!empty($row['featured_image'])): ?>
                                <img src="<?php echo htmlspecialchars($row['featured_image']); ?>" 
                                     class="featured-image-thumb" 
                                     style="max-width:200px;float:left;margin:0 15px 15px 0;"
                                     onerror="this.style.display='none'">
                            <?php endif; ?>
                            <?php 
                            $content = strip_tags($row['content'] ?? '', '<p><a><strong><em><br>');
                            if (strlen($content) > 200) {
                                $content = substr($content, 0, 200) . '... <a class="read-more" href="index.php?post='.$row['id'].'">Read more</a>';
                            }
                            echo nl2br($content);
                            ?>
                            <div style="clear:both;"></div>
                        </div>
                    </article>
                    <?php endwhile; ?>
                </div>
            </main>
            <aside class="sidebar">
                <?php require_once 'includes/sidebar.php'; ?>
            </aside>
        </div>
        <?php
    } else {
        echo '<p>No articles found'.(!empty($search) ? ' matching your search.' : '.').'</p>';
    }
}

require_once 'includes/footer.php';
?>