<?php
include '../../dbConnection/dbConnection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jeepneyID = $_POST['jeepneyID'];
    $userID = $_SESSION['userID'];
    $status = 'confirmed';


    // Insert the booking into the database
    $stmt = $conn->prepare("INSERT INTO booking (userID, jeepneyID, status) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $userID, $jeepneyID, $status);

    if ($stmt->execute()) {
        // Update jeepney occupancy
        $updateStmt = $conn->prepare("UPDATE jeepney SET occupied = occupied + 1 WHERE jeepneyID = ?");
        $updateStmt->bind_param("i", $jeepneyID);
        $updateStmt->execute();
        $updateStmt->close();

        header("Location: success.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

