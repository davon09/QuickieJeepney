<?php
session_start();

if (isset($_SESSION['userID'])) {
    // Destroy the session if the user is logged in
    session_destroy();
    echo 'logged_out';
} else {
    echo 'no_user_logged_in';
}
?>
