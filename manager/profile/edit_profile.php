<?php
include '../../dbConnection/dbConnection.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    $userID = $_SESSION['userID'];

    // Get form data
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validate required fields
    if (empty($firstName) || empty($lastName) || empty($email)) {
        $response['message'] = 'First name, last name, and email are required.';
        echo json_encode($response);
        exit();
    }

    // Handle Profile Photo Upload
    $profilePhotoPath = null;
    if (isset($_FILES['profilePhoto']) && $_FILES['profilePhoto']['error'] === UPLOAD_ERR_OK) {
        $fileName = $_FILES['profilePhoto']['name'];
        $fileTmpName = $_FILES['profilePhoto']['tmp_name'];
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileType, $allowedTypes)) {
            // Create unique file name
            $newFileName = uniqid('profile_', true) . '.' . $fileType;

            // Ensure the upload directory exists
            $uploadDir = '../../uploads/profile_photos/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $uploadFile = $uploadDir . $newFileName;

            // Move uploaded file
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

    // Begin a database transaction
    $conn->begin_transaction();

    try {
        // Update basic profile information
        $updateQuery = "UPDATE user SET firstName = ?, lastName = ?, email = ? WHERE userID = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssi", $firstName, $lastName, $email, $userID);
        if (!$stmt->execute()) {
            throw new Exception('Error updating profile information.');
        }

        // Update profile photo if uploaded
        if ($profilePhotoPath) {
            $updateImageQuery = "UPDATE user SET profile_image = ? WHERE userID = ?";
            $stmtImage = $conn->prepare($updateImageQuery);
            $stmtImage->bind_param("si", $profilePhotoPath, $userID);
            if (!$stmtImage->execute()) {
                throw new Exception('Error updating profile photo.');
            }
        }

        // Update password if provided and matches confirmation
        if (!empty($password)) {
            if ($password === $confirmPassword) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updatePasswordQuery = "UPDATE user SET password = ? WHERE userID = ?";
                $stmtPassword = $conn->prepare($updatePasswordQuery);
                $stmtPassword->bind_param("si", $hashedPassword, $userID);
                if (!$stmtPassword->execute()) {
                    throw new Exception('Error updating password.');
                }
            } else {
                throw new Exception('Password and confirm password do not match.');
            }
        }

        // Commit the transaction
        $conn->commit();

        $response['success'] = true;
        $response['message'] = 'Profile updated successfully!';
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
}
?>