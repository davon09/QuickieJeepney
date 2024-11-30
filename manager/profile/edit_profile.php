<?php
include '../../dbConnection/dbConnection.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    $userID = $_SESSION['userID'];

    // Get form data
    $firstName = $_POST['firstName']; // Matches the name attribute of the firstName input
    $lastName = $_POST['lastName']; // Matches the name attribute of the lastName input
    $email = $_POST['email']; // Matches the name attribute of the email input
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Handle Profile Photo Upload
    $profilePhotoPath = null;

    if (isset($_FILES['profilePhoto']) && $_FILES['profilePhoto']['error'] === UPLOAD_ERR_OK) {
        $fileName = $_FILES['profilePhoto']['name'];
        $fileTmpName = $_FILES['profilePhoto']['tmp_name'];
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        // Validate file type
        if (in_array($fileType, $allowedTypes)) {
            // Ensure the upload directory exists
            $uploadDir = '../../uploads/profile_photos/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
            }

            $uploadFile = $uploadDir . basename($fileName);

            // Move uploaded file to desired location
            if (move_uploaded_file($fileTmpName, $uploadFile)) {
                $profilePhotoPath = $uploadFile;
            } else {
                $response['message'] = 'Error uploading the photo.';
                echo json_encode($response);
                exit();
            }
        } else {
            $response['message'] = 'Invalid file type. Only jpg, jpeg, png, gif are allowed.';
            echo json_encode($response);
            exit();
        }
    }

    // Update profile information in the database
    $updateQuery = "UPDATE user SET firstName = ?, lastName = ?, email = ? WHERE userID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssi", $firstName, $lastName, $email, $userID);

    if ($stmt->execute()) {
        // Update profile image if uploaded
        if ($profilePhotoPath) {
            $updateImageQuery = "UPDATE user SET profile_image = ? WHERE userID = ?";
            $stmtImage = $conn->prepare($updateImageQuery);
            $stmtImage->bind_param("si", $profilePhotoPath, $userID);
            $stmtImage->execute();
        }

        // Update password if provided and matches confirmation
        if ($password && $password === $confirmPassword) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updatePasswordQuery = "UPDATE user SET password = ? WHERE userID = ?";
            $stmtPassword = $conn->prepare($updatePasswordQuery);
            $stmtPassword->bind_param("si", $hashedPassword, $userID);
            $stmtPassword->execute();
        }

        // Return success response
        $response['success'] = true;
        $response['message'] = 'Profile updated successfully!';
    } else {
        $response['message'] = 'Error updating profile data.';
    }

    echo json_encode($response);
}
?>