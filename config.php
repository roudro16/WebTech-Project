<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sports_booking_db');

// Create database connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper functions
function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}

function isTournamentManager() {
    return isset($_SESSION['is_tournament_manager']) && $_SESSION['is_tournament_manager'];
}

function isTurfAuthority($userId = null, $venueId = null) {
    global $pdo;
    
    if (!$userId) {
        $userId = $_SESSION['user_id'] ?? 0;
    }
    
    if ($venueId) {
        // Check if user is authority for specific venue
        $stmt = $pdo->prepare("SELECT 1 FROM turf_authorities WHERE user_id = ? AND venue_id = ?");
        $stmt->execute([$userId, $venueId]);
        return $stmt->fetchColumn();
    } else {
        // Check if user has any turf authority role
        return isset($_SESSION['is_turf_authority']) && $_SESSION['is_turf_authority'];
    }
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function displayError($message) {
    $_SESSION['error'] = $message;
}

function displaySuccess($message) {
    $_SESSION['success'] = $message;
}

function getVenueStatusBadge($status) {
    $badges = [
        'active' => '<span class="status-badge active">Active</span>',
        'maintenance' => '<span class="status-badge maintenance">Maintenance</span>',
        'closed' => '<span class="status-badge closed">Closed</span>'
    ];
    return $badges[$status] ?? '';
}

function getBookingStatusBadge($status) {
    $badges = [
        'confirmed' => '<span class="status-badge confirmed">Confirmed</span>',
        'cancelled' => '<span class="status-badge cancelled">Cancelled</span>',
        'completed' => '<span class="status-badge completed">Completed</span>',
        'pending' => '<span class="status-badge pending">Pending Approval</span>'
    ];
    return $badges[$status] ?? '';
}
?>