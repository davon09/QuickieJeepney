<?php
session_start();

// Simulated users for login validation
$users = [
    ['username' => 'student', 'password' => 'pass123'],
    ['username' => 'faculty', 'password' => 'pass456'],
    ['username' => 'employee', 'password' => 'pass789'],
];

// Get the form data
$username = $_POST['username'];
$password = $_POST['password'];

// Validate the credentials
$valid = false;
foreach ($users as $user) {
    if ($user['username'] == $username && $user['password'] == $password) {
        $valid = true;
        break;
    }
}

if ($valid) {
    // Store user info in the session
    $_SESSION['username'] = $username;
    header('Location: index.html');
} else {
    // Redirect back to login page with an error message
    header('Location: login.html?error=1');
}
?>
