<?php
include '../../dbConnection/dbConnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode the JSON input
    $input = json_decode(file_get_contents("php://input"), true);

    if (!empty($input['announcement_ids'])) {
        // Sanitize IDs
        $ids = implode(',', array_map('intval', $input['announcement_ids']));

        // Delete query
        $query = "DELETE FROM announcements WHERE announcementID IN ($ids)";
        if ($conn->query($query)) {
            echo json_encode(["status" => "success", "message" => "Announcements removed successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to remove announcements."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "No announcements selected."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

$conn->close();