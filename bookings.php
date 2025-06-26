<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = (int)($_POST['booking_id'] ?? 0);
    
    if ($booking_id > 0) {
        $conn->begin_transaction();
        
        try {
            $booking_sql = "SELECT venue_id, slot FROM bookings 
                           WHERE id = ? AND user_id = ? AND status = 'confirmed' FOR UPDATE";
            $booking_stmt = $conn->prepare($booking_sql);
            $booking_stmt->bind_param("ii", $booking_id, $user_id);
            $booking_stmt->execute();
            $booking_result = $booking_stmt->get_result();
            
            if ($booking_result->num_rows === 0) {
                throw new Exception("Booking not found or already cancelled");
            }
            
            $booking = $booking_result->fetch_assoc();
            
            $cancel_sql = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
            $cancel_stmt = $conn->prepare($cancel_sql);
            $cancel_stmt->bind_param("i", $booking_id);
            $cancel_stmt->execute();
            
            $update_sql = "UPDATE venues SET available_slots = available_slots + ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $booking['slot'], $booking['venue_id']);
            $update_stmt->execute();
            
            $conn->commit();
            
            $_SESSION['success'] = "Booking cancelled successfully";
            header("Location: bookings.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
            header("Location: bookings.php");
            exit();
        }
    }
}

$bookings_sql = "SELECT b.id, b.booking_date, b.slot, b.total_price, b.status,
                 v.venue_name, v.city, v.price_per_slot
                 FROM bookings b
                 JOIN venues v ON b.venue_id = v.id
                 WHERE b.user_id = ?
                 ORDER BY b.booking_date DESC, b.created_at DESC";
$bookings_stmt = $conn->prepare($bookings_sql);
$bookings_stmt->bind_param("i", $user_id);
$bookings_stmt->execute();
$bookings = $bookings_stmt->get_result();

$stats_sql = "SELECT 
              COUNT(*) as total_bookings,
              SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as active_bookings,
              SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
              SUM(CASE WHEN status = 'confirmed' THEN total_price ELSE 0 END) as total_spent
              FROM bookings
              WHERE user_id = ?";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | Sports Venue Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <h1>My Bookings</h1>
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
        
        <section class="booking-stats">
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Bookings</h3>
                    <p><?= $stats['total_bookings'] ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Active Bookings</h3>
                    <p><?= $stats['active_bookings'] ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Cancelled Bookings</h3>
                    <p><?= $stats['cancelled_bookings'] ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Total Spent</h3>
                    <p>tk<?= number_format($stats['total_spent'], 2) ?></p>
                </div>
            </div>
        </section>
        
        <section class="bookings-list">
            <h2>Booking History</h2>
            
            <?php if ($bookings->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="bookings-table">
                        <thead>
                            <tr>
                                <th>Venue</th>
                                <th>Date</th>
                                <th>Slots</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th>Status</th>
                              
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($booking = $bookings->fetch_assoc()): ?>
                            <tr id="booking-<?= $booking['id'] ?>">
                                <td>
                                    <?= htmlspecialchars($booking['venue_name']) ?>
                                    <small><?= htmlspecialchars($booking['city']) ?></small>
                                </td>
                                <td><?= date('M j, Y', strtotime($booking['booking_date'])) ?></td>
                                <td><?= $booking['slot'] ?></td>
                                <td>$<?= number_format($booking['price_per_slot'], 2) ?></td>
                                <td>$<?= number_format($booking['total_price'], 2) ?></td>
                                <td>
                                    <span class="status-badge <?= $booking['status'] ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($booking['status'] === 'confirmed'): ?>
                                        <form method="POST" class="inline-form">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <button type="submit" name="cancel_booking" class="btn btn-sm btn-danger">
                                                Cancel
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted"></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert info">
                    You have no bookings yet. <a href="venues.php">Book a venue now!</a>
                </div>
            <?php endif; ?>
        </section>
    </main>
    
    
</body>
</html>