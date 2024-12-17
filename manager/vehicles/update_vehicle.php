<?php
session_start();
include '../../dbConnection/dbConnection.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jeepneyID'], $_POST['departure_time'], $_POST['status'])) {
    $jeepneyID = $_POST['jeepneyID'];
    $departureTime = $_POST['departure_time'];
    $status = $_POST['status'];

    // SQL query
    $sql = "UPDATE jeepney SET departure_time = ?, status = ? WHERE jeepneyID = ?";

    try {
        // Prepare the SQL statement
        $stmt = $conn->prepare($sql);
        
        // Bind parameters to the query
        $stmt->bind_param("ssi", $departureTime, $status, $jeepneyID);

        // Execute the query
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Data updated successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();

    } catch (Exception $e) {
        // Handle errors
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
    exit;
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}
?>
