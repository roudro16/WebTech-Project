<?php
require_once 'includes/check_auth_cookie.php';
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';

// Only allow turf authorities
if (!$_SESSION['is_turf_authority']) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get all bookings for venues managed by this turf authority
$sql = "SELECT b.*, u.username, u.email, v.venue_name, v.city 
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN venues v ON b.venue_id = v.id
        JOIN turf_authorities ta ON v.id = ta.venue_id
        WHERE ta.user_id = $user_id";

// Add filters if they exist
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
$date_from = isset($_GET['date_from']) ? $conn->real_escape_string($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? $conn->real_escape_string($_GET['date_to']) : '';
$venue_filter = isset($_GET['venue_id']) ? (int)$_GET['venue_id'] : 0;

if (!empty($status_filter)) {
    $sql .= " AND b.status = '$status_filter'";
}

if (!empty($date_from)) {
    $sql .= " AND b.booking_date >= '$date_from'";
}

if (!empty($date_to)) {
    $sql .= " AND b.booking_date <= '$date_to'";
}

if ($venue_filter > 0) {
    $sql .= " AND v.id = $venue_filter";
}

$sql .= " ORDER BY b.booking_date DESC, b.created_at DESC";

$bookings = $conn->query($sql);

// Get venues managed by this turf authority for filter dropdown
$managed_venues = $conn->query("
    SELECT v.id, v.venue_name 
    FROM venues v
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
    <title>Manage Bookings | Turf Manager</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .booking-filters {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            align-items: flex-end;
        }
        
        .filter-form .form-group {
            margin-bottom: 0;
        }
        
        .filter-actions {
            display: flex;
            gap: 10px;
            align-self: flex-end;
        }
        
        .user-info {
            line-height: 1.4;
        }
        
        .user-name {
            font-weight: bold;
        }
        
        .user-email {
            font-size: 13px;
            color: #666;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .actions {
            white-space: nowrap;
        }
        
        @media (max-width: 768px) {
            .filter-form {
                grid-template-columns: 1fr;
            }
            
            .filter-actions {
                grid-column: 1;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Manage Bookings</h1>
    </header>
    
    <nav class="nav">
        <a href="turf_dashboard.php">Dashboard</a>
        <a href="turf_bookings.php" class="active">Bookings</a>
                <a href="turf_venues.php" class="active">My Venues</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </nav>
    
    <main class="container">
        <section class="booking-filters">
            <h2>Filters</h2>
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="venue_id">Venue</label>
                    <select id="venue_id" name="venue_id" class="form-control">
                        <option value="0">All Venues</option>
                        <?php while ($venue = $managed_venues->fetch_assoc()): ?>
                            <option value="<?= $venue['id'] ?>" <?= $venue_filter == $venue['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($venue['venue_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date_from">From Date</label>
                    <input type="date" id="date_from" name="date_from" value="<?= $date_from ?>" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="date_to">To Date</label>
                    <input type="date" id="date_to" name="date_to" value="<?= $date_to ?>" class="form-control">
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn">Apply Filters</button>
                    <a href="turf_bookings.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </section>
        
        <section class="bookings-list">
            <div class="table-responsive">
                <?php if ($bookings->num_rows > 0): ?>
                    <table class="bookings-table">
                        <thead>
                            <tr>
                                <th>Venue</th>
                                <th>User</th>
                                <th>Date</th>
                                <th class="text-center">Slots</th>
                                <th class="text-right">Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($booking = $bookings->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($booking['venue_name']) ?>
                                        <small><?= htmlspecialchars($booking['city']) ?></small>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-name"><?= htmlspecialchars($booking['username']) ?></div>
                                            <div class="user-email"><?= htmlspecialchars($booking['email']) ?></div>
                                        </div>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($booking['booking_date'])) ?></td>
                                    <td class="text-center"><?= $booking['slot'] ?></td>
                                    <td class="text-right">$<?= number_format($booking['total_price'], 2) ?></td>
                                    <td>
                                        <span class="status-badge <?= $booking['status'] ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <?php if ($booking['status'] === 'confirmed'): ?>
                                            <form method="POST" action="turf_cancel_booking.php" class="inline-form">
                                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Are you sure you want to cancel this booking?')">
                                                    Cancel
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">No actions</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert info">
                        No bookings found matching your criteria.
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <script>
        