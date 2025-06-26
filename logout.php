<?php
session_start();
require_once 'includes/db_connection.php';

if (!empty($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    $sql = "DELETE FROM auth_tokens WHERE token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    
    setcookie(
        "remember_token",
        "",
        [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]
    );
}

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

header("Location: index.php");
exit();
?>