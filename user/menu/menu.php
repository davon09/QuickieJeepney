<?php
session_start();
include '../../dbConnection/dbConnection.php';  

// Check if user is logged in (ensure the userID is in the session)
if (!isset($_SESSION['userID'])) {
    header("Location: /QuickieJeepney/index.php"); // Redirect to login page if not logged in
    exit();
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

// Fetch jeepney details including the image path
$sql = "SELECT * FROM jeepney";
$result = mysqli_query($conn, $sql);

$htmlOutput = '';
while ($row = mysqli_fetch_assoc($result)) {
    $jeepneyId = $row['jeepneyID']; 
    $vehicleType = strtolower($row['type']); 
    
    $departureTime = $row['departure_time']; 
    $departureTimeFormatted = date('h:i A', strtotime($departureTime)); 

    $htmlOutput .= '<div class="jeepney-card" data-type="' . $vehicleType . '" data-departure="' . $departureTimeFormatted . '">';
    $htmlOutput .= '<img src="serve_image.php?id=' . $jeepneyId . '" alt="Jeepney Image" class="jeepney-image">';
    $htmlOutput .= '<h2>Seats Available: ' . ($row['capacity'] - $row['occupied']) . '</h2>';
    $htmlOutput .= '<p>Route: ' . htmlspecialchars($row['route']) . '</p>';
    $htmlOutput .= '<p class="departure">Departure: ' . $departureTimeFormatted . '</p>';
    $htmlOutput .= '<button class="book-now" data-id="' . $jeepneyId . '">BOOK NOW</button>';
    $htmlOutput .= '</div>';
}

// Fetch announcements
$sqlAnnouncements = "SELECT announcementName, description, date, validUntil 
                      FROM announcements 
                      ORDER BY date ASC";

$resultAnnouncements = $conn->query($sqlAnnouncements);

$announcementHTML = '';
if ($resultAnnouncements && $resultAnnouncements->num_rows > 0) {
    while ($row = $resultAnnouncements->fetch_assoc()) {
        $announcementHTML .= '<h4>' . htmlspecialchars($row['announcementName']) . '</h4>';
        $announcementHTML .= '<p>' . htmlspecialchars($row['description']) . '</p>';
        $announcementHTML .= '<small>Effective: ' . htmlspecialchars($row['date']) . 
                             ' to ' . htmlspecialchars($row['validUntil']) . '</small><br><br>';
    }
} else {
    $announcementHTML = '<p>No announcements at the moment.</p>';
}

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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="menu.js"></script>
</head>

<body>
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
        </div>
    </header>

    <nav class="sidebar">
        <div class="menu-title">Menu</div> 
        <hr>
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
                <a href="../../user/booktransaction/booking-history.php" class="sidebar-link">
                    <img src="../../images/booking.png" alt="Booking" class="sidebar-icon">Booking
                </a>
            </li>
        </ul>
    </nav>

    <section class="main-content">

        <div class="cards-container">
            <div class="left-column">
                <div class="welcome-card">
                    <h2>Hi, <?= explode(' ', $fullName)[0]; ?>!</h2>
                    <p>Ready to reserve a jeepney?</p>
                </div>
                <div class="announcement-card">
                    <h3><i class="fas fa-bullhorn"></i> Announcements</h3>
                    <?= $announcementHTML; ?>
                </div>
            </div>
            <div class="right-column">
                <div class="available-jeepney-card">
                    <h3>Available Jeepney</h3>
                    <div class="filters">
                        <div>
                            <label for="type">Filter by Vehicle Type:</label>
                            <select id="type">
                                <option value="all">All</option>
                                <option value="traditional">Traditional</option>
                                <option value="modern">Modern</option>
                            </select>
                        </div>

                        <div>
                            <label for="sort-by">Sort by:</label>
                            <select id="sort-by">
                                <option value="none">None</option>
                                <option value="departure">Departure</option>
                                <option value="seats">Available Seats</option>
                            </select>
                        </div>
                    </div>

                    <div class="jeepney-cards" id="jeepney-cards">
                        <?= $htmlOutput; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>