<?php
include 'dbConnection/dbConnection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loginInput = $conn->real_escape_string($_POST['loginInput']);
    $password = $_POST['password'];

    // Check if input is email or contact number
    if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
        $query = "SELECT * FROM user WHERE email=?";
    } else {
        $query = "SELECT * FROM user WHERE contactNumber=?";
    }

    // Prepare and execute the query
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $loginInput);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify the hashed password using password_verify()
            if (password_verify($password, $user['password'])) {
                
                // Successful login
                session_start();
                $_SESSION['userID'] = $user['userID'];
                $_SESSION['lastName'] = $user['lastName'];
                $_SESSION['firstName'] = $user['firstName'];
                $_SESSION['email'] = $user['email'];

                header("Location: user/menu/menu.php");
                exit();
            } else {
                // echo "$password - " . $user['password'];
                echo "<script>alert('Invalid password!'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Email or contact number not found!'); window.history.back();</script>";
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
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
                    <!-- Input field for email or contact number -->
                    <input type="text" name="loginInput" placeholder="Email or Contact Number" required>
                </div>
                <div class="information">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <span class="toggle-password" onclick="togglePasswordVisibility('password')">
                        <i class="fa fa-eye-slash" id="toggleIcon"></i>
                    </span>
                </div>
                <div class="forgot-password">
                    <a href="#">Forgot Password?</a>
                </div>
                <button type="submit" class="login-btn">Login</button>
                <div class="or-section">
                    <span>or</span>
                </div>
                <button type="button" class="create-account-btn" onclick="window.location.href='user/register/register.php';">
                    Create an account
                </button>
            </form>
        </div>
    </div>

    <!-- Link the external JavaScript file -->
    <script src="script.js"></script>
</body>
</html>
