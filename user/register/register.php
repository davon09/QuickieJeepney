<?php
include '../dbConnection/dbConnection.php';  // Adjust path as necessary

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data from POST request
    $lastName = $_POST['lastName'];
    $firstName = $_POST['firstName'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password for security
    $contactNumber = $_POST['contactNumber'];
    $occupation = $_POST['occupation'];

    // Check if email already exists in the database
    $checkEmailQuery = "SELECT * FROM user WHERE email = ?";
    $stmt = $conn->prepare($checkEmailQuery);
    
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo 'email_exists';  // Send 'email_exists' if email is already in use
            $stmt->close();  // Close the statement after checking email
            $conn->close();  // Close the connection
            exit();  // Stop further execution
        }

        // Close the statement after the check is done
        $stmt->close();
    } else {
        // Error preparing the statement
        echo 'error';
        $conn->close();  // Close connection if there was an error
        exit();
    }

    // Insert the data into the database
    $sql = "INSERT INTO user (lastName, firstName, email, password, contactNumber, occupation) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssssss", $lastName, $firstName, $email, $password, $contactNumber, $occupation);

        if ($stmt->execute()) {
            // Registration successful, send 'success'
            echo 'success';
        } else {
            // Error inserting data
            echo 'error';
        }

        // Close the statement after insertion
        $stmt->close();
    } else {
        // Error preparing the statement
        echo 'error';
    }

    // Close the database connection
    $conn->close();
    exit();  // Stop further execution to prevent HTML from being sent
}
?>

<?php
// The HTML form should only be rendered if the request is NOT a POST request
if ($_SERVER["REQUEST_METHOD"] != "POST"): 
?>

<!-- HTML for the form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
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
            <div class="input-container" style="position: relative;">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <span class="toggle-password" onclick="togglePasswordVisibility('password')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                    <i class="fa fa-eye-slash"></i>
                </span>
            </div>
            <div class="input-container" style="position: relative;">
                <input type="password" id="retypePassword" name="retypePassword" placeholder="Retype Password" required>
                <span class="toggle-password" onclick="togglePasswordVisibility('retypePassword')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                    <i class="fa fa-eye-slash"></i>
                </span>
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
<?php endif; // End of HTML section ?>
