<?php
session_start();
include '../../dbConnection/dbConnection.php';

// Check if user is logged in (ensure the userID is in the session)
//if (!isset($_SESSION['userID'])) {
//    header("Location: /QuickieJeepney/index.php"); // Redirect to login page if not logged in
//    exit();
//}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch payment data
$sqlPayment = "SELECT paymentID, amount, paymentMethod, paymentStatus FROM payment";
$resultPayment = $conn->query($sqlPayment);

if (!$resultPayment) {
    die("Payment Query Failed: " . $conn->error);
}

// Fetch user data
$sqlUser = "SELECT userID, firstName, lastName FROM user";
$resultUser = $conn->query($sqlUser);

if (!$resultUser) {
    die("User Query Failed: " . $conn->error);
}

$userData = [];
while ($row = $resultUser->fetch_assoc()) {
    $userData[$row['userID']] = $row;
}

$paymentHTML = '';
if ($resultPayment->num_rows > 0) {
    while ($paymentRow = $resultPayment->fetch_assoc()) {
        $userID = $paymentRow['paymentID'];
        $fullName = isset($userData[$userID])
            ? $userData[$userID]['firstName'] . ' ' . $userData[$userID]['lastName']
            : 'Unknown User';

        $paymentHTML .= "
            <tr>
                <td>{$fullName}</td>
                <td>₱" . number_format($paymentRow['amount'], 2) . "</td>
                <td>{$paymentRow['paymentMethod']}</td>
                <td>{$paymentRow['paymentStatus']}</td>
                <td>
                    <div class='dropdown'>
                        <button class='action-btn'>...</button>
                        <div class='dropdown-content'>
                            <a href='../users/view_user.php?userID={$userID}'>View User</a>
                            <a href='../users/ban_user.php?userID={$userID}'>Ban User</a>
                            <a href='../users/block_user.php?userID={$userID}'>Block User</a>
                        </div>
                    </div>
                </td>
            </tr>
        ";
    }
} else {
    $paymentHTML = "<tr><td colspan='5'>No payments available.</td></tr>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments</title>
    <link rel="stylesheet" href="manager_payment.css">
</head>
<body>
    <!-- Header -->
    <header class="top-header">
        <div class="logo-section">
            <img src="../../images/qj-logo.png" alt="Quickie Jeepney Logo" class="logo-image">
        </div>
        <div class="user-card">
            <button class="logout-btn" id="logoutBtn">
                <h3><i class="fas fa-sign-out-alt"></i>Logout</h3>
            </button>
            <div class="text header-text">
                <h3><?php echo isset($_SESSION['userName']) ? $_SESSION['userName'] : "Guest"; ?></h3>
                <p><?php echo isset($_SESSION['userRole']) ? $_SESSION['userRole'] : "N/A"; ?></p>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="menu-title">Menu</div>
        <hr>
        <ul class="menu-links">
            <li class="nav-link">
                <a href="../menu/manager_menu.php" class="sidebar-link">
                    <i class="fas fa-home sidebar-icon"></i> Home
                </a>
            </li>
            <li class="nav-link">
                <a href="../profile/manager_profile.php" class="sidebar-link">
                    <i class="fas fa-user sidebar-icon"></i> Profile
                </a>
            </li>
            <li class="nav-link">
                <a href="../passenger/manage_passenger.php" class="sidebar-link">
                    <i class="fas fa-users sidebar-icon"></i> Manage Passenger
                </a>
            </li>
            <li class="nav-link">
                <a href="../status/jeepney_status.php" class="sidebar-link">
                    <i class="fas fa-clipboard sidebar-icon"></i> Jeepney Status
                </a>
            </li>
            <li class="nav-link">
                <a href="../payment/manager_payment.php" class="sidebar-link">
                    <i class="fas fa-search sidebar-icon"></i> Payments
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="payments">
            <h1>Payments</h1>
            <table class="payments-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $paymentHTML; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script src="manager_payment.js"></script>
</body>
</html>
