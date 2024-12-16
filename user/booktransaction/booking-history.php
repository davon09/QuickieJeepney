<?php
include '../../dbConnection/dbConnection.php'; 
session_start();

// Ensure user is logged in
if (!isset($_SESSION['userID'])) {
    echo "Please log in to view your booking history.";
    exit();
}

$userId = $_SESSION['userID'];

// Fetch bookings
$sql = "SELECT b.bookingID, b.userID, b.jeepneyID, b.status, j.plateNumber, j.route 
        FROM booking b
        INNER JOIN jeepney j ON b.jeepneyID = j.jeepneyID
        WHERE b.userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->num_rows > 0 ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();
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
                <img src="../../images/backarrow.png" alt="Back">
            </a>
        </div>
        <h1>Booking History</h1>
    </header>

    <div class="booking-section">
        <?php if (empty($bookings)): ?>
            <p>No bookings found.</p>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <p><strong>Booking ID:</strong> <?= htmlspecialchars($booking['bookingID']); ?></p>
                    <p><strong>Jeep Plate Number:</strong> <?= htmlspecialchars($booking['plateNumber']); ?></p>
                    <p><strong>Route:</strong> <?= htmlspecialchars($booking['route']); ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($booking['status']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
