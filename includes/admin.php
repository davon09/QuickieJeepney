<?php
session_start();
if (isset($_SESSION["admin"])) {
    // echo 'Login Successful';
} else {
    echo '<script>
    window.location.href="index.php";
    </script>';
include("logout.php");
}

require("db.php");
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
