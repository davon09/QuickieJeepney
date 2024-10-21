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
    <link rel="stylesheet" href="profile.css">
    <title>User Profile</title>
</head>
<body>
    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <img src="profile-pic.png" alt="Profile Picture" class="profile-pic">
                <h1><?= $user['FirstName'] . ' ' . $user['LastName']; ?></h1>
                <p><?= $user['Email']; ?></p>
            </div>
            <form method="POST" action="profile.php">
                <div class="form-group">
                    <label for="contactNumber">Contact Number</label>
                    <input type="text" name="contactNumber" id="contactNumber" value="<?= $user['ContactNumber']; ?>">
                </div>
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" placeholder="Enter new password">
                </div>
                <button type="submit" class="save-btn">Save Changes</button>
            </form>
        </div>
    </div>

    <script src="profile.js"></script>
</body>
</html>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <div class="profile-container">
        <div class="profile-card">
            <form method="POST" action="profile.php">
                <div class="profile-header">
                    <img src="user-placeholder.png" alt="Profile" class="profile-pic">
                    <h1><?php echo htmlspecialchars($user['name']); ?></h1>
                    <p>Student</p>
                </div>
                <div class="form-group">
                    <label for="name"><i class="fa fa-user"></i></label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email"><i class="fa fa-envelope"></i></label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password"><i class="fa fa-lock"></i></label>
                    <input type="password" name="password" id="password" placeholder="New password (leave blank if not changing)">
                </div>
                <div class="form-group">
                    <label for="contact"><i class="fa fa-phone"></i></label>
                    <input type="text" name="contact" id="contact" value="<?php echo htmlspecialchars($user['contact']); ?>" required>
                </div>
                <button type="submit" class="save-btn">Save Changes</button>
            </form>
        </div>
    </div>
    <script src="profile.js"></script>
</body>
</html>
