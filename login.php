<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Sports Venue Booking</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <form class="login-form" action="process.php" method="POST">
            <h2>Login</h2>
            <label>Username: <input type="text" name="username" required></label>
            <label>Password: <input type="password" name="password" required></label>
            <input type="submit" value="Login">
            <button type="button" onclick="window.location.href='index.php'">Cancel</button>
        </form>
    </div>
</body>
</html>