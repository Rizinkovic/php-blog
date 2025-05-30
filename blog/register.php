<?php
require_once __DIR__ . '/../config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Create user_profiles table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    bio TEXT,
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } else {
        // Check if username/email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username or email already exists';
        } else {
            // Create user
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password_hash);
            
            if ($stmt->execute()) {
                // Create empty profile
                $user_id = $stmt->insert_id;
                $conn->query("INSERT INTO user_profiles (user_id) VALUES ($user_id)");
                
                $success = 'Registration successful! You can now <a href="login.php" style="color: #28a745; font-weight: bold;">login</a>.';
                $_POST = array(); // Clear form
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Register</title>
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
        .success-message {
            color: #28a745;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
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
        .password-strength {
            font-size: clamp(0.75rem, 2.5vw, 0.85rem);
            margin-top: 0.3rem;
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
        <h1>Register</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required oninput="checkPasswordStrength(this.value)">
                <span class="password-toggle" onclick="togglePassword('password')">👁️</span>
                <div class="password-strength" id="strength-text"></div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <span class="password-toggle" onclick="togglePassword('confirm_password')">👁️</span>
            </div>
            
            <button type="submit" class="btn-auth">Register</button>
            
            <div class="form-footer">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </form>
    </div>
    
    
    <script>
        function togglePassword(id) {
            const field = document.getElementById(id);
            field.type = field.type === 'password' ? 'text' : 'password';
        }
        
        function checkPasswordStrength(password) {
            const strengthText = document.getElementById('strength-text');
            
            if (!password) {
                strengthText.textContent = '';
                strengthText.style.color = '';
                return;
            }
            
            if (password.length < 8) {
                strengthText.textContent = 'Weak (min 8 characters)';
                strengthText.style.color = '#dc3545';
            } else if (password.length < 12) {
                strengthText.textContent = 'Medium';
                strengthText.style.color = '#fd7e14';
            } else {
                strengthText.textContent = 'Strong';
                strengthText.style.color = '#28a745';
            }
        }
    </script>
</body>
</html>