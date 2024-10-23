<?php
session_start(); // Ensure session is started before using session variables
include '../../dbConnection/dbConnection.php';

// Fetch user data based on session user ID
if (isset($_SESSION['userID'])) {
    $userID = $_SESSION['userID'];
} else {
    echo "No user logged in.";
    exit();
}

// Use prepared statements to prevent SQL injection
$sql = "SELECT firstName, lastName, email, contactNumber, occupation FROM user WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "No user found.";
    exit();
}

// Handle form submission for updating contact number and password
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $contactNumber = $_POST['contactNumber'];
    $password = $_POST['password'];

    // Check if contact number is provided and update it
    if (!empty($contactNumber)) {
        $updateContactSQL = "UPDATE user SET contactNumber = ? WHERE userID = ?";
        $stmt = $conn->prepare($updateContactSQL);
        $stmt->bind_param("si", $contactNumber, $userID);
        $stmt->execute();
    }

    // Check if password is provided and update it
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updatePasswordSQL = "UPDATE user SET password = ? WHERE userID = ?";
        $stmt = $conn->prepare($updatePasswordSQL);
        $stmt->bind_param("si", $hashedPassword, $userID);
        $stmt->execute();
    }

    header("Location: profile.php?update=success");
    exit();
}

$conn->close();
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="profile.css">
    <!-- Font Awesome for the eye icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="header">
        <a href="../menu/menu.php" class="back-button">
            <img src="/images/backarrow.png" alt="Back" width="30" height="30">
        </a>
        <h2>Edit Profile</h2>
    </div>

    <div class="profile-details">
        <div class="profile-image">
            <img src="../../images/profile.png" alt="Profile Picture">
            <span class="edit-icon">
                <img src="../../images/camera.png" alt="Edit Profile">
            </span>
        </div>
        <div class="profile-info">
            <h3><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></h3> <!-- current user name -->
            <div class="profile-info">
    <p><?php echo htmlspecialchars($user['occupation']); ?></p> <!-- role -->
</div>

        </div>
    </div>

    <form action="" method="POST" class="edit-form">
        <div class="form-row">
            <div class="form-group">
                <label for="fullname">Fullname</label>
                <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter new password">
                <span class="password-toggle">
                    <i class="fa fa-eye-slash"></i>
                </span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm new password">
                <span class="password-toggle">
                    <i class="fa fa-eye-slash"></i>
                </span>
            </div>
        </div>

        <div class="form-group">
            <label for="mobile">Mobile</label>
            <input type="text" id="mobile" name="contactNumber" value="<?php echo htmlspecialchars($user['contactNumber']); ?>">
        </div>

        <button type="submit" class="save-button">Save Changes</button>
    </form>

    <!-- JavaScript -->
    <script src="profile.js"></script>
</body>
</html>
