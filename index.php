<?php
include 'dbConnection/dbConnection.php';

// Hardcoded users array
$users = [
    ['username' => 'student', 'password' => 'pass123'],
    ['username' => 'faculty', 'password' => 'pass456'],
    ['username' => 'employee', 'password' => 'pass789'],
];

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];  // Username field from the form
    $password = $_POST['password'];  // Password field from the form

    // 1. Check against hardcoded users array
    $valid = false;
    foreach ($users as $user) {
        if ($user['username'] == $username && $user['password'] == $password) {
            $valid = true;
            break;
        }
    }

    if ($valid) {
        // If user is found in the hardcoded list
        $_SESSION['username'] = $username;
        header('Location: menu.html');
    } else {
        // 2. Check against the database if not found in hardcoded users
        $sql = "SELECT * FROM users WHERE email = ?";
        
        // Prepare the statement to avoid SQL injection
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);  // Assuming username is the email in the database
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Fetch the user data
            $user = $result->fetch_assoc();
            
            // Verify the password (assuming it's hashed in the database)
            if (password_verify($password, $user['password'])) {
                // Password is correct, start the session and redirect
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['firstname'];  // Or any other user data
                
                // Redirect to the dashboard or homepage
                header("Location: menu.html");
            } else {
                echo "Invalid password.";
            }
        } else {
            // No user found with the given username/email in either array or database
            header('Location: index.php?error=1');
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    }
}

// Display error message if login fails
if (isset($_GET['error'])) {
    echo '<p style="color: red; text-align: center;">Invalid login credentials, please try again.</p>';
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
    <!-- Display registration success message here -->
    <?php if (isset($_GET['registered']) && $_GET['registered'] == 'success'): ?>
        <p style="color: green; text-align: center;">Registration successful! You can now log in.</p>
    <?php endif; ?>

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
                    <input type="text" id="username" name="username" placeholder="Email or Phone" required>
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
