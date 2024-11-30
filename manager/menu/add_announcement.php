<?php
// Set headers to return JSON response
header("Content-Type: application/json");

// Database connection
include '../../dbConnection/dbConnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $announcementName = $_POST['announcementName'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? '';
    $validUntil = $_POST['validUntil'] ?? '';

    // Validate required fields
    if (empty($announcementName) || empty($description) || empty($date) || empty($validUntil)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'All fields are required.',
        ]);
        exit;
    }

    // Insert into the database
    $stmt = $conn->prepare("INSERT INTO announcements (announcementName, description, date, validUntil, createdAt) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param('ssss', $announcementName, $description, $date, $validUntil);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Announcement added successfully.',
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add announcement.',
        ]);
    }
    $stmt->close();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.',
    ]);
}
$conn->close();
?>