<?php
include '../../dbConnection/dbConnection.php';

$sql = "SELECT paymentID, paymentStatus, paymentMethod, amount 
        FROM payment
        ORDER BY paymentID DESC LIMIT 1"; 
$result = $conn->query($sql);

$paymentMethod = '';
$amountPaid = '';

if ($result->num_rows > 0) {
    
    $row = $result->fetch_assoc();
    $paymentMethod = htmlspecialchars($row['paymentMethod']);
    $amountPaid = htmlspecialchars($row['amount']);
} else {
    $paymentMethod = 'N/A'; 
    $amountPaid = 'N/A';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeepney Booking</title>
    <link rel="stylesheet" href="confirm_booking.css">
    <script src="confirm_booking.php"></script>
</head>
<body>
    <header class="header-bar"></header>

    <div class="image-container">
    <img src="../../images/success.png" class="image_check" alt="success">
    <p class="text-success">Booked Successfully!</p>
    <p class="text-below">You have successfully booked at your jeepney. Please proceed arrive at the jeep before its departure.</p>

<div class="text-container">
    <p class="text-info">Mode of Payment: <?php echo $paymentMethod; ?> </p>
    <p class="text-info">Amount Paid: <?php echo $amountPaid; ?> </p>
</div>
<a href="../menu/menu.php" class="button-back">Back to Menu</a>
</body>
</html>