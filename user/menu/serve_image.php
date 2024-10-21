<?php
include '../dbConnection/dbConnection.php';  

// Check if jeepney ID is passed in the query string
if (isset($_GET['id'])) {
    $jeepneyId = $_GET['id']; // Corrected to match the ID parameter

    // Fetch the image data from the database
    $sql = "SELECT jeep_image FROM jeepney WHERE jeepneyID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $jeepneyId); // Use the correct ID here
    $stmt->execute();
    $stmt->bind_result($imageData);
    $stmt->fetch();
    $stmt->close();

    if ($imageData) {
        // Set the appropriate content-type header (assuming JPEG images)
        header("Content-Type: image/jpeg"); // Use image/jpeg or image/png as appropriate
        echo $imageData; // Output the image data
    } else {
        echo "No image found.";
    }
}
?>
