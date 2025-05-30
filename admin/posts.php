<?php
require_once '../config.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['flash_message'] = "Article deleted successfully";
    header("Location: posts.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $author_id = $_SESSION['user_id'];
    
    // Validate inputs
    $errors = [];
    if (empty($title)) $errors[] = "Title is required";
    if (empty($content)) $errors[] = "Content is required";
    
    if (empty($errors)) {
        if ($id > 0) {
            // Update existing article
            $stmt = $conn->prepare("UPDATE articles SET 
                                  title = ?, 
                                  content = ?, 
                                  featured_image = ?
                                  WHERE id = ?");
            $stmt->bind_param("sssi", $title, $content, $image_url, $id);
        } else {
            // Create new article
            $stmt = $conn->prepare("INSERT INTO articles 
                                  (title, content, author_id, featured_image) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssis", $title, $content, $author_id, $image_url);
        }
        
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['flash_message'] = $id > 0 ? "Article updated successfully" : "Article created successfully";
        header("Location: posts.php");
        exit();
    }
}

// Get article to edit
$editing = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editing = $result->fetch_assoc();
    $stmt->close();
}

// Get all articles
$articles = $conn->query("SELECT * FROM articles ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($editing) ? 'Edit Post' : 'Add New Post' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .tox-tinymce { min-height: 400px; }
        .featured-image-preview { max-width: 100%; height: auto; margin-bottom: 1rem; }
        .flash-message { position: fixed; top: 20px; right: 20px; z-index: 1000; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-success flash-message alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['flash_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h1 class="h4 mb-0"><?= isset($editing) ? 'Edit Post' : 'Add New Post' ?></h1>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <input type="hidden" name="id" value="<?= $editing['id'] ?? '' ?>">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($editing['title'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image_url" class="form-label">Featured Image URL</label>
                        <input type="url" class="form-control" id="image_url" name="image_url" 
                               value="<?= htmlspecialchars($editing['featured_image'] ?? '') ?>"
                               placeholder="https://example.com/image.jpg">
                        <?php if (isset($editing) && !empty($editing['featured_image'])): ?>
                            <img src="<?= htmlspecialchars($editing['featured_image']) ?>" 
                                 class="featured-image-preview d-block mt-2"
                                 onerror="this.style.display='none'">
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea id="content" class="form-control" name="content" rows="10" required><?= htmlspecialchars($editing['content'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">Save Post</button>
                        <?php if (isset($editing)): ?>
                            <a href="posts.php" class="btn btn-outline-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow mt-5">
            <div class="card-header bg-primary text-white">
                <h2 class="h4 mb-0">All Posts</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($article = $articles->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($article['title']) ?></td>
                                <td><?= date('M j, Y', strtotime($article['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="posts.php?edit=<?= $article['id'] ?>" class="btn btn-outline-primary">Edit</a>
                                        <a href="posts.php?delete=<?= $article['id'] ?>" class="btn btn-outline-danger" 
                                           onclick="return confirm('Are you sure?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>