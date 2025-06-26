<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';

if (!$_SESSION['is_turf_authority']) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$venues = $conn->query("
    SELECT v.* FROM venues v
    JOIN turf_authorities ta ON v.id = ta.venue_id
    WHERE ta.user_id = $user_id
    ORDER BY v.venue_name
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Venues | Sports Venue Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<style>
/* Turf Venues List - Spacious 2-Column Grid */
.venues-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.search-filter {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

/* Spacious 2-column grid system */
.venues-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr); /* Strict 2 columns */
    gap: 30px; /* Larger gap between items */
    padding: 15px; /* Grid container padding */
}

/* Extra spacious venue cards */
.venue-card {
    background: white;
    border-radius: 10px; /* Slightly rounder corners */
    overflow: hidden;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08); /* Softer shadow */
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 25px; /* Internal padding for content */
}

.venue-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.12);
}

.venue-image {
    height: 200px; /* Taller image area */
    background-color: #f5f5f5; /* Lighter placeholder */
    background-size: cover;
    background-position: center;
    margin: -25px -25px 20px -25px; /* Negative margin to stretch image */
    border-radius: 10px 10px 0 0;
}

.venue-content {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.venue-title {
    margin: 0 0 20px 0; /* More space below title */
    color: var(--blue);
    font-size: 1.3rem;
    font-weight: 600;
}

.venue-location {
    display: flex;
    align-items: center;
    color: #666;
    margin-bottom: 25px; /* More space below location */
    font-size: 15px; /* Slightly larger */
    gap: 10px;
}

.venue-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px; /* More space between stats */
    margin: 25px 0; /* Vertical spacing */
}

.venue-stat {
    text-align: center;
    padding: 15px 10px; /* More padding */
    background: rgba(245, 245, 245, 0.5);
    border-radius: 8px;
    transition: background 0.3s;
}

.venue-stat:hover {
    background: rgba(245, 245, 245, 0.9);
}

.stat-value {
    font-size: 22px; /* Larger value */
    font-weight: bold;
    color: var(--blue);
    margin-bottom: 8px;
}

.stat-label {
    font-size: 14px;
    color: #666;
    letter-spacing: 0.5px;
}

.venue-actions {
    margin-top: 25px; /* Space above buttons */
    padding-top: 20px;
    border-top: 1px solid #eee;
    display: flex;
    gap: 15px; /* Space between buttons */
}

.no-venues {
    text-align: center;
    padding: 60px 20px;
    color: #666;
    grid-column: 1 / -1;
    font-size: 1.1rem;
}

/* Responsive adjustment for smaller screens */
@media (max-width: 768px) {
    .venues-grid {
        grid-template-columns: 1fr; /* Single column on mobile */
        gap: 25px;
    }
    
    .venue-card {
        padding: 20px;
    }
    
    .venue-image {
        height: 180px;
        margin: -20px -20px 15px -20px;
    }
}
</style>
<body>
    <header class="header">
        <h1>My Managed Venues</h1>
    </header>
    <nav class="nav">
        <a href="turf_dashboard.php">Home</a>
        <a href="turf_bookings.php">Bookings</a>
        <a href="turf_venues.php" class="active">My Venues</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </nav>
    <main class="container">
        <?php if ($venues->num_rows > 0): ?>
            <div class="grid-container">
                <?php while ($venue = $venues->fetch_assoc()): ?>
                    <div class="venue-card">
                        <h3><?= htmlspecialchars($venue['venue_name']) ?></h3>
                        <p><?= htmlspecialchars($venue['city']) ?></p>
                        <p>Available Slots: <?= $venue['available_slots'] ?></p>
                        <p>Price: $<?= number_format($venue['price_per_slot'], 2) ?></p>
                        <a href="turf_venue_details.php?id=<?= $venue['id'] ?>" class="btn">Manage</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert info">
                You are not assigned to manage any venues yet.
            </div>
        <?php endif; ?>
    </main>
</body>
</html>