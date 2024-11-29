<?php
include '../../dbConnection/dbConnection.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $routeName = isset($_POST['routeName']) ? trim($_POST['routeName']) : '';
    $startPoint = isset($_POST['startPoint']) ? trim($_POST['startPoint']) : '';
    $startLat = isset($_POST['startLat']) ? floatval($_POST['startLat']) : null;
    $startLng = isset($_POST['startLng']) ? floatval($_POST['startLng']) : null;
    $endPoint = isset($_POST['endPoint']) ? trim($_POST['endPoint']) : '';
    $endLat = isset($_POST['endLat']) ? floatval($_POST['endLat']) : null;
    $endLng = isset($_POST['endLng']) ? floatval($_POST['endLng']) : null;

    // Validate required fields
    if (empty($routeName) || empty($startPoint) || empty($endPoint) || is_null($startLat) || is_null($startLng) || is_null($endLat) || is_null($endLng)) {
        echo "<script>alert('All fields are required!'); window.history.back();</script>";
        exit;
    }

    // Prepare SQL statement to insert data into the transportation_routes table
    $sql = "INSERT INTO transportation_routes (routeName, startPoint, startLat, startLng, endPoint, endLat, endLng)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    // Use prepared statements to prevent SQL injection
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssddssd", $routeName, $startPoint, $startLat, $startLng, $endPoint, $endLat, $endLng);

        if ($stmt->execute()) {
            // Redirect back with success message
            echo "<script>alert('Route added successfully!'); window.location.href = 'manage_routes.php';</script>";
        } else {
            // Handle SQL error
            echo "<script>alert('Error adding route: " . $stmt->error . "'); window.history.back();</script>";
        }

        $stmt->close();
    } else {
        // Handle SQL preparation error
        echo "<script>alert('Error preparing SQL statement: " . $conn->error . "'); window.history.back();</script>";
    }

    // Close the database connection
    $conn->close();
} else {
    // Redirect if the request method is not POST
    header("Location: manager_menu.php");
    exit;
}

?>