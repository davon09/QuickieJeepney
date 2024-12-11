<?php
session_start();
include '../../dbConnection/dbConnection.php';

// Handle AJAX request to update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jeepneyID']) && isset($_POST['departure_time']) && isset($_POST['status'])) {
    $jeepneyID = $_POST['jeepneyID'];
    $status = $_POST['status'];
    $departureTime = $_POST['departure_time'];

    $dbStatus = ($status === 'Unavailable') ? 'Unavailable' : 'Available';
    
    // Example: If there's a 'status' column in the jeepney table:
    $sql = "UPDATE jeepney SET status = ?, departure_time = ? WHERE jeepneyID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssi", $dbStatus, $departureTime, $jeepneyID);
        $updated = $stmt->execute();
        $stmt->close();

        if ($updated) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Query preparation failed']);
    }

    exit; // Stop execution here after handling AJAX
}

// Normal page load logic
// // Check if user is logged in
// if (!isset($_SESSION['userID'])) {
//     header("Location: /QUICKIEJEEPNEY/index.php"); // Redirect to login page if not logged in
//     exit();
// }

// Fetch logged-in user's details
$userID = $_SESSION['userID'];
$sqlUser = "SELECT firstName, lastName, occupation, email, profile_image FROM user WHERE userID = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $userID);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
if ($resultUser->num_rows > 0) {
    $user = $resultUser->fetch_assoc();
    $fullName = $user['firstName'] . ' ' . $user['lastName'];
    $occupation = $user['occupation'];
    $profileImage = $user['profile_image'];
} else {
    $fullName = "Guest";
    $occupation = "N/A";
    $profileImage = null;
}

// Fetch jeepney details
$sqlJeepney = "
    SELECT 
        j.jeepneyID, 
        j.plateNumber, 
        j.jeep_image, 
        d.lastName,
        d.firstName,
        j.type,
        j.departure_time,
        j.status
    FROM jeepney j
    LEFT JOIN driver d ON j.driverID = d.driverID
";
$resultJeepney = $conn->query($sqlJeepney);

if (!$resultJeepney) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Vehicles</title>
    <link rel="stylesheet" href="manager_vehicles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  
</head>

