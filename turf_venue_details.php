<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';

if (!$_SESSION['is_turf_authority']) {
    header("Location: dashboard.php");
    exit();
}

$venue_id = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

// Verify this user manages this venue
$venue = $conn->query("
    SELECT v.* FROM venues v
    JOIN turf_authorities ta ON v.id = ta.venue_id
    WHERE ta.user_id = $user_id AND v.id = $venue_id
")->fetch_assoc();

if (!$venue) {
    $_SESSION['error'] = "Venue not found or you don't have permission";
    header("Location: turf_venues.php");
    exit();
}

// Handle form submission for updating venue details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $available_slots = (int)($_POST['available_slots'] ?? 0);
    $price_per_slot = (float)($_POST['price_per_slot'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    
    $conn->query("
        UPDATE venues SET 
        available_slots = $available_slots,
        price_per_slot = $price_per_slot,
        status = '$status',
        description = '$description'
        WHERE id = $venue_id
    ");
    
    $_SESSION['success'] = "Venue updated successfully";
    header("Location: turf_venue_details.php?id=$venue_id");
    exit();
}

// Get bookings for this venue
$bookings = $conn->query("
    SELECT b.*, u.username, u.email 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    WHERE b.venue_id = $venue_id
    ORDER BY b.booking_date DESC, b.created_at DESC
");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($venue['venue_name']) ?> | Sports Venue Booking</title>
    <style>
        /* Core Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            line-height: 1.5;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
        }
        
        /* Header */
        .header {
            background: #10192c;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        /* Navigation */
        .nav {
            background: #0b1633;
            padding: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .nav a {
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .nav a:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .nav a.logout-btn {
            margin-left: auto;
            background-color: #dc2626;
        }
        
        .nav a.logout-btn:hover {
            background-color: #b91c1c;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        
        .form-group textarea {
            min-height: 100px;
        }
        
        /* Buttons */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #10192c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background: #0b1633;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 14px;
        }
        
        .btn-danger {
            background: #dc2626;
        }
        
        .btn-danger:hover {
            background: #b91c1c;
        }
        
        /* Alerts */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert.info {
            background: #e0f2fe;
            color: #0369a1;
            border-left: 4px solid #0369a1;
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-badge.confirmed {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-badge.cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
        
        /* Venue Details Specific Styles */
        .venue-details-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }
        
        @media (min-width: 992px) {
            .venue-details-container {
                grid-template-columns: 2fr 1fr;
            }
        }
        
        .venue-info-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .venue-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .venue-title {
            margin: 0;
            color: #10192c;
        }
        
        .venue-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        
        .venue-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .meta-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
        }
        
        .meta-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .meta-value {
            font-size: 18px;
            font-weight: bold;
            color: #10192c;
        }
        
        .booking-list {
            margin-top: 30px;
        }
        
        .booking-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .booking-item {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .booking-user {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .booking-date {
            color: #666;
            font-size: 14px;
        }
        
        /* Tables */
        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }
        
        .bookings-table th,
        .bookings-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .bookings-table th {
            background: #10192c;
            color: white;
        }
        
        .bookings-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .inline-form {
            display: inline-block;
            margin: 0;
        }
    </style>
</head>
<body>
    <header class="header">
        <h1><?= htmlspecialchars($venue['venue_name']) ?></h1>
    </header>
    <nav class="nav">
        <a href="turf_dashboard.php">Home</a>
                <a href="turf_bookings.php" class="active">Bookings</a>
                        <a href="turf_venues.php" class="active">My Venues</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </nav>
    <main class="container">
        <section class="venue-details">
            <h2>Venue Information</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Venue Name</label>
                    <input type="text" value="<?= htmlspecialchars($venue['venue_name']) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" value="<?= htmlspecialchars($venue['city']) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Available Slots</label>
                    <input type="number" name="available_slots" value="<?= $venue['available_slots'] ?>" min="0" required>
                </div>
                <div class="form-group">
                    <label>Price per Slot</label>
                    <input type="number" name="price_per_slot" value="<?= $venue['price_per_slot'] ?>" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="active" <?= $venue['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="maintenance" <?= $venue['status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                        <option value="closed" <?= $venue['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"><?= htmlspecialchars($venue['description']) ?></textarea>
                </div>
                <button type="submit" class="btn">Update Venue</button>
            </form>
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