<?php
session_start();
require_once 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method";
    header("Location: signup.php");
    exit();
}

if ($conn->connect_error) {
    $_SESSION['error'] = "Database connection failed. Please try again later.";
    header("Location: signup.php");
    exit();
}

$username = trim($conn->real_escape_string($_POST['username'] ?? ''));
$email = trim($conn->real_escape_string($_POST['email'] ?? ''));
$password = $conn->real_escape_string($_POST['password'] ?? '');
$confirm_password = $_POST['confirm_password'] ?? '';
$city = trim($conn->real_escape_string($_POST['city'] ?? ''));
$country = trim($conn->real_escape_string($_POST['country'] ?? ''));

// Basic validation
if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
    $_SESSION['error'] = "All required fields must be filled";
    $_SESSION['form_data'] = $_POST;
    header("Location: signup.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format";
    $_SESSION['form_data'] = $_POST;
    header("Location: signup.php");
    exit();
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = "Passwords do not match";
    $_SESSION['form_data'] = $_POST;
    header("Location: signup.php");
    exit();
}

if (strlen($password) < 8) {
    $_SESSION['error'] = "Password must be at least 8 characters";
    $_SESSION['form_data'] = $_POST;
    header("Location: signup.php");
    exit();
}

$check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    $_SESSION['error'] = "Database error. Please try again later.";
    header("Location: signup.php");
    exit();
}

$check_stmt->bind_param("ss", $username, $email);
if (!$check_stmt->execute()) {
    $_SESSION['error'] = "Database error. Please try again later.";
    header("Location: signup.php");
    exit();
}

$check_result = $check_stmt->get_result();
if ($check_result->num_rows > 0) {
    $_SESSION['error'] = "Username or email already exists";
    $_SESSION['form_data'] = $_POST;
    header("Location: signup.php");
    exit();
}

$insert_sql = "INSERT INTO users (username, email, password, city, country) VALUES (?, ?, ?, ?, ?)";
$insert_stmt = $conn->prepare($insert_sql);

if (!$insert_stmt) {
    $_SESSION['error'] = "Database error. Please try again later.";
    header("Location: signup.php");
    exit();
}

$insert_stmt->bind_param("sssss", $username, $email, $password, $city, $country);
if (!$insert_stmt->execute()) {
    $_SESSION['error'] = "Error creating account. Please try again.";
    header("Location: signup.php");
    exit();
}

$_SESSION['user_id'] = $conn->insert_id;
$_SESSION['username'] = $username;
$_SESSION['success'] = "Account created successfully!";
header("Location: login.php");
exit();
?>