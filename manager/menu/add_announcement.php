<?php
// Database connection
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $announcementName = $_POST['announcementName'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $validUntil = $_POST['validUntil'];

    // Insert data into announcements table
    $sql = "INSERT INTO announcements (announcementName, description, date, validUntil) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $announcementName, $description, $date, $validUntil);

    if ($stmt->execute()) {
        echo "Announcement added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
