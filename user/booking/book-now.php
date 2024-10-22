<?php
// Include your database connection file
include '../../dbConnection/dbConnection.php';

// Check if 'id' is passed in the URL and is valid
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $jeepneyId = $_GET['id'];

    // Prepare the SQL statement to fetch jeepney and driver details
    $stmt = $conn->prepare(
        "SELECT j.jeepneyID, j.driverID, j.plateNumber, j.capacity, j.occupied, 
                j.route, j.type, j.departure_time, j.jeep_image, 
                d.firstName, d.lastName 
         FROM jeepney j
         INNER JOIN driver d ON j.driverID = d.driverID
         WHERE j.jeepneyID = ?"
    );

    // Bind the jeepney ID parameter
    $stmt->bind_param("i", $jeepneyId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a matching record was found
    if ($result->num_rows > 0) {
        $jeepney = $result->fetch_assoc();  // Fetch the result
        $driverName = $jeepney['firstName'] . ' ' . $jeepney['lastName']; // Combine driver name
    } else {
        echo '<p>No details found for this jeepney.</p>';
        exit();  // Stop further processing if no data found
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo '<p>Invalid Jeepney ID.</p>';
    exit();  // Stop processing if ID is not valid
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeepney Booking</title>
    <link rel="stylesheet" href="book-now.css">
</head>
<body>
<div class="booking-container">
    <a href="../menu/menu.php" class="back-button">
        <img src="../../images/backarrow.png" alt="Back" width="30" height="30">
    </a>
    <div class="content">
        <div class="jeepney-info">
            <div class="jeepney-container">
                <!-- Display the image dynamically using serve_image.php -->
                <img src="../menu/serve_image.php?id=<?= $jeepney['jeepneyID']; ?>" 
                     alt="Jeepney Image" class="jeepney-img">
                <div class="overlay"></div>

                <!-- Driver Info Section -->
                <div class="driver-info">
                    <div class="driver-photo">
                        <img src="../../images/driver.png" alt="Driver" class="driver-avatar">
                    </div>
                    <div class="driver-label">Driver:</div>
                    <div class="driver-name"><?= htmlspecialchars($driverName); ?></div>
                </div>
            </div>
        </div>

        <!-- Jeepney Details Section -->
        <div class="details">
            <p><strong>Jeep Plate Number:</strong> 
               <span class="highlight"><?= htmlspecialchars($jeepney['plateNumber']); ?></span>
            </p>
            <p><strong>Time of Departure:</strong> 
               <span class="highlight"><?= htmlspecialchars($jeepney['departure_time']); ?></span>
            </p>
            <p><strong>Seat Capacity:</strong> 
               <span class="highlight"><?= htmlspecialchars($jeepney['capacity']); ?></span>
            </p>
            <p><strong>Seats Available:</strong> 
               <span class="highlight"><?= ($jeepney['capacity'] - $jeepney['occupied']); ?></span>
            </p>
            <p><strong>Vehicle Type:</strong> 
               <span class="highlight"><?= htmlspecialchars($jeepney['type']); ?></span>
            </p>

            <!-- Payment Method Section -->
            <div class="payment-method">
                <label for="payment-method">SELECT PAYMENT METHOD</label>
                <div class="dropdown-wrapper">
                    <select id="payment-method" name="payment_method" required>
                        <option value="">Select payment method...</option>
                        <option value="cash">Cash</option>
                        <option value="gcash">GCash</option>
                    </select>
                    <span class="arrow-icon">&#x25BC;</span>
                </div>
            </div>

            <!-- Booking Confirmation Form -->
            <form action="confirm_booking.php" method="POST">
                <input type="hidden" name="jeepneyID" value="<?= $jeepney['jeepneyID']; ?>">
                <button type="submit" class="book-btn">Confirm Booking</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
