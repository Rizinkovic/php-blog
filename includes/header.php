<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rizinkovic</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://kit.fontawesome.com/af0db50a6f.js" crossorigin="anonymous"></script>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }

        /* Header Styles */
        .site-header {
            background-color: #2c3e50; /* Restored previous background color */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .site-title img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .main-nav ul {
            display: flex;
            list-style: none;
            gap: 20px;
        }

        .main-nav a {
            text-decoration: none;
            color: white;
            font-weight: 500;
            padding: 10px;
            transition: color 0.3s;
        }

        .main-nav a:hover {
            color: #007bff;
        }

        .search-box {
            display: flex;
            align-items: center;
        }

        .search-box input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
            outline: none;
        }

        .search-box button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-left: none;
            background: #007bff;
            color: #fff;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            transition: background 0.3s;
        }

        .search-box button:hover {
            background: #0056b3;
        }

        /* Hamburger Menu */
        .hamburger {
            display: none;
            font-size: 24px;
            cursor: pointer;
            color: #333;
        }

        /* Comment Section Styles */
        .comments-section {
            margin-top: 40px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .comment {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .comment-avatar {
            margin-right: 15px;
        }

        .comment-avatar img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .comment-content {
            flex: 1;
        }

        .comment-author {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .comment-author a {
            color: #333;
            text-decoration: none;
        }

        .comment-text {
            color: #555;
            line-height: 1.5;
        }

        .comment-date {
            color: #999;
            font-size: 0.9em;
            margin-top: 5px;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                align-items: flex-start;
            }

            .site-title {
                text-align: center;
                margin-bottom: 10px;
            }

            .hamburger {
                display: block;
                position: absolute;
                top: 20px;
                right: 15px;
            }

            .main-nav {
                width: 100%;
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease-in-out;
            }

            .main-nav.active {
                max-height: 300px;
            }

            .main-nav ul {
                flex-direction: column;
                align-items: center;
                width: 100%;
            }

            .main-nav li {
                width: 100%;
                text-align: center;
                padding: 10px 0;
                border-top: 1px solid #eee;
            }

            .search-box {
                width: 100%;
                margin-top: 15px;
            }

            .search-box form {
                display: flex;
                width: 100%;
            }

            .search-box input {
                flex: 1;
            }

            .comment {
                flex-direction: column;
            }

            .comment-avatar {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }

        @media (max-width: 480px) {
            .site-title img {
                max-width: 200px;
            }

            .search-box input {
                padding: 6px;
            }

            .search-box button {
                padding: 6px 10px;
            }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <h1 class="site-title"><a href="https://x.com/rizinkovic"><img src="https://images.cooltext.com/5731206.png" alt="Rizinkovic" /></a></h1>
            <span class="hamburger"><i class="fas fa-bars"></i></span>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="aboutus.php#">About</a></li>
                    <li><a href="contact.php#">Contact</a></li>
                    <li><a href="admin/login.php">Admin</a></li>
                </ul>
            </nav>
            <div class="search-box">
                <form action="index.php" method="get">
                    <input type="text" name="search" placeholder="Search posts...">
                    <button type="submit">Search</button>
                </form>
            </div>
        </div>
    </header>
    <div class="container main-content">
        <!-- Your main content goes here -->
    </div>

    <script>
        // Toggle mobile menu
        document.querySelector('.hamburger').addEventListener('click', function() {
            document.querySelector('.main-nav').classList.toggle('active');
        });
    </script>
</body>
</html>