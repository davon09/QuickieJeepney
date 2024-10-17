<?php
// Include the database connection file
include 'db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password for security
    $phone = $_POST['phone'];
    $occupation = $_POST['occupation'];

    // Insert the data into the database
    $sql = "INSERT INTO users (fullname, email, password, phone, occupation) VALUES (?, ?, ?, ?, ?)";

    // Prepare the SQL statement to avoid SQL injection
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $fullname, $email, $password, $phone, $occupation);

    if ($stmt->execute()) {
        // Redirect to a success page or show a success message
        echo "Account created successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Create Account</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="container">
        <div class="form-header">
            <h1>Let’s Create Your Account</h1>
            <span class="close-btn">&times;</span>
        </div>
        <form action="register.php" method="POST" id="signupForm" class="signup-form">
            <div class="input-container">
                <input type="text" id="fullname" name="fullname" placeholder="Full Name" required>
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
                <input type="text" id="occupation" name="occupation" placeholder="Occupation" required>
            </div>
            <div class="terms-container">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">I agree to the <a href="#">Terms & Privacy</a></label>
            </div>
            <button type="submit" class="signup-btn">Sign Up</button>
            <p class="signin-link">Have an account? <a href="#">Sign In</a></p>
        </form>
    </div>

    <script src="register.js"></script>
</body>
</html>
