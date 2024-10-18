<?php
// Include the database connection file
include '../dbConnection/dbConnection.php';  // Make sure this points to the correct location

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $lastName = $conn->real_escape_string($_POST['lastName']);
    $firstName = $conn->real_escape_string($_POST['firstName']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $occupation = $conn->real_escape_string($_POST['occupation']);

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    echo "Hashed Password: " . $hashed_password; // Debug line

    // Check if the email already exists
    $check_email_query = "SELECT * FROM user WHERE email = '$email'";
    $result = $conn->query($check_email_query);

    if ($result->num_rows > 0) {
        echo "<script>alert('Email already exists!'); window.history.back();</script>";
        exit;
    }

    // Insert the data into the database
    $sql = "INSERT INTO user (lastName, firstName, email, password, phone, occupation) VALUES 
    ('$lastName', '$firstName', '$email', '$hashed_password', '$phone', '$occupation')";
    echo $sql; // Debug line

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
    } else {
        echo "Error inserting user: " . $conn->error; // Debug line
    }

    // Close the connection
    $conn->close();
}
?>

<!-- HTML for the form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="container">
        <div class="form-header">
            <h1>Let’s Create Your Account</h1>
            <span class="close-btn" onclick="window.location.href='../index.php';">&times;</span>
        </div>
        <form action="register.php" method="POST" id="signupForm" class="signup-form">
            <div class="input-container">
                <input type="text" id="lastName" name="lastName" placeholder="Last Name" required>
            </div>
            <div class="input-container">
                <input type="text" id="firstName" name="firstName" placeholder="First Name" required>
            </div>
            <div class="input-container">
                <input type="email" id="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="input-container">
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            <div class="input-container">
                <input type="tel" id="phone" name="phone" placeholder="Phone Number" required>
            </div>
            <div class="input-container">
                <select id="occupation" name="occupation" required>
                    <option value="" disabled selected>Select Occupation</option>
                    <option value="Student">Student</option>
                    <option value="Teacher">Teacher</option>
                </select>
            </div>
            <div class="terms-container">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">I agree to the <a href="#">Terms & Privacy</a></label>
            </div>
            <button type="submit" class="signup-btn">Sign Up</button>
        </form>
    </div>
    <script src="register.js"></script>
</body>
</html>
