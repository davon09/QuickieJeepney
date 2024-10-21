<?php
include '../../dbConnection/dbConnection.php';  

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
    <nav class="sidebar">
        <header>
            <div class="image-text">
                <span class="image">
                    <img src="../../images/profile.png" alt="Profile Image">
                </span>
                <div class="text header-text">
                    <span class="name">Maria Dela Cruz</span>
                    <br>
                    <span class="occupation">Student</span>
                    <br>
                    <button class="logout-btn" id="logoutBtn">Logout</button>
                    <!-- Popup Modal for Logout Confirmation -->
                    <div id="confirmLogout" class="modal">
                        <div class="modal-content">
                            <p>Are you sure you want to log out?</p>
                            <button id="confirmYes" class="confirm-btn">Yes</button>
                            <button id="confirmNo" class="confirm-btn">No</button>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <div class="menu-bar">
            <div class="menu">
                <ul class="menu-links">
                    <li class="nav-link">
                        <a href="menu.php" class="sidebar-link">
                            <img src="../../images/home.png" alt="Home" class="sidebar-icon">Home</a>
                    </li>
                    <li class="nav-link">
                        <a href="../profile/profile.php" class="sidebar-link">
                            <img src="../../images/profile_menu.png" alt="Profile" class="sidebar-icon">Profile</a>
                    </li>
                    <li class="nav-link">
                        <a href="../booking/booking.php" class="sidebar-link">
                            <img src="../../images/booking.png" alt="Booking" class="sidebar-icon">Booking</a>
                    </li>
                    <li class="nav-link">
                        <a href="../payment/payment.php" class="sidebar-link">
                            <img src="../../images/payment.png" alt="Payment" class="sidebar-icon">Payment</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="main-content">
        <div class="welcome">
            <h2>Hi, Maria!</h2>
            <p>Ready to reserve a jeepney?</p>
        </div>

        <div class="announcements">
            <h3>Announcements</h3>
            <p>The Jeepney Terminal will be moved in front of Shakey's Legarda.</p>
        </div>

        <div class="available-jeepney">
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

            <!-- Output the generated HTML for the jeepney cards -->
            <?php echo $htmlOutput; ?>
        </div>
    </section>
</body>
</html>
