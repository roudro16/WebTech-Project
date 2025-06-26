<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';

$user_id = $_SESSION['user_id'];

$user_sql = "SELECT username, email, city, country FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    
    $update_sql = "UPDATE users SET city = ?, country = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssi", $city, $country, $user_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully";
        header("Location: profile.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating profile";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "All password fields are required";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error'] = "New passwords do not match";
    } elseif (strlen($new_password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters";
    } else {
        $check_sql = "SELECT password FROM users WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $db_user = $check_result->fetch_assoc();
        
        if ($current_password !== $db_user['password']) {
            $_SESSION['error'] = "Current password is incorrect";
        } else {
           
            $update_sql = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $new_password, $user_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['success'] = "Password changed successfully";
                header("Location: profile.php");
                exit();
            } else {
                $_SESSION['error'] = "Error changing password";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Sports Venue Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <h1>My Profile</h1>
    </header>
    
    <nav class="nav">
        <a href="dashboard.php">Home</a>
        <a href="venues.php">Venues</a>
        <a href="bookings.php">My Bookings</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </nav>
    
    <main class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-grid">
            <section class="profile-section">
                <h2>Profile Information</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" 
                               value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country" 
                               value="<?= htmlspecialchars($user['country'] ?? '') ?>">
                    </div>
                    
                    <button type="submit" class="btn">Update Profile</button>
                </form>
            </section>
            
            <section class="password-section">
                <h2>Change Password</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-danger">
                        Change Password
                    </button>
                </form>
            </section>
        </div>
    </main>
    
    
</body>
</html>