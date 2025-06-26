<?php
require_once 'includes/check_auth_cookie.php';
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sports Venue Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <h1>Welcome, <?= htmlspecialchars($username) ?></h1>
    </header>
    
    <nav class="nav">
        <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">Home</a>
        <a href="venues.php" class="<?= basename($_SERVER['PHP_SELF']) == 'venues.php' ? 'active' : '' ?>">Venues</a>
        <a href="bookings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : '' ?>">My Bookings</a>
        <?php if ($_SESSION['is_turf_authority']): ?>
            <a href="turf_dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'turf_dashboard.php' ? 'active' : '' ?>">Turf Manager</a>
        <?php endif; ?>
        <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">Profile</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </nav>
    
    <main class="container">
        <section class="dashboard-section">
            <h2>Quick Actions</h2>
            <div class="action-cards">
                <div class="card">
                    <h3>Book a Venue</h3>
                    <p>Find and book available sports venues</p>
                    <br>
                    <a href="venues.php" class="btn">Browse Venues</a>
                </div>
                
                <div class="card">
                    <h3>My Bookings</h3>
                    <p>View and manage your bookings</p>
                    <br>
                    <a href="bookings.php" class="btn">View Bookings</a>
                </div>
                
                <div class="card">
                    <h3>My Profile</h3>
                    <p>Update your personal information</p>
                    <br>
                    <a href="profile.php" class="btn">Edit Profile</a>
                </div>
            </div>
        </section>
        
        <section class="recent-bookings">
            <h2>Recent Bookings</h2>
            <?php
            $bookings_sql = "SELECT b.id, v.venue_name, b.booking_date, b.slot 
                            FROM bookings b
                            JOIN venues v ON b.venue_id = v.id
                            WHERE b.user_id = ?
                            ORDER BY b.booking_date DESC
                            LIMIT 3";
            $stmt = $conn->prepare($bookings_sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $bookings = $stmt->get_result();
            
            if ($bookings->num_rows > 0): ?>
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Venue</th>
                            <th>Date</th>
                            <th>Slots</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = $bookings->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['venue_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($booking['booking_date'])) ?></td>
                            <td><?= $booking['slot'] ?></td>
                            <td>
                                <a href="bookings.php#booking-<?= $booking['id'] ?>" class="btn btn-sm">View</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
            <?php else: ?>
                <p>You have no recent bookings. <a href="venues.php">Book a venue now!</a></p>
            <?php endif; ?>
        </section>
    </main>
    
    
</body>
</html>