<?php
include '../../dbConnection/dbConnection.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $jeepneyId = $_GET['id'];

    // Fetch jeepney and driver details
    $stmt = $conn->prepare(
        "SELECT j.jeepneyID, j.driverID, j.plateNumber, j.capacity, j.occupied, 
                j.route, j.type, j.departure_time, j.jeep_image, 
                d.firstName, d.lastName 
         FROM jeepney j
         INNER JOIN driver d ON j.driverID = d.driverID
         WHERE j.jeepneyID = ?"
    );

    $stmt->bind_param("i", $jeepneyId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $jeepney = $result->fetch_assoc(); 
        $driverName = $jeepney['firstName'] . ' ' . $jeepney['lastName'];

        // Decode departure_time (assume it's stored as JSON in the database)
        $schedule = json_decode($jeepney['departure_time'], true);
        $departure_options = '';
        foreach ($schedule as $day => $times) {
            foreach ($times as $time) {
                $departure_options .= '<option value="' . htmlspecialchars($time) . '">' . htmlspecialchars($day . ' - ' . $time) . '</option>';
            }
        }
    } else {
        echo '<p>No details found for this jeepney.</p>';
        exit();
    }

    $stmt->close();
} else {
    echo '<p>Invalid Jeepney ID.</p>';
    exit();
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
                <img src="../menu/serve_image.php?id=<?= $jeepney['jeepneyID']; ?>" 
                     alt="Jeepney Image" class="jeepney-img">
                <div class="overlay"></div>

                <div class="driver-info">
                    <div class="driver-photo">
                        <img src="../../images/driver.png" alt="Driver" class="driver-avatar">
                    </div>
                    <div class="driver-label">Driver:</div>
                    <div class="driver-name"><?= htmlspecialchars($driverName); ?></div>
                </div>
            </div>
        </div>

        <div class="details">
            <p><strong>Jeep Plate Number:</strong> 
               <span class="highlight"><?= htmlspecialchars($jeepney['plateNumber']); ?></span>
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

            <form action="confirm_booking.php" method="POST">
                <input type="hidden" name="jeepneyID" value="<?= $jeepney['jeepneyID']; ?>">
                <input type="hidden" name="userID" value="1"> <!-- Replace with session user ID -->

                <div class="payment-method">
                    <label for="departure-time">SELECT TIME OF DEPARTURE</label>
                    <div class="dropdown-wrapper">
                        <select id="payment-method" name="departure_time" required> 
                            <option value="" disabled selected>Select departure time...</option>
                            <?= $departure_options; ?>
                        </select>
                    </div>

                    <label for="payment-method">SELECT PAYMENT METHOD</label>
                    <div class="dropdown-wrapper">
                        <select id="payment-method" name="payment_method" required> 
                            <option value="" disabled selected>Select payment method...</option>
                            <option value="cash">Cash</option>
                            <option value="gcash">GCash</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="book-btn" id="book-btn" disabled>Confirm Booking</button>
            </form>
        </div>
    </div>
</div>

<script>
// Enable booking button only if both dropdowns are selected
let paymentMethodSelect = document.getElementById('payment-method');
let departureTimeSelect = document.getElementById('departure-time');

function validateForm() {
    const paymentMethod = paymentMethodSelect.value;
    const departureTime = departureTimeSelect.value;
    const bookBtn = document.getElementById('book-btn');
    
    bookBtn.disabled = paymentMethod === "" || departureTime === "";
}

paymentMethodSelect.addEventListener('change', validateForm);
departureTimeSelect.addEventListener('change', validateForm);
</script>
</body>
</html>
