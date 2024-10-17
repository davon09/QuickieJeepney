<?php
// Include the database connection file
include '../dbConnection/dbConnection.php';  // Make sure this points to the correct location

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
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssss", $fullname, $email, $password, $phone, $occupation);

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect to index.php after successful registration
            header("Location: index.php?registered=success");
            exit(); // Ensure the script stops after the redirect
        } else {
            // Debug message
            echo "Error inserting data: " . $stmt->error;  // For debugging SQL errors
        }

        // Close the statement
        $stmt->close();
    } else {
        // Debug message
        echo "Error preparing statement: " . $conn->error;  // For debugging statement preparation issues
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
