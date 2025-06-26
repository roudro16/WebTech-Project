<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/check_auth_cookie.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sports venue Booking System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .project-name {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            color:rgb(9, 66, 122);
            margin: 20px 0;
        }
        
        .form-group.remember-me {
            margin: 1rem 0;
            display: flex;
            align-items: center;
        }
        
        .form-group.remember-me input[type="checkbox"] {
            width: 16px;
            height: 18px;
            margin-right: 8px;
            cursor: pointer;
        }
        
        .form-group.remember-me label {
            cursor: pointer;
            user-select: none;
            color:rgb(78, 7, 34);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="project-name">Sports venue Booking System</div>
        <div class="form-container">
            <h2>Login</h2>
            
            <?php if ($error): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form action="login_process.php" method="POST">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group remember-me">
                    <input type="checkbox" id="remember" name="remember" value="1">
                    <label for="remember">Remember Me</label>
                </div>
                
                <button type="submit" class="btn">Login</button>
            </form>
            
            <div class="form-footer">
                <p>Don't have an account? <a href="signup.php">Sign up</a></p>
            </div>
        </div>
    </div>
</body>
</html>