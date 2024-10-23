<?php
session_start(); // Assuming session-based login
include '../../dbConnection/dbConnection.php'; // Include the database connection

// Fetch booking and driver details from the database
$sql = "SELECT driverID, plateNumber, departure_time, capacity, occupied, type FROM booking,jeepney  WHERE bookingID = 1"; // Example bookingID
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $driverID = $row['driverID'];
    $plateNumber = $row['plateNumber'];
    $departureTime = $row['departureTime'];
    $capacity = $row['seatCapacity'];
    $occupied = $row['seatsAvailable'];
    $type = $row['vehicleType'];
} else {
    echo "No booking details found";
    exit();
}

// Handle form submission for payment method selection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paymentMethod = $_POST['paymentMethod'];
    if ($paymentMethod) {
        echo "<script>alert('Payment method selected: $paymentMethod');</script>";
        // Here, you can add a database update to save the selected payment method
    } else {
        echo "<script>alert('Please select a payment method');</script>";
    }
}

// Create all the content variables at the top for use in the HTML below
$driverDetails = "
    <p class='driver-name'>Driver: $driverID</p>
    <p>Jeep Plate Number: <strong>$plateNumber</strong></p>
    <p>Time of Departure: <strong>$departureTime</strong></p>
    <p>Seat Capacity: $capacity</p>
    <p>Seats Occupied: $occupied</p>
    <p>Vehicle Type: $type</p>
";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="payment.css">
</head>
<body>

<div class="payment-container">
    <div class="jeep-info">
        <img src="image.png" alt="Jeep Image" class="jeep-image">
        <div class="driver-info">
            <?php echo $driverDetails; ?>
        </div>
    </div>

    <div class="payment-method">
        <h2>Select Payment Method</h2>
        <form method="POST" action="payment.php">
            <div class="dropdown">
                <button id="dropdownBtn" class="dropdown-btn">Select payment method...</button>
                <div id="dropdownContent" class="dropdown-content">
                    <label>
                        <input type="radio" name="paymentMethod" value="Cash">
                        <img src="cash-icon.png" alt="Cash"> Cash
                    </label>
                    <label>
                        <input type="radio" name="paymentMethod" value="GCash">
                        <img src="gcash-icon.png" alt="GCash"> GCash
                    </label>
                </div>
            </div>
            <button type="submit" class="book-btn">Book</button>
        </form>
    </div>
</div>

<script src="payment.js"></script>
</body>
</html>