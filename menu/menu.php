<?php
include '../dbConnection/dbConnection.php';
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
                    <img src="/images/profile.png" alt="logo">
                </span>
                <div class="text header-text">
                    <span class="name">Maria Dela Cruz</span>
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
                        <a href="#home" class="sidebar-link">
                            <img src="/images/home.png" alt="home" class="sidebar-icon">Home</a>
                            </li>
                     <li class="nav-link">
                        <a href="../user/profile/profile.php" class="sidebar-link">
                            <img src="/images/profile_menu.png" alt="home" class="sidebar-icon">Profile</a>
                            </li>
                     <li class="nav-link">
                        <a href="#booking" class="sidebar-link">
                            <img src="/images/booking.png" alt="home" class="sidebar-icon">Booking</a>
                        </li>
                    <li class="nav-link">
                        <a href="#payment" class="sidebar-link">
                            <img src="/images/payment.png" alt="home" class="sidebar-icon">Payment</a></a>
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
                    <option value="all">Jeepney</option>
                    <option value="all">Bus</option>

                </select>
                <label for="sort-by">Sort by:</label>
                <select id="sort-by">
                    <option value="departure">Departure</option>
                    <option value="departure">Time</option>
                    <option value="departure">Available Seats</option>
                    <!-- Add more sorting options here -->
                </select>
            </div>

            <div class="jeepney-cards">
                <!-- Each card structure -->
                <div class="jeepney-card" data-type="jeep" data-departure="11:00">
                    <img src="/images/jeep1.jpg" alt="Jeepney Image">
                    <h3>Seats Available:10</h3>
                    <p>Route: Marcos Highway to Burnham Park</p>
                    
                    <p>Departure: 11:00 AM</p>
                    
                    <button class="book-now">BOOK NOW</button>
                </div>
                <div class="jeepney-card" data-type="jeep" data-departure="11:00">
                    <img src="/images/jeep1.jpg" alt="Jeepney Image">
                    <h3>Seats Available:10</h3>
                    <p>Route: Marcos Highway to Burnham Park</p>
                    
                    <p>Departure: 11:00 AM</p>
                   
                    <button class="book-now">BOOK NOW</button>
                </div>
                <div class="jeepney-card" data-type="jeep" data-departure="11:00">
                    <img src="/images/jeep1.jpg" alt="Jeepney Image">
                    <h3>Seats Available:6</h3>
                    <p>Route: Marcos Highway to Burnham Park</p>
                    
                    <p>Departure: 11:00 AM</p>
                    
                    <button class="book-now">BOOK NOW</button>
                </div>
                
                <div class="jeepney-card" data-type="bus" data-departure="13:30">
                    <img src="/images/bus1.jpg" alt="Bus Image">
                    <h3>Seats Available:12</h3>
                    <p>Route: Happy Homes to Burnham Park</p>
                    
                    <p>Departure: 1:30 PM</p>
                    
                    <button class="book-now">BOOK NOW</button>
                </div>
                <div class="jeepney-card" data-type="bus" data-departure="13:30">
                    <img src="/images/bus1.jpg" alt="Bus Image">
                    <h3>Seats Available:69</h3>
                    <p>Route: Happy Homes to Burnham Park</p>
                    
                    <p>Departure: 1:30 PM</p>
                    
                    <button class="book-now">BOOK NOW</button>
                </div>
                <div class="jeepney-card" data-type="bus" data-departure="13:30">
                    <img src="/images/bus1.jpg" alt="Bus Image">
                    <h3>Seats Available:30 </h3>
                    <p>Route: Happy Homes to Burnham Park</p>            
                    <p>Departure: 1:30 PM</p>
                    <button class="book-now">BOOK NOW</button>
                </div>
        </div>
        <!-- Logout Confirmation Modal -->
        <div id="logoutModal" class="modal">
            <div class="modal-content">
                <p>Are you sure you want to Logout?</p>
                <button id="confirmLogout">Logout</button>
                <button id="cancelLogout">Cancel</button>
            </div>
        </div>
    </section>   
    </body>  
</html>