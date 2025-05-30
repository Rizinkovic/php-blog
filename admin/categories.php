<?php
require_once '../config.php';
require_once '../includes/auth.php';


if(!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Handle category actions
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM categories WHERE id = $id");
    header("Location: categories.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = htmlspecialchars(trim($_POST['name']));
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
    
    if($id > 0) {
        // Update category
        $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $slug, $id);
    } else {
        // Create category
        $stmt = $conn->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $slug);
    }
    
    $stmt->execute();
    header("Location: categories.php");
    exit();
}

// Get all categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Manage Categories</h1>
        
        <div class="category-form">
            <h2><?php echo isset($_GET['edit']) ? 'Edit Category' : 'Add New Category'; ?></h2>
            <?php
            $editing = null;
            if(isset($_GET['edit'])) {
                $id = intval($_GET['edit']);
                $editing = $conn->query("SELECT * FROM categories WHERE id = $id")->fetch_assoc();
            }
            ?>
            <form method="post">
                <input type="hidden" name="id" value="<?php echo $editing ? $editing['id'] : 0; ?>">
                <div class="form-group">
                    <label for="name">Category Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo $editing ? $editing['name'] : ''; ?>" required>
                </div>
                <button type="submit">Save Category</button>
                <?php if($editing): ?>
                    <a href="categories.php" class="button">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="categories-list">
            <h2>All Categories</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($category = $categories->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                        <td><?php echo htmlspecialchars($category['slug']); ?></td>
                        <td class="actions">
                            <a href="categories.php?edit=<?php echo $category['id']; ?>" class="edit">Edit</a>
                            <a href="categories.php?delete=<?php echo $category['id']; ?>" class="delete" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>