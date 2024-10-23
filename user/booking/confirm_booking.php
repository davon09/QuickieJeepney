<?php
include '../../dbConnection/dbConnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jeepneyID = $_POST['jeepneyID'];
    $userID = 1; 
    $status = 'Booked'; 

    $stmt = $conn->prepare("INSERT INTO booking (userID, jeepneyID, status) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $userID, $jeepneyID, $status);

    if ($stmt->execute()) {
    
        header("Location: success.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
