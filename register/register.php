<?php
// Include the database connection file
include '../dbConnection/dbConnection.php';  // Make sure this points to the correct location

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $lastName = $_POST['lastName'];
    $firstName = $_POST['firstName'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password for security
    $contactNumber = $_POST['contactNumber'];
    $occupation = $_POST['occupation'];

    // Check if email already exists in the database
    $checkEmailQuery = "SELECT * FROM user WHERE email = ?";
    if ($stmt = $conn->prepare($checkEmailQuery)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Email already exists, redirect to register.html with an error message
            header("Location: register.php?error=email_exists");
            $stmt->close();
            $conn->close();
            exit(); // Stop further execution to avoid duplicate registration
        }

        $stmt->close();
    } else {
        // Error preparing the statement
        echo "Error preparing email check: " . $conn->error;
    }

    // Insert the data into the database
    $sql = "INSERT INTO user (lastName, firstName, email, password, contactNumber, occupation) VALUES (?, ?, ?, ?, ?, ?)";

    // Prepare the SQL statement to avoid SQL injection
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssss", $lastName, $firstName, $email, $password, $contactNumber, $occupation);

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect to index.php after successful registration
            header("Location: ../index.php?registered=success");
            exit(); // Ensure the script stops after the redirect
        } else {
            // Debug message for insertion errors
            echo "Error inserting data: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        // Debug message for statement preparation errors
        echo "Error preparing statement: " . $conn->error;
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
                <input type="tel" id="contactNumber" name="contactNumber" placeholder="Contact Number" required>
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
    <div id="popupMessage" class="popup-message"></div>
    <script src="register.js"></script>
</body>
</html>
