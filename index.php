<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Venue Booking</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/images/sports-bg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        .container {
            max-width: 800px;
            padding: 2rem;
        }
        h1 {
            font-size: 3.5rem; 
            margin-bottom: 1.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        p {
            font-size: 1.3rem; 
            margin-bottom: 3rem; 
        }
        .btn-container {
            display: flex;
            gap: 1rem; 
            justify-content: center;
        }
        .btn {
            display: inline-block;
            padding: 1rem 2.5rem;
            background: #2a5f8a;
            color: white;
            text-decoration: none;
            border-radius: 6px; 
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn:hover {
            background: #1d4b6f;
            transform: translateY(-2px); 
            box-shadow: 0 6px 8px rgba(0,0,0,0.15);
        }
        .btn-signup {
            background: transparent;
            border: 2px solid white;
        }
        .btn-signup:hover {
            background: rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>SPORTS VENUE BOOKING</h1> 
        <p>Reserve sports facilities in just a few clicks</p> 
        <div class="btn-container">
            <a href="login.php" class="btn">Login</a>
            <a href="signup.php" class="btn btn-signup">Sign Up</a> 
        </div>
    </div>
</body>
</html>