<body>
    <header class="top-header">
        <div class="logo-section">
            <img src="../../images/qj-logo.png" alt="Quickie Jeepney Logo" class="logo-image">
        </div>
        <div class="user-card">
            <button class="logout-btn" id="logoutBtn">
                <h3><i class="fas fa-sign-out-alt"></i>Logout</h3>
            </button>
            <a href="../profile/profile.php" id="profileBtn">
                <span class="image">
                    <?php if ($profileImage): ?>
                        <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profile Image">
                    <?php else: ?>
                        <img src="../../images/profile.png" alt="Profile Image">
                    <?php endif; ?>
                </span>
                <div class="text header-text">
                    <h3><?= htmlspecialchars($fullName) ?></h3>
                    <p><?= htmlspecialchars($occupation) ?></p>
                </div>
            </a>
        </div>
    </header>

    <nav class="sidebar">
        <div class="menu-title">Menu</div>
        <hr>
        <ul class="menu-links">
            <li class="nav-link">
                <a href="../menu/manager_menu.php" class="sidebar-link">
                    <i class="fas fa-home sidebar-icon"></i>Home
                </a>
            </li>
            <li class="nav-link">
                <a href="../profile/manager_profile.php" class="sidebar-link">
                    <i class="fas fa-user sidebar-icon"></i>Profile
                </a>
            </li>
            <li class="nav-link active">
                <a href="../vehicles/manager_vehicles.php" class="sidebar-link">
                    <i class="fas fa-car sidebar-icon"></i>Vehicles
                </a>
            </li>
            <li class="nav-link">
                <a href="../booking/manager_booking.php" class="sidebar-link">
                    <i class="fas fa-calendar-alt sidebar-icon"></i>Booking Logs
                </a>
            </li>

            <li class="nav-link">
                <a href="../payment/manager_payment.php" class="sidebar-link">
                    <i class="fas fa-calendar-alt sidebar-icon"></i>Payment
                </a>
            </li>
            <li class="nav-link">
                <a href="../booking/manager_booking.php" class="sidebar-link">
                    <i class="fas fa-calendar-alt sidebar-icon"></i>Manage Passengers
                </a>
            </li>
        </ul>
    </nav>

    <section class="main-content">
        <div class="container">
            <h1>Jeepney Status</h1>
            <div class="controls">
                <div class="filter">
                    <label for="filter">Filter by:</label>
                    <select id="filter">
                        <option value="all">All</option>
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
                <input type="text" id="searchPlate" placeholder="Search Plate Number..." class="search-bar">
            </div>

            <div class="jeepney-grid" id="jeepneyGrid">
                <?php if ($resultJeepney->num_rows > 0): ?>
                    <?php while ($row = $resultJeepney->fetch_assoc()): 
                        $displayStatus = ($row['status'] === 'Unavailable') ? 'Unavailable' : 'Available';
                    ?>
                        <div class="jeepney-card" 
                            data-jeepneyid="<?= htmlspecialchars($row['jeepneyID']) ?>"
                            data-plate="<?= htmlspecialchars($row['plateNumber']) ?>"
                            data-driverName="<?= htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) ?>"
                            data-vehicletype="<?= htmlspecialchars($row['type']) ?>"
                            data-departuretime="<?= htmlspecialchars($row['departure_time']) ?>"
                            data-status="<?= strtolower($displayStatus) ?>"
                        >
                            <img src="data:image/jpeg;base64,<?= base64_encode($row['jeep_image']) ?>" alt="Jeepney Image">
                            
                            <div class="status <?= strtolower($displayStatus) ?>">
                                <?= htmlspecialchars(strtoupper($displayStatus)) ?>
                            </div>
                            <div class="details">
                                <p><strong>Plate:</strong> <?= htmlspecialchars($row['plateNumber']) ?></p>
                                <button class="edit-btn edit-btn-jeep-id_<?= htmlspecialchars($row['jeepneyID']) ?>">Edit</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No jeepneys available.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Modal Structure -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Edit Jeepney Details</h2>
            <form id="editForm">
                <p><span>Jeepney ID:</span> <span id="modalJeepneyID"></span></p>
                <p><span>Plate Number:</span> <span id="modalPlateNumber"></span></p>
                <p><span>Driver:</span> <span id="modalDriverName"></span></p>
                <p><span>Vehicle Type:</span> <span id="modalVehicleType"></span></p>
                <p><span>Departure Time:</span>
                    <input id="modalDepartureTime" type="time" name="modalDepartureTime">
                </p>
                <p><span>Status:</span>
                    <select id="modalStatus" name="status">
                        <option value="Available">Available</option>
                        <option value="Unavailable">Unavailable</option>
                    </select>
                </p>
                <button type="submit" class="update-btn">UPDATE</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const filterSelect = document.getElementById('filter');
            const searchInput = document.getElementById('searchPlate');
            const jeepneyGrid = document.getElementById('jeepneyGrid');

            const editModal = document.getElementById('editModal');
            const closeModalBtn = editModal.querySelector('.close-modal');
            const editForm = document.getElementById('editForm');

            const modalJeepneyID = document.getElementById('modalJeepneyID');
            const modalPlateNumber = document.getElementById('modalPlateNumber');
            const modalDriverName = document.getElementById('modalDriverName');
            const modalVehicleType = document.getElementById('modalVehicleType');
            const modalDepartureTime = document.getElementById('modalDepartureTime');
            const modalStatus = document.getElementById('modalStatus');

            filterSelect.addEventListener('change', applyFilters);
            searchInput.addEventListener('input', applyFilters);

            function applyFilters() {
                const filterValue = filterSelect.value.toLowerCase();
                const searchValue = searchInput.value.toLowerCase();

                const jeepneyCards = jeepneyGrid.querySelectorAll('.jeepney-card');

                jeepneyCards.forEach(card => {
                    const plate = card.getAttribute('data-plate').toLowerCase();
                    let status = card.getAttribute('data-status').toLowerCase();
                    let originalStatus = (status === 'unavailable') ? 'unavailable' : 'available';

                    const matchesFilter = filterValue === 'all' || originalStatus === filterValue;
                    const matchesSearch = plate.includes(searchValue);

                    if (matchesFilter && matchesSearch) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            jeepneyGrid.addEventListener('click', (e) => {
                if (e.target.classList.contains('edit-btn')) {
                    const card = e.target.closest('.jeepney-card');

                    const jeepneyID = card.getAttribute('data-jeepneyid');
                    const plateNumber = card.getAttribute('data-plate');
                    const driverName = card.getAttribute('data-drivername');
                    const vehicleType = card.getAttribute('data-vehicletype');
                    const departureTime = card.getAttribute('data-departuretime');
                    const status = card.getAttribute('data-status'); // 'available' or 'unavailable'

                    modalJeepneyID.textContent = jeepneyID;
                    modalPlateNumber.textContent = plateNumber;
                    modalDriverName.textContent = driverName || '';
                    modalVehicleType.textContent = vehicleType || '';
                    modalDepartureTime.value = departureTime || '';

                    modalStatus.value = status.charAt(0).toUpperCase() + status.slice(1);

                    editModal.style.display = 'flex';
                }
            });

            closeModalBtn.addEventListener('click', () => {
                editModal.style.display = 'none';
            });

            window.addEventListener('click', (e) => {
                if (e.target === editModal) {
                    editModal.style.display = 'none';
                }
            });

            editForm.addEventListener('submit', (e) => {
                e.preventDefault();

                const updatedStatus = modalStatus.value; // 'Available' or 'Unavailable'
                const jeepneyID = modalJeepneyID.textContent;
                const updatedDepartureTime = modalDepartureTime.value;

                const formData = new FormData();
                formData.append('jeepneyID', jeepneyID);
                formData.append('status', updatedStatus);
                formData.append('departure_time', updatedDepartureTime);

                for (let [key, value] of formData.entries()) {
                    console.log(key, value);
                }

                fetch('manager_vehicles.php', { // same file handling AJAX
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('Jeepney status updated!');
                        const card = document.querySelector(`.jeepney-card[data-jeepneyid="${jeepneyID}"]`);
                        if (card) {
                            card.setAttribute('data-status', updatedStatus.toLowerCase());
                            const statusDiv = card.querySelector('.status');
                            if (statusDiv) statusDiv.textContent = updatedStatus.toUpperCase();
                            statusDiv.classList.remove('available', 'unavailable');
                            statusDiv.classList.add(updatedStatus.toLowerCase());

                            card.setAttribute('data-departuretime', updatedDepartureTime);
                            const departureTimeInput = card.querySelector('#modalDepartureTime');
                            departureTimeInput.value = updatedDepartureTime;
                        }
                        editModal.style.display = 'none';
                    } else {
                        alert('Error updating jeepney status: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(err => console.error(err));
            });
        });
    </script>
</body>
</html>
