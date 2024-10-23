<?php
include '../../dbConnection/dbConnection.php'; 

// Fetch booking data from the database
$sql = "SELECT bookingID, userID, jeepneyID, status FROM booking WHERE userID = ?"; 
$stmt = $conn->prepare($sql);
$userId = 1; // Replace with actual user ID if needed
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();


if ($result->num_rows > 0) {
    $bookings = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $bookings = []; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History</title>
    <link rel="stylesheet" href="booking-history.css">
</head>
<body>

<div class="container">
    <header class="header">
        <div class="back-button">
            <a href="../menu/menu.php">
                <img src="../../images/backarrow.png" alt="Back" id="back-btn">
            </a>
        </div>
        <div class="header-title">
            <h1>History</h1>
        </div>
    </header>

    <div class="booking-section">
        <h2>Upcoming</h2>
        
        <?php if (empty($bookings)): ?>
            <p>No bookings found.</p>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="image-container">
                        <div class="fill-box">
                            <img src="../../images/jeep1.jpg" alt="Booking Image">
                        </div>
                    </div>
                    <div class="booking-details">
                        <p><strong>Booking ID:</strong> <span class="highlight"> <?php echo htmlspecialchars($booking['bookingID']); ?></span></p>
                        <p><strong>User ID:</strong> <span class="highlight"> <?php echo htmlspecialchars($booking['userID']); ?></span></p>
                        <p><strong>Jeepney ID:</strong> <span class="highlight"> <?php echo htmlspecialchars($booking['jeepneyID']); ?></span></p>
                        <p><strong>Status:</strong> <span class="highlight"> <?php echo htmlspecialchars($booking['status']); ?></span></p>
                    </div>
                    <a href="../cancelbooking/cancel-booking.php" class="view-more">View More</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="booking-history.js"></script>
</body>
</html>
