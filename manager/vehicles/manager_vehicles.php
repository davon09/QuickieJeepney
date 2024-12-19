<?php
session_start();
include '../../dbConnection/dbConnection.php';

// Normal page load logic
// // Check if user is logged in
// if (!isset($_SESSION['userID'])) {
//     header("Location: /QUICKIEJEEPNEY/index.php"); // Redirect to login page if not logged in
//     exit();
// }

// Fetch logged-in user's details
// $userID = $_SESSION['userID'];
// todo
$userID = 8; // !TEMP ONLY
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
        <?php
            $choices = [
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday',
            ];
        ?>
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Edit Jeepney Details</h2>
            <form id="editForm">
                <p><span>Jeepney ID:</span> <span id="modalJeepneyID"></span></p>
                <p><span>Plate Number:</span> <span id="modalPlateNumber"></span></p>
                <p><span>Driver:</span> <span id="modalDriverName"></span></p>
                <p><span>Vehicle Type:</span> <span id="modalVehicleType"></span></p>
                <p><span>Departure Schedule:</span>
                    <!-- <input id="modalDepartureTime" type="time" name="modalDepartureTime"> -->
                    <p id="modalDepartureTime"></p>
                </p>

                <!-- hidden -->
                <div style="display:flex; gap:2px; flex-wrap:wrap; height: 0px;"> <!-- remove 'height: 0px' to show -->
                    <?php foreach ($choices as $index => $choice): ?>
                        <label class="schedule-label" for="checkbox-<?php echo $index; ?>">
                            <input type="checkbox" 
                                id="checkbox-<?php echo $index; ?>" 
                                type="checkbox"
                                data-target="content-<?php echo $index; ?>"
                                class="toggle-checkbox"/>
                            <?php echo $choice; ?>
                        </label>
                        <br/>
                    <?php endforeach; ?>
                </div>
                <script>
                    // Wait for DOM to load
                    document.addEventListener('DOMContentLoaded', () => {
                        const checkboxes = document.querySelectorAll('.toggle-checkbox');
                        
                        checkboxes.forEach(checkbox => {
                            checkbox.click(); // Simulate a click on each checkbox
                        });
                    });

                    // Get all labels that act like buttons
                    const labels = document.querySelectorAll('.schedule-label');

                    labels.forEach(label => {
                        const checkbox = label.querySelector('input[type="checkbox"]'); // Get the checkbox within the label

                        // Add event listener to the checkbox
                        checkbox.addEventListener('change', function() {
                            if (this.checked) {
                                label.classList.add('checked'); // Add "checked" class when checkbox is checked
                            } else {
                                label.classList.remove('checked'); // Remove "checked" class when unchecked
                            }
                        });
                    });
                </script>
                
                <p>
                    <?php foreach ($choices as $index => $choice): ?>
                        <div id="content-<?php echo $index; ?>" class="toggle-content schedule-for-<?php echo $choice; ?>">
                            <p style="margin: 0 0 .5rem 0;"><b><?php echo $choice; ?> departure schedule</b></p>
                        </div>
                    <?php endforeach; ?>
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

            var cardEditBtn = document.querySelectorAll('.edit-btn');
            cardEditBtn.forEach(editBtn => {

            });

            jeepneyGrid.addEventListener('click', (e) => {
                if (e.target.classList.contains('edit-btn')) {

                    // remove contents
                    var scheduleContainers = document.querySelectorAll('[class*="schedule-for-"]');
                    scheduleContainers.forEach((element, index) => {
                        const firstChild = element.firstElementChild; // this contains the header
                        // Remove all other child nodes except the first child
                        while (element.lastElementChild !== firstChild) {
                            element.removeChild(element.lastElementChild);
                        }
                    });

                    const card = e.target.closest('.jeepney-card');

                    const jeepneyID = card.getAttribute('data-jeepneyid');
                    const plateNumber = card.getAttribute('data-plate');
                    const driverName = card.getAttribute('data-drivername');
                    const vehicleType = card.getAttribute('data-vehicletype');
                    const departureTime = card.getAttribute('data-departuretime');
                    const status = card.getAttribute('data-status'); // 'available' or 'unavailable'

                    var departureTimeJSON = JSON.parse(departureTime);

                    Object.keys(departureTimeJSON).forEach(key => {
                        var timeArr = departureTimeJSON[key]; // ex. ["14:00", "15:00"]
                        var timeInputContainer = document.querySelector('.schedule-for-' + key);

                        timeArr.forEach(sched => {
                            addTimeInput(timeInputContainer, key, sched); // key contains day of week, no initial value
                        });
                    });
                    
                    var keys = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                    keys.forEach(function(key) {
                        var timeInputContainer = document.querySelector('.schedule-for-' + key);

                        const addTimeBtn = document.createElement('button');
                        addTimeBtn.setAttribute('type', 'button');
                        addTimeBtn.style = "background:rgb(45, 160, 66); color: white; border: 0px; padding: .25rem";
                        addTimeBtn.textContent = "Add Time";

                        const container = document.createElement('div');
                        container.appendChild(addTimeBtn);

                        addTimeBtn.addEventListener('click', () => {
                            addTimeInput(timeInputContainer, key, null); // key contains day of week, no initial value
                        });

                        timeInputContainer.appendChild(container);
                    });

                    modalJeepneyID.textContent = jeepneyID;
                    modalPlateNumber.textContent = plateNumber;
                    modalDriverName.textContent = driverName || '';
                    modalVehicleType.textContent = vehicleType || '';
                    // modalDepartureTime.textContent = departureTime || '';

                    modalStatus.value = status.charAt(0).toUpperCase() + status.slice(1);

                    editModal.style.display = 'flex';
                }
            });

            function addTimeInput(timeInputContainer, key, inputValue) {
                const timeInputLabel = document.createElement('p');
                timeInputLabel.style = "height: 1.5rem; margin: 0";
                timeInputLabel.textContent = "Departure Time: ";

                const timeInput = document.createElement('input');
                timeInput.classList.add(key + "-sched");
                timeInput.classList.add("schedule-input");
                timeInput.setAttribute("type", "time");
                timeInput.style = "padding: 0 .5rem; height: 1.5rem";
                // set the input value if argument is not empty
                if (inputValue) {
                    timeInput.value = inputValue; // Set the input value
                } else {
                    timeInput.value = ''; // Clear the input value if inputValue is empty
                }

                const removeTimeBtn = document.createElement('button');
                removeTimeBtn.setAttribute('type', 'button');
                removeTimeBtn.textContent = "remove";
                removeTimeBtn.style = "margin-left: .5rem; height: 1.5rem; padding: 0 .5rem; background:rgb(169, 32, 32); color: white; border: 0px";

                const container = document.createElement('div');
                container.classList.add("time-input-container");
                container.appendChild(timeInputLabel);
                container.appendChild(timeInput);
                container.appendChild(removeTimeBtn);

                container.style = "display: flex; gap: 1rem; margin: .25rem 0";

                timeInputContainer.appendChild(container);

                removeTimeBtn.addEventListener('click', () => {
                    const parentDiv = removeTimeBtn.closest('.time-input-container');

                    if (parentDiv) {
                        parentDiv.remove(); // Remove the parent div
                    }
                });
            }

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

                // grab the values for the schedule
                let schedule = {};
                const days = <?php echo json_encode($choices); ?>; // Mon to Sun
                days.forEach(day => {
                    // Select the container for each day's schedule
                    const dayContainer = document.querySelector(`.schedule-for-${day}`);
                    
                    // Check if the container exists and has input elements
                    if (dayContainer) {
                        const inputs = dayContainer.querySelectorAll("input");
                        
                        // Collect input values into an array
                        const values = Array.from(inputs).map(input => input.value).filter(value => value.trim() !== "");
                        
                        // If values are not empty, add them to the schedule object
                        if (values.length > 0) {
                            schedule[day] = values;
                        }
                    }
                });

                const formData = new FormData();
                formData.append('jeepneyID', jeepneyID);
                formData.append('status', updatedStatus);
                formData.append('departure_time', JSON.stringify(schedule));

                // // view the values of the payload
                // for (let [key, value] of formData.entries()) {
                //     console.log(key, value);
                // }

                fetch('update_vehicle.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('Jeepney status updated!');
                        
                        location.reload();
                    } else {
                        alert('Error updating jeepney status: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(err => console.error(err));
            });
        });

        document.querySelectorAll('input[type=checkbox][data-target]').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                var targetDiv = document.getElementById(this.getAttribute('data-target'));
                if (this.checked) {
                    targetDiv.style.display = 'block';
                } else {
                    targetDiv.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
