<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: venues.php");
    exit();
}

$venue_id = (int)($_POST['venue_id'] ?? 0);
$booking_date = $_POST['booking_date'] ?? '';
$slots = (int)($_POST['slots'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($venue_id <= 0 || empty($booking_date) || $slots <= 0) {
    $_SESSION['error'] = "Invalid booking data";
    header("Location: venues.php");
    exit();
}

$today = date('Y-m-d');
if ($booking_date < $today) {
    $_SESSION['error'] = "Booking date must be in the future";
    header("Location: venues.php");
    exit();
}

$conn->begin_transaction();

try {
    $venue_sql = "SELECT available_slots, price_per_slot FROM venues WHERE id = ? FOR UPDATE";
    $venue_stmt = $conn->prepare($venue_sql);
    $venue_stmt->bind_param("i", $venue_id);
    $venue_stmt->execute();
    $venue_result = $venue_stmt->get_result();
    
    if ($venue_result->num_rows === 0) {
        throw new Exception("Venue not found");
    }
    
    $venue = $venue_result->fetch_assoc();
    
    if ($venue['available_slots'] < $slots) {
        throw new Exception("Not enough slots available");
    }
    
    $total_price = $venue['price_per_slot'] * $slots;
    
    $booking_sql = "INSERT INTO bookings (user_id, venue_id, booking_date, slot, total_price) 
                    VALUES (?, ?, ?, ?, ?)";
    $booking_stmt = $conn->prepare($booking_sql);
    $booking_stmt->bind_param("iisid", $user_id, $venue_id, $booking_date, $slots, $total_price);
    $booking_stmt->execute();
    
    $update_sql = "UPDATE venues SET available_slots = available_slots - ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $slots, $venue_id);
    $update_stmt->execute();
    
    $conn->commit();
    
    $_SESSION['success'] = "Booking successful!";
    header("Location: bookings.php");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
    header("Location: venues.php");
    exit();
}
?>