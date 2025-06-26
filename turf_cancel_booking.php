<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';

if (!$_SESSION['is_turf_authority']) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: turf_dashboard.php");
    exit();
}

$booking_id = (int)($_POST['booking_id'] ?? 0);
$user_id = $_SESSION['user_id'];

// Verify this booking is for a venue this user manages
$booking = $conn->query("
    SELECT b.* FROM bookings b
    JOIN turf_authorities ta ON b.venue_id = ta.venue_id
    WHERE ta.user_id = $user_id AND b.id = $booking_id AND b.status = 'confirmed'
")->fetch_assoc();

if (!$booking) {
    $_SESSION['error'] = "Booking not found or already cancelled";
    header("Location: turf_dashboard.php");
    exit();
}

$conn->begin_transaction();
try {
    // Update booking status
    $conn->query("UPDATE bookings SET status = 'cancelled' WHERE id = $booking_id");
    
    // Return slots to venue
    $conn->query("
        UPDATE venues 
        SET available_slots = available_slots + {$booking['slot']} 
        WHERE id = {$booking['venue_id']}
    ");
    
    $conn->commit();
    $_SESSION['success'] = "Booking #$booking_id cancelled successfully";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error cancelling booking: " . $e->getMessage();
}

header("Location: turf_bookings.php");
exit();
?>