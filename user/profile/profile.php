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
$sql = "SELECT firstName, lastName, email, contactNumber, occupation, profile_image FROM user WHERE userID = ?";
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

// Handle form submission for updating contact number, password, and profile image
if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    
    // Update contact number
    if (isset($_POST['contactNumber'])) {
        $contactNumber = $_POST['contactNumber'];
        if (!empty($contactNumber)) {
            $updateContactSQL = "UPDATE user SET contactNumber = ? WHERE userID = ?";
            $stmt = $conn->prepare($updateContactSQL);
            $stmt->bind_param("si", $contactNumber, $userID);
            $stmt->execute();
        }
    }

    // Update password
    if (isset($_POST['password']) && !empty($_POST['password'])) {
        $password = $_POST['password'];
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updatePasswordSQL = "UPDATE user SET password = ? WHERE userID = ?";
        $stmt = $conn->prepare($updatePasswordSQL);
        $stmt->bind_param("si", $hashedPassword, $userID);
        $stmt->execute();
    }

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $image = $_FILES['profile_image']['tmp_name'];
        $imageData = file_get_contents($image);
        $imageData = mysqli_real_escape_string($conn, $imageData);

        $sql = "UPDATE user SET profile_image = ? WHERE userID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $imageData, $userID);
        if ($stmt->execute()) {
            echo "Profile image uploaded successfully!";
        } else {
            echo "Error updating profile image: " . $conn->error;
        }
    }
    
    header("Location: profile.php");
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
            <img src="../../images/backarrow.png" alt="Back" width="30" height="30">
        </a>
        <h2>Edit Profile</h2>
    </div>

    <form action="profile.php" method="POST" enctype="multipart/form-data">
    <div class="profile-details">
        <div class="profile-image">
        <?php
        if (!empty($user['profile_image'])) {
            echo '<img class="profile-pic" src="data:image/png;base64,' . base64_encode($user['profile_image']) . '" alt="Profile Picture">';
        } else {
            echo '<img class="profile-pic" src="../../images/profile.png" alt="Profile Picture">';
        }
        ?>

        <span class="edit-icon">
            <img src="../../images/camera.png" alt="Edit Profile">
            <input type="file" name="profile_image" id="profile_image" style="display:none;">
        </span>
            
        </div>
        <div class="profile-info">
            <h3><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></h3> <!-- current user name -->
            <div class="profile-info">
                <p><?php echo htmlspecialchars($user['occupation']); ?></p> <!-- role -->
            </div>
        </div>
    </div>

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
