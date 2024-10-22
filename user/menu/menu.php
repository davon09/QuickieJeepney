<?php
session_start();
include '../../dbConnection/dbConnection.php';  

// Check if user is logged in (ensure the userID is in the session)
if (!isset($_SESSION['userID'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Fetch logged-in user's details including the occupation
$userID = $_SESSION['userID'];
$sqlUser = "SELECT firstName, lastName, occupation FROM user WHERE userID = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $userID);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
if ($resultUser->num_rows > 0) {
    $user = $resultUser->fetch_assoc();
    $fullName = $user['firstName'] . ' ' . $user['lastName'];
    $occupation = $user['occupation'];
} else {
    $fullName = "Guest";
    $occupation = "N/A";
}

// Fetch jeepney details including the image path
$sql = "SELECT * FROM jeepney";
$result = mysqli_query($conn, $sql);

// Initialize an empty string to store the generated HTML
$htmlOutput = '';

// Loop through the result and build the HTML structure inside PHP
while ($row = mysqli_fetch_assoc($result)) {
    $jeepneyId = $row['jeepneyID']; // Assuming 'jeepneyID' is the primary key of the jeepney table
    $htmlOutput .= '<div class="jeepney-card" data-type="jeep" data-departure="11:00AM">';
    // Use the serve_image.php script to serve the image
    $htmlOutput .= '<img src="serve_image.php?id=' . $jeepneyId . '" alt="Jeepney Image">';
    $htmlOutput .= '<h3>Seats Available: ' . $row['occupied'] . '</h3>';
    $htmlOutput .= '<p>Route: ' . $row['route'] . '</p>';
    $htmlOutput .= '<p>Departure: 11:00 AM</p>';
    $htmlOutput .= '<button class="book-now">BOOK NOW</button>';
    $htmlOutput .= '</div>';
}

// Prepare the dynamic HTML snippets for the user details
$userDetailsHTML = '
    <span class="name">' . $fullName . '</span>
    <br>
    <span class="occupation">' . $occupation . '</span>
    <br>
';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Quickie Jeepney</title>
    <link rel="stylesheet" href="menu.css">
    <script src="menu.js"></script>
</head>
<body>
    <header class="top-header">
        <div class="logo-section">
            <img src="../../images/qj-logo.png" alt="Quickie Jeepney Logo" class="logo-image">
        </div>

        <div class="user-card">
            <span class="image">
                <img src="../../images/profile.png" alt="Profile Image">
            </span>
            <div class="text header-text">
                <h3>Danyel Rosario</h3>
                <p>Student</p>
            </div>
            <button class="logout-btn" id="logoutBtn">
                <img src="../../images/logout.png" alt="Logout Icon" class="logout-icon">
            </button>
        </div>
    </header>

    <nav class="sidebar">
        <div class="menu-title">Menu</div> 
        <ul class="menu-links">
            <li class="nav-link">
                <a href="menu.php" class="sidebar-link">
                    <img src="../../images/home.png" alt="Home" class="sidebar-icon">Home
                </a>
            </li>
            <li class="nav-link">
                <a href="../profile/profile.php" class="sidebar-link">
                    <img src="../../images/profile_menu.png" alt="Profile" class="sidebar-icon">Profile
                </a>
            </li>
            <li class="nav-link">
                <a href="../booking/booking.php" class="sidebar-link">
                    <img src="../../images/booking.png" alt="Booking" class="sidebar-icon">Booking
                </a>
            </li>
            <li class="nav-link">
                <a href="../payment/payment.php" class="sidebar-link">
                    <img src="../../images/payment.png" alt="Payment" class="sidebar-icon">Payment
                </a>
            </li>
        </ul>
    </nav>

    <section class="main-content">
        <div class="welcome">
            <h2>Hi, <?= explode(' ', $fullName)[0]; ?>!</h2>
            <p>Ready to reserve a jeepney?</p>
        </div>

        <div class="announcement-card">
            <h3>Announcements</h3>
            <p>The Jeepney Terminal will be moved in front of Shakey's Legarda.</p>
        </div>


        <div class="available-jeepney-card">
            <h3>Available Jeepney</h3>


            <div class="filters">
                <label for="vehicle-type">Filter by Vehicle Type:</label>
                <select id="vehicle-type">
                    <option value="all">All</option>
                    <option value="jeepney">Jeepney</option>
                    <option value="bus">Bus</option>
                </select>

                <label for="sort-by">Sort by:</label>
                <select id="sort-by">
                    <option value="departure">Departure</option>
                    <option value="seats">Available Seats</option>
                </select>
            </div>

            <div class="jeepney-cards" id="jeepney-cards">
                <?= $htmlOutput; ?> 
            </div>
        </div>
    </section>
</body>
</html>
