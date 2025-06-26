<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';

// Check if user is a turf authority
if (!$_SESSION['is_turf_authority']) {
    header("Location: dashboard.php");
    exit();
}

// Get venues this user manages
$user_id = $_SESSION['user_id'];
$venues = $conn->query("
    SELECT v.* FROM venues v
    JOIN turf_authorities ta ON v.id = ta.venue_id
    WHERE ta.user_id = $user_id
    ORDER BY v.venue_name
");

// Get bookings for these venues
$bookings = $conn->query("
    SELECT b.*, u.username, u.email 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN turf_authorities ta ON b.venue_id = ta.venue_id
    WHERE ta.user_id = $user_id
    ORDER BY b.booking_date DESC, b.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turf Manager Dashboard | Sports Venue Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<style>
/* Turf Dashboard Specific Styles */
.turf-dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    text-align: center;
}

.stat-value {
    font-size: 28px;
    font-weight: bold;
    color: var(--blue);
    margin: 10px 0;
}

.stat-label {
    color: #666;
    font-size: 14px;
}

.recent-bookings {
    margin-top: 40px;
}

.booking-card {
    background: white;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 6px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    transition: transform 0.3s;
}

.booking-card:hover {
    transform: translateY(-3px);
}

.booking-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.booking-venue {
    font-weight: bold;
    color: var(--blue);
}

.booking-date {
    color: #666;
}

.booking-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
    font-size: 14px;
}

.detail-item {
    color: #555;
}

.detail-item strong {
    color: #333;
}
/* My Venues Section Styles */
.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.venue-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}



.venue-card-content {
    padding: 20px;
}

.venue-card h3 {
    margin-top: 0;
    color: #2c3e50;
    font-size: 1.2rem;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
    margin-bottom: 15px;
}

.venue-details {
    margin-bottom: 20px;
}

.venue-details p {
    margin: 8px 0;
    color: #555;
    font-size: 0.9rem;
}

.venue-details i {
    margin-right: 8px;
    color: var(--blue);
    width: 16px;
    text-align: center;
}

.manage-btn {
    display: block;
    text-align: center;
    background-color: var(--blue);
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.3s;
}

</style>
<body>
    <header class="header">
        <h1>Turf Manager Dashboard</h1>
    </header>
    <nav class="nav">
        <a href="turf_dashboard.php">Home</a>
                <a href="turf_bookings.php" class="active">Bookings</a>
                        <a href="turf_venues.php" class="active">My Venues</a>

        <a href="logout.php" class="logout-btn">Logout</a>
    </nav>
    <main class="container">
        <section class="dashboard-section">
    <h2>My Venues</h2>
    <?php if ($venues->num_rows > 0): ?>
        <div class="grid-container">
            <?php while ($venue = $venues->fetch_assoc()): ?>
                <div class="venue-card">
                    <div class="venue-card-content">
                        <h3><?= htmlspecialchars($venue['venue_name']) ?></h3>
                        <div class="venue-details">
                            <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($venue['city']) ?></p>
                            <p><i class="fas fa-calendar-alt"></i> Available Slots: <?= $venue['available_slots'] ?></p>
                            <p><i class="fas fa-tag"></i> Price: tk <?= number_format($venue['price_per_slot'], 2) ?></p>
                        </div>
                        <a href="turf_venue_details.php?id=<?= $venue['id'] ?>" class="btn manage-btn">Manage Venue</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert info">
            You are not assigned to manage any venues yet.
        </div>
    <?php endif; ?>
</section>

        <section class="venue-bookings">
            <h2>Bookings for This Venue</h2>
            <?php if ($bookings->num_rows > 0): ?>
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Date</th>
                            <th>Slots</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = $bookings->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($booking['username']) ?>
                                    <small><?= htmlspecialchars($booking['email']) ?></small>
                                </td>
                                <td><?= date('M j, Y', strtotime($booking['booking_date'])) ?></td>
                                <td><?= $booking['slot'] ?></td>
                                <td>$<?= number_format($booking['total_price'], 2) ?></td>
                                <td>
                                    <span class="status-badge <?= $booking['status'] ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($booking['status'] === 'confirmed'): ?>
                                        <form method="POST" action="turf_cancel_booking.php" class="inline-form">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert info">
                    No bookings found for this venue.
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>