<?php
session_start();
include '../../dbConnection/dbConnection.php';

// Check if user is logged in (ensure the userID is in the session)
// if (!isset($_SESSION['userID'])) {
//    header("Location: /QuickieJeepney/index.php"); // Redirect to login page if not logged in
//    exit();
// }

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch payment data
$sqlPayment = "SELECT paymentID, amount, paymentMethod, paymentStatus FROM payment";
$resultPayment = $conn->query($sqlPayment);

if (!$resultPayment) {
    die("Payment Query Failed: " . $conn->error);
}

// Fetch logged-in user's details including the occupation
$userID = $_SESSION['userID'];
$sqlUser = "SELECT firstName, lastName, occupation, email, profile_image FROM user WHERE userID = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $userID);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
if ($resultUser->num_rows > 0) {
    $user = $resultUser->fetch_assoc();
    $fullName = $user['firstName'] . ' ' . $user['lastName'];
    $occupation = $user['occupation'];
    $profileImage = $user['profile_image'];
} else {
    $fullName = "Guest";
    $occupation = "N/A";
    $profileImage = null;
}

$userDetailsHTML = '
    <span class="name">' . $fullName . '</span>
    <br>
    <span class="occupation">' . $occupation . '</span>
    <br>
';

$paymentHTML = '';
if ($resultPayment->num_rows > 0) {
    while ($paymentRow = $resultPayment->fetch_assoc()) {
        $userID = $paymentRow['paymentID'];
        $fullName = isset($userData[$userID])
            ? $userData[$userID]['firstName'] . ' ' . $userData[$userID]['lastName']
            : 'Unknown User';

        $paymentHTML .= "
            <tr>
                <td>{$fullName}</td>
                <td>₱" . number_format($paymentRow['amount'], 2) . "</td>
                <td>{$paymentRow['paymentMethod']}</td>
                <td>{$paymentRow['paymentStatus']}</td>
                <td>
                </td>
            </tr>
        ";
    }
} else {
    $paymentHTML = "<tr><td colspan='5'>No payments available.</td></tr>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments</title>
    <link rel="stylesheet" href="manager_payment.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="top-header">
        <div class="logo-section">
            <img src="../../images/qj-logo.png" alt="Quickie Jeepney Logo" class="logo-image">
        </div>
<<<<<<< HEAD
        
=======

>>>>>>> 71f70a71fe337edc9a02f36003114858dad36e34
        <div class="user-card">
            <button class="logout-btn" id="logoutBtn">
                <h3><i class="fas fa-sign-out-alt"></i>Logout</h3>
            </button>
<<<<<<< HEAD
            <a href="../profile/manager_profile.php" id="profileBtn">
                <span class="image">
                    <?php
                    // Check if profile image exists and display it, otherwise show default
                    if ($profileImage) {
                        // Display the actual profile image from the database
                        echo '<img src="' . htmlspecialchars($profileImage) . '" alt="Profile Image">';
                    } else {
                        // Display default profile image if no image is found
                        echo '<img src="../../images/profile.png" alt="Profile Image">';
                    }
                    ?>
                </span>
                <div class="text header-text">
                    <h3><?= $fullName; ?></h3>
                    <p><?= $occupation; ?></p>
                </div>
            </a>
=======
            
            <div class="text header-text">
                <h3><?php echo isset($_SESSION['userName']) ? $_SESSION['userName'] : "Guest : TODO"; ?></h3>
                <p><?php echo isset($_SESSION['userRole']) ? $_SESSION['userRole'] : "N/A : TODO"; ?></p>
            </div>
>>>>>>> 71f70a71fe337edc9a02f36003114858dad36e34
        </div>
    </header>

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="menu-title">Menu</div>
        <hr>
        <ul class="menu-links">
            <li class="nav-link">
                <a href="../menu/manager_menu.php" class="sidebar-link">
                    <i class="fas fa-home sidebar-icon"></i>Home
                </a>
            </li>
            <li class="nav-link">
                <a href="../profile/manager_profile.php" class="sidebar-link">
                    <i class="fas fa-user sidebar-icon"></i>Profile
                </a>
            </li>
            <li class="nav-link">
                <a href="../vehicles/manager_vehicles.php" class="sidebar-link">
                    <i class="fas fa-car sidebar-icon"></i>Vehicles
                </a>
            </li>
            <li class="nav-link">
                <a href="../booking/manager_booking.php" class="sidebar-link">
                    <i class="fas fa-calendar-alt sidebar-icon"></i>Booking Logs
                </a>
            </li>

            <li class="nav-link active">
                <a href="../payment/manager_payment.php" class="sidebar-link">
                    <i class="fas fa-calendar-alt sidebar-icon"></i>Payment
                </a>
            </li>
            <li class="nav-link">
                <a href="../passenger/manager_passenger.php" class="sidebar-link">
                    <i class="fas fa-calendar-alt sidebar-icon"></i>Manage Passengers
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="payments">
            <h1>Payments</h1>
            <table class="payments-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $paymentHTML; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script src="manager_payment.js"></script>
</body>
</html>
