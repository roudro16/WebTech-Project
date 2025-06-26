<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';

$search = $_GET['search'] ?? '';
$city = $_GET['city'] ?? '';

$sql = "SELECT * FROM venues WHERE available_slots > 0";
$params = [];
$types = '';

if (!empty($search)) {
    $sql .= " AND venue_name LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

if (!empty($city)) {
    $sql .= " AND city = ?";
    $params[] = $city;
    $types .= 's';
}

$sql .= " ORDER BY venue_name";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$venues = $stmt->get_result();

$cities_result = $conn->query("SELECT DISTINCT city FROM venues ORDER BY city");
$cities = $cities_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venues | Sports Venue Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <h1>Available Venues</h1>
    </header>
    
    <nav class="nav">
        <a href="dashboard.php">Home</a>
        <a href="venues.php">Venues</a>
        <a href="bookings.php">My Bookings</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </nav>
    
    <main class="container">
        <section class="search-section">
            <form method="GET" class="search-form">
                <div class="form-group">
                    <input type="text" name="search" placeholder="Search venues..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                
                <div class="form-group">
                    <select name="city">
                        <option value="">All Cities</option>
                        <?php foreach ($cities as $city_option): ?>
                            <option value="<?= htmlspecialchars($city_option['city']) ?>"
                                <?= $city === $city_option['city'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($city_option['city']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn">Search</button>
                <a href="venues.php" class="btn btn-secondary">Reset</a>
            </form>
        </section>
        
        <section class="venues-list">
            <?php if ($venues->num_rows > 0): ?>
                <div class="grid-container">
                    <?php while ($venue = $venues->fetch_assoc()): ?>
                        <div class="venue-card">
                            <div class="venue-image">
                                <!-- Placeholder for venue image -->
                                <div class="image-placeholder"></div>
                            </div>
                            
                            <div class="venue-details">
                                <h3><?= htmlspecialchars($venue['venue_name']) ?></h3>
                                <p class="location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($venue['city']) ?>
                                </p>
                                
                                <p class="description">
                                    <?= htmlspecialchars($venue['description'] ?? 'No description available') ?>
                                </p>
                                
                                <div class="venue-meta">
                                    <div class="meta-item">
                                        <span>Available Slots:</span>
                                        <strong><?= $venue['available_slots'] ?></strong>
                                    </div>
                                    
                                    <div class="meta-item">
                                        <span>Price per Slot:</span>
                                        <strong>tk<?= number_format($venue['price_per_slot'], 2) ?></strong>
                                    </div>
                                </div>
                                
                                <div class="booking-form">
                                    <form action="book_venue.php" method="POST">
                                        <input type="hidden" name="venue_id" value="<?= $venue['id'] ?>">
                                        
                                        <div class="form-group">
                                            <label for="date-<?= $venue['id'] ?>">Date:</label>
                                            <input type="date" id="date-<?= $venue['id'] ?>" 
                                                   name="booking_date" required
                                                   min="<?= date('Y-m-d') ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="slots-<?= $venue['id'] ?>">Slots:</label>
                                            <input type="number" id="slots-<?= $venue['id'] ?>" 
                                                   name="slots" min="1" 
                                                   max="<?= $venue['available_slots'] ?>" 
                                                   value="1" required>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-block">Book Now</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert info">
                    No venues found matching your criteria.
                </div>
            <?php endif; ?>
        </section>
    </main>
    
    
</body>
</html>