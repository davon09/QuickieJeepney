<?php
$host = "localhost";  // Replace with your database server host if different
$username = "root";   // Replace with your MySQL username
$password = "";       // Replace with your MySQL password
$database = "quickiejeepney"; // Your database name

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
