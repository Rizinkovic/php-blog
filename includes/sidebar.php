<div class="sidebar" style="
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
    margin-bottom: 20px;
    /* Desktop styles */
    width: 300px;
    float: right;
    margin-left: 20px;
    /* Mobile responsiveness */
    @media (max-width: 768px) {
        width: 100%;
        float: none;
        margin-left: 0;
        margin-bottom: 30px;
        padding: 15px;
        box-sizing: border-box;
    }
">
    <h3 style="
        margin-top: 0;
        color: #333;
        font-size: 1.3em;
        border-bottom: 1px solid #ddd;
        padding-bottom: 8px;
    ">About</h3>
    <p style="
        line-height: 1.6;
        color: #555;
        margin-bottom: 25px;
    ">Welcome to my blog where I'll be sharing my coding journey, stay tuned.</p>
    
    <h3 style="
        color: #333;
        font-size: 1.3em;
        border-bottom: 1px solid #ddd;
        padding-bottom: 8px;
    ">Recent Posts</h3>
    <?php
    $recent_query = "SELECT id, title FROM articles ORDER BY created_at DESC LIMIT 5";
    $recent_result = $conn->query($recent_query);
    if ($recent_result->num_rows > 0) {
        echo '<ul style="
            list-style: none;
            padding-left: 0;
            margin-top: 15px;
        ">';
        while($row = $recent_result->fetch_assoc()) {
            echo '<li style="
                margin-bottom: 10px;
                padding-bottom: 10px;
                border-bottom: 1px solid #eee;
            "><a href="?post='.$row['id'].'" style="
                color: #007bff;
                text-decoration: none;
                display: block;
                padding: 5px 0;
                transition: color 0.3s;
            ">'.htmlspecialchars($row['title']).'</a></li>';
        }
        echo '</ul>';
    } else {
        echo '<p style="
            color: #666;
            font-style: italic;
        ">No posts yet</p>';
    }
    ?>
</div>