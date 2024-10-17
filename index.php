<?php
session_start();
include '../dbConn/db_Conn.php';

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
        header('Location: index.html');
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
                header("Location: dashboard.php");
            } else {
                echo "Invalid password.";
            }
        } else {
            // No user found with the given username/email in either array or database
            header('Location: login.html?error=1');
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    }
}

// Display error message if login fails
if (isset($_GET['error'])) {
    echo '<p style="color: red;">Invalid login credentials, please try again.</p>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Ride Booking</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="background-overlay"></div>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Quickie Jeepney</h1>
                <p>BOOKING</p>
            </div>
                <form action="#">
                    <div class="information">
                        <input type="text" placeholder="Email or Phone" required>
                    </div>
                    <div class="information">
                        <input type="password" placeholder="Password" required>
                    </div>
                    <div class="forgot-password">
                        <a href="#">Forgot Password?</a>
                    </div>
                    <button type="submit" class="login-btn">Login</button>
                    <div class="or-section">
                        <span>or</span>
                    </div>
                    <button type="button" class="create-account-btn">Create an account</button>
                </form>
            </div>
        </div>

    <script src="script.js"></script>

</body>
</html>
