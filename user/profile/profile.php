<?php
include '../../dbConnection/dbConnection.php';

// Fetch user data based on session user ID
$userID = $_SESSION['userID'];  // Assuming userID was stored in session during login
$sql = "SELECT firstName, lastName, email, contactNumber FROM user WHERE userID = '$userID'";
$result = $conn->query($sql);

/*$query = "SELECT * FROM user WHERE userID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();*/

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "No user found";
    exit();
}

// Handle form submission for updating contact number and password
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $contactNumber = $_POST['contactNumber'];
    $password = $_POST['password'];

    if (!empty($contactNumber)) {
        // Update contact number
        $updateContactSQL = "UPDATE users SET ContactNumber = '$contactNumber' WHERE UserID = '$UserID'";
        $conn->query($updateContactSQL);
    }

    if (!empty($password)) {
        // Hash the new password and update
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updatePasswordSQL = "UPDATE users SET Password = '$hashedPassword' WHERE UserID = '$UserID'";
        $conn->query($updatePasswordSQL);
    }

    header("Location: profile.php?update=success");
    exit();
}

/*if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update user data if form is submitted
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];  // In production, make sure to validate inputs
    $contact = $_POST['contact'];

    // Only update password if a new password was provided
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $updateQuery = "UPDATE user SET name=?, email=?, password=?, contact=? WHERE userID=?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssssi", $name, $email, $hashedPassword, $contact, $userID);
    } else {
        $updateQuery = "UPDATE user SET name=?, email=?, contact=? WHERE userID=?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssi", $name, $email, $contact, $userID);
    }

    if ($stmt->execute()) {
        echo "Profile updated successfully!";
        // Optionally reload the page to reflect updated data
        header("Location: profile.php");
        exit();
    } else {
        echo "Failed to update profile.";
    }
}*/

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <div class="header">
        <a href="#" class="back-button">
            <img src="/images/backarrow.png" alt="Back" width="30" height="30">
        </a>
        <h2>Edit Profile</h2>
    </div>

    <div class="profile-details">
        <div class="profile-image">
            <img src="/images/profile.png" alt="Profile Picture">
            <span class="edit-icon">
                    <img src="/images/camera.png" alt="Edit Profile">
                </span>
        </div>
        <div class="profile-info">
            <h3>Maria Dela Cruz</h3>
            <p>Student</p>
        </div>
    </div>

    <form action="#" method="POST" class="edit-form">
        <div class="form-row">
            <div class="form-group">
                <label for="fullname">Fullname</label>
                <input type="text" id="fullname" name="fullname" value="Maria Dela Cruz">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" value="password">
                <span class="password-toggle">
                        <img src="/images/eye.png" alt="Toggle Password" width="24" height="24">
                    </span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="delacruzmaria@gmail.com">
            </div>
            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm-password" value="password">
                <span class="password-toggle">
                        <img src="/images/eye.png" alt="Toggle Password" width="24" height="24">
                    </span>
            </div>
        </div>

        <div class="form-group">
            <label for="mobile">Mobile</label>
            <input type="text" id="mobile" name="mobile" value="09881234567">
        </div>

        <button type="submit" class="save-button">Save Changes</button>
    </form>
</body>
</html>
