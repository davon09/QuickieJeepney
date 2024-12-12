<?php
session_start();
include '../../dbConnection/dbConnection.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch user profile details for the header
$userID = $_SESSION['userID'] ?? null;
$profileImage = null;
$fullName = "Guest";
$occupation = "N/A";

if ($userID) {
    $sqlUser = "SELECT firstName, lastName, occupation, profile_image FROM user WHERE userID = ?";
    $stmtUser = $conn->prepare($sqlUser);
    $stmtUser->bind_param("i", $userID);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();

    if ($resultUser->num_rows > 0) {
        $user = $resultUser->fetch_assoc();
        $fullName = $user['firstName'] . ' ' . $user['lastName'];
        $occupation = $user['occupation'];
        $profileImage = $user['profile_image'];
    }
}

// Fetch jeepney data
$sqlPassengers = "SELECT jeepneyID, plateNumber, type FROM jeepney";
$resultPassengers = $conn->query($sqlPassengers);

if (!$resultPassengers) {
    die("Query Failed: " . $conn->error);
}

// Generate HTML for passenger cards
$passengersHTML = '';
if ($resultPassengers->num_rows > 0) {
    while ($row = $resultPassengers->fetch_assoc()) {
        $passengersHTML .= "
            <div class='passenger-card'>
                <img src='../../images/jeepney_icon.png' alt='Jeepney Icon' class='passenger-icon'>
                <div class='passenger-info'>
                    <h3>{$row['plateNumber']}</h3>
                    <p>{$row['type']}</p>
                </div>
                <a href='../passengers/manage_passengers.php?jeepneyID={$row['jeepneyID']}' class='manage-passengers-btn'>Manage Passengers</a>
            </div>
        ";
    }
} else {
    $passengersHTML = "<p>No jeepneys available.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Passengers</title>
    <link rel="stylesheet" href="manager_passenger.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Header Section -->
    <header class="top-header">
        <div class="logo-section">
            <img src="../../images/qj-logo.png" alt="Quickie Jeepney Logo" class="logo-image">
        </div>
        
        <div class="user-card">
            <button class="logout-btn" id="logoutBtn">
                <h3><i class="fas fa-sign-out-alt"></i>Logout</h3>
            </button>
            <a href="../profile/profile.php" id="profileBtn">
                <span class="image">
                    <?php
                    if ($profileImage) {
                        echo '<img src="' . htmlspecialchars($profileImage) . '" alt="Profile Image">';
                    } else {
                        echo '<img src="../../images/profile.png" alt="Profile Image">';
                    }
                    ?>
                </span>
                <div class="text header-text">
                    <h3><?= $fullName; ?></h3>
                    <p><?= $occupation; ?></p>
                </div>
            </a>
        </div>
    </header>

    <!-- Sidebar Section -->
    <nav class="sidebar">
        <div class="menu-title">Menu</div> 
        <hr>
        <ul class="menu-links">
            <li class="nav-link">
                <a href="../menu/manager_menu.php" class="sidebar-link">
                    <i class="fas fa-home sidebar-icon" class="sidebar-icon"></i>Home
                </a>
            </li>
            <li class="nav-link">
                <a href="../profile/manager_profile.php" class="sidebar-link">
                    <i class="fas fa-user sidebar-icon" class="sidebar-icon"></i>Profile
                </a>
            </li>
            <li class="nav-link">
                <a href="../vehicles/manager_vehicles.php" class="sidebar-link">
                    <i class="fas fa-car sidebar-icon" class="sidebar-icon"></i>Vehicles
                </a>
            </li>
            <li class="nav-link">
                <a href="../booking/manager_booking.php" class="sidebar-link">
                    <i class="fas fa-calendar-alt sidebar-icon" class="sidebar-icon"></i>Booking Logs
                </a>
            </li>

            <li class="nav-link">
                <a href="../payment/manager_payment.php" class="sidebar-link">
                    <i class="fas fa-calendar-alt sidebar-icon" class="sidebar-icon"></i>Payment
                </a>
            </li>
            <li class="nav-link active">
                <a href="../passenger/manager_passenger.php" class="sidebar-link">
                    <i class="fas fa-calendar-alt sidebar-icon" class="sidebar-icon"></i>Manage Passengers
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="passenger-section">
            <h1>Manage Passengers</h1>
            <div class="passenger-cards">
                <?= $passengersHTML; ?>
            </div>
        </div>
    </main>
    <script src="manager_passenger.js"></script>
</body>
</html>
