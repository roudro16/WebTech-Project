<?php
session_start();
require_once 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method";
    header("Location: login.php");
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']); 

if (empty($username) || empty($password)) {
    $_SESSION['error'] = "All fields are required";
    header("Location: login.php");
    exit();
}

$is_email = filter_var($username, FILTER_VALIDATE_EMAIL);
$field = $is_email ? 'email' : 'username';

$sql = "SELECT id, username, password, is_admin, is_turf_authority FROM users WHERE $field = ? AND password = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $_SESSION['error'] = "Database error. Please try again later.";
    header("Location: login.php");
    exit();
}

$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Invalid credentials";
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['is_admin'] = $user['is_admin'];
$_SESSION['is_turf_authority'] = $user['is_turf_authority'];

session_regenerate_id(true);

if ($remember) {
    $cookie_name = "remember_token";
    $cookie_value = bin2hex(random_bytes(16)); 
    $expiry = time() + 60 * 60 * 24 * 30; 
    
    $token_sql = "INSERT INTO auth_tokens (user_id, token, expires_at) VALUES (?, ?, ?)";
    $token_stmt = $conn->prepare($token_sql);
    $expires_at = date('Y-m-d H:i:s', $expiry);
    $token_stmt->bind_param("iss", $user['id'], $cookie_value, $expires_at);
    $token_stmt->execute();
    
    setcookie($cookie_name, $cookie_value, $expiry, "/", "", false, true);
}

// Determine redirect based on user type
if ($user['is_admin']) {
    $redirect = 'admin/dashboard.php';
} elseif ($user['is_turf_authority']) {
    $redirect = 'turf_dashboard.php';
} else {
    $redirect = 'dashboard.php';
}

header("Location: $redirect");
exit();
?>