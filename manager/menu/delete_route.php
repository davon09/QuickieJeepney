<?php
include '../../dbConnection/dbConnection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $routeID = intval($_POST['routeID']);

    if ($routeID) {
        $stmt = $conn->prepare("DELETE FROM transportation_routes WHERE routeID = ?");
        $stmt->bind_param("i", $routeID);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'error';
        }
    } else {
        echo 'invalid';
    }
}
?>