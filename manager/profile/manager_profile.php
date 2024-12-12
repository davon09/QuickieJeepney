<?php
session_start();
include '../../dbConnection/dbConnection.php';  

// Check if user is logged in (ensure the userID is in the session)
// if (!isset($_SESSION['userID'])) {
//     header("Location: /QuickieJeepney/index.php"); // Redirect to login page if not logged in
//     exit();
// }

// Fetch logged-in user's details including the occupation
$userID = $_SESSION['userID'];
$sqlUser = "SELECT firstName, lastName, occupation, email, contactNumber, profile_image FROM user WHERE userID = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $userID);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
if ($resultUser->num_rows > 0) {
    $user = $resultUser->fetch_assoc();
    $fullName = $user['firstName'] . ' ' . $user['lastName'];
    $occupation = $user['occupation'];
    $email = $user['email'];
    $contactNumber= $user['contactNumber'];
    $profileImage = $user['profile_image'];

    $firstName = $user['firstName']; 
    $lastName = $user['lastName']; 
} else {
    $firstName = "Guest";
    $lastName = "User";
    $occupation = "N/A";
    $email = "N/A";
    $contactNumber = "N/A";
    $profileImage = null;
}

$userDetailsHTML = '
    <span class="name">' . $firstName . ' ' . $lastName . '</span>
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
    <link rel="stylesheet" href="manager_profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="manager_profile.js"></script>
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
                <a href="../menu/manager_menu.php" class="sidebar-link">
                    <i class="fas fa-home sidebar-icon" class="sidebar-icon"></i>Home
                </a>
            </li>
            <li class="nav-link active">
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
            <li class="nav-link">
                <a href="../passenger/manager_passenger.php" class="sidebar-link">
                    <i class="fas fa-calendar-alt sidebar-icon" class="sidebar-icon"></i>Manage Passengers
                </a>
            </li>
        </ul>
    </nav>
    <section class="main-content">
        <form id="editProfileForm" action="edit_profile.php" method="POST" enctype="multipart/form-data">
            <div class="profile-card">
                <div class="profile-header">
                    <h2>Edit Profile</h2>
                </div>

                <!-- Profile and Edit Section -->
                <div class="profile-container">
                    <!-- Profile Picture Section (Left Side) -->
                    <div class="profile-left">
                        <div class="profile-picture">
                            <?php
                            if ($profileImage) {
                                // Display the profile image from the database as an image file path
                                echo '<img id="profileImage" src="' . htmlspecialchars($profileImage) . '" alt="Profile Image" style="width: 250px; height: 250px; border-radius: 50%;">';
                            } else {
                                // Display default image if no profile image is set
                                echo '<img id="profileImage" src="../../images/profile.png" alt="Profile Image" style="width: 250px; height: 250px; border-radius: 50%;">';
                            }
                            ?>
                            <label for="uploadPhoto" class="upload-photo-btn">Change Photo</label>
                            <input type="file" id="uploadPhoto" name="profilePhoto" class="upload-input" accept="image/*">
                        </div>
                        <hr class="divider">
                        <div class="user-details">
                            <h2><?= $firstName . ' ' . $lastName; ?></h2>
                            <p><?= $occupation; ?></p>
                        </div>
                    </div>

                    <!-- Edit Profile Form (Right Side) -->
                    <div class="profile-right">
                        <div class="form-group">
                            <label for="editFirstName">First Name</label>
                            <input type="text" id="editFirstName" name="firstName" value="<?= htmlspecialchars($firstName); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="editLastName">Last Name</label>
                            <input type="text" id="editLastName" name="lastName" value="<?= htmlspecialchars($lastName); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="editEmail">Email</label>
                            <input type="email" id="editEmail" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="editPassword">Password</label>
                            <input type="password" id="editPassword" name="password" placeholder="New password">
                        </div>

                        <div class="form-group">
                            <label for="editConfirmPassword">Confirm Password</label>
                            <input type="password" id="editConfirmPassword" name="confirmPassword" placeholder="Confirm new password">
                        </div>
                        <div class="form-group">
                            <label for="contactNumber">Contact Number</label>
                            <input type="text" id="contactNumber" name="contactNumber" value="<?= htmlspecialchars($contactNumber); ?>" required>
                        </div>

                        <div class="form-footer">
                            <button type="submit" class="submit-btn">Save Changes</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
</body>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const uploadInput = document.getElementById('uploadPhoto');
        const profileImage = document.getElementById('profileImage');
        const form = document.getElementById('editProfileForm');

        // Handle image preview when a new photo is selected
        uploadInput.addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImage.src = e.target.result; // Update the image preview
                };
                reader.readAsDataURL(file);
            }
        });

        // Handle form submission with Fetch API
        form.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the form from submitting the usual way

            const formData = new FormData(form); // Collect form data including the file

            // Submit form data to the PHP script using fetch
            fetch('edit_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message); // Show success message
                    location.reload(); // Optionally reload the page
                } else {
                    alert('Error: ' + data.message); // Show error message
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('There was an error with the request.');
            });
        });
    });
   
</script>
</html>