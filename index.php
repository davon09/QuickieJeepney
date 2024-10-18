<?php
include 'dbConnection/dbConnection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);

    // Query to find the user by email
    $query = "SELECT * FROM user WHERE email='$email'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            // Successful login
            // Store user information in session
            $_SESSION['userID'] = $user['userID']; // Store user ID
            $_SESSION['lastName'] = $user['lastName']; // Store user's name
            $_SESSION['firstName'] = $user['firstName']; // Store user's name
            $_SESSION['email'] = $user['email']; // Store user's email

            header("Location: menu.html");
            exit();
        } else {
            echo "<script>alert('Invalid password!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Email not found!'); window.history.back();</script>";
    }

    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Ride Booking</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="background-overlay"></div>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Quickie Jeepney</h1>
                <p>BOOKING</p>
            </div>
            <!-- Update form action to point to the current PHP script -->
            <form action="index.php" method="POST" id="bookingForm">
                <div class="information">
                    <input type="email" name="email" placeholder="Email or Phone" required>
                </div>
                <div class="information">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <span class="toggle-password" onclick="togglePasswordVisibility('password')">
                        <i class="fa fa-eye-slash"></i> <!-- Initial icon is eye-slash -->
                    </span>
                </div>
                <div class="forgot-password">
                    <a href="#">Forgot Password?</a>
                </div>
                <button type="submit" class="login-btn">Login</button>
                <div class="or-section">
                    <span>or</span>
                </div>
                <a href="register/register.php" class="create-account-btn">Create an account</a>
            </form>
        </div>
    </div>

    <!-- Link the external JavaScript file -->
    <script src="script.js"></script>
</body>
</html>
