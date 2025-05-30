<?php
require_once __DIR__ . '/../config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: ../index.php");
                exit();
            }
        }
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f4f7fa;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }
        .auth-container {
            max-width: 32rem;
            width: 100%;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0 1.25rem rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
            font-size: clamp(1.5rem, 5vw, 1.8rem);
        }
        .form-group {
            margin-bottom: 1.25rem;
            position: relative;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
            font-size: clamp(0.9rem, 3vw, 1rem);
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 0.25rem;
            font-size: clamp(0.9rem, 3vw, 1rem);
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-group input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.1);
        }
        .btn-auth {
            width: 100%;
            padding: 0.75rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 0.25rem;
            font-size: clamp(0.9rem, 3vw, 1rem);
            cursor: pointer;
            transition: background-color 0.3s;
            touch-action: manipulation;
        }
        .btn-auth:hover {
            background-color: #0069d9;
        }
        .form-footer {
            margin-top: 1.25rem;
            text-align: center;
            color: #666;
            font-size: clamp(0.85rem, 2.5vw, 0.9rem);
        }
        .form-footer a {
            color: #007bff;
            text-decoration: none;
        }
        .form-footer a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: #dc3545;
            font-weight: bold;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 0.75rem;
            border-radius: 0.25rem;
            margin-bottom: 1.25rem;
            font-size: clamp(0.85rem, 2.5vw, 0.9rem);
        }
        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 2.5rem;
            cursor: pointer;
            font-size: clamp(0.9rem, 3vw, 1rem);
            user-select: none;
        }
        @media (max-width: 36rem) {
            .auth-container {
                margin: 1rem;
                padding: 1.5rem;
            }
            .form-group {
                margin-bottom: 1rem;
            }
            .btn-auth {
                padding: 0.8rem;
            }
            .password-toggle {
                top: 2.3rem;
            }
        }
        @media (max-width: 24rem) {
            h1 {
                font-size: clamp(1.2rem, 4vw, 1.5rem);
            }
            .form-group label, .form-group input, .btn-auth {
                font-size: clamp(0.8rem, 2.8vw, 0.9rem);
            }
            .password-toggle {
                top: 2.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1>Login</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <span class="password-toggle" onclick="togglePassword('password')">👁️</span>
            </div>
            
            <button type="submit" class="btn-auth">Login</button>
            
            <div class="form-footer">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </form>
    </div>
    
    <script>
        function togglePassword(id) {
            const field = document.getElementById(id);
            field.type = field.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>