<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Sports venue Booking System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .project-name {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="project-name">Sports venue Booking System</div>
        <div class="form-container">
            <h2>Create Account</h2>
            
            <?php if ($error): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form action="signup_process.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" 
                           value="<?= htmlspecialchars($form_data['username'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" 
                           value="<?= htmlspecialchars($form_data['city'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country" 
                           value="<?= htmlspecialchars($form_data['country'] ?? '') ?>">
                </div>
                
                <button type="submit" class="btn">Sign Up</button>
            </form>
            
            <div class="form-footer">
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>
</body>
</html>