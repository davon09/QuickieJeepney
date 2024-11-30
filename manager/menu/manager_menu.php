<?php
session_start();
include '../../dbConnection/dbConnection.php';  

// Check if user is logged in (ensure the userID is in the session)
if (!isset($_SESSION['userID'])) {
    header("Location: /QuickieJeepney/index.php"); // Redirect to login page if not logged in
    exit();
}

date_default_timezone_set('Asia/Manila');
$currentDate = date('Y-m-d'); // Get today's date in 'YYYY-MM-DD' format
error_log("Current Date: " . $currentDate);
echo date('Y-m-d');

// Fetch logged-in user's details including the occupation
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

$userDetailsHTML = '
    <span class="name">' . $fullName . '</span>
    <br>
    <span class="occupation">' . $occupation . '</span>
    <br>
';

// Fetch transportation routes from the database
$sqlRoutes = "SELECT routeName, startPoint, endPoint, startLat, startLng, endLat, endLng FROM transportation_routes";
$resultRoutes = $conn->query($sqlRoutes);

// Fetch announcements that are still valid (validUntil >= today's date)
$sql = "SELECT announcementName, description, 
               DATE_FORMAT(date, '%d/%m/%Y') AS formattedDate, 
               DATE_FORMAT(validUntil, '%d/%m/%Y') AS formattedValidUntil 
        FROM announcements 
        WHERE validUntil >= ? 
        ORDER BY date DESC";

$stmtAnnouncements = $conn->prepare($sql);
if ($stmtAnnouncements === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmtAnnouncements->bind_param("s", $currentDate);
$stmtAnnouncements->execute();
$result = $stmtAnnouncements->get_result();

$announcementsHTML = "";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $announcementsHTML .= "
        <li class='announcement-item'>
            <strong>{$row['announcementName']}</strong><br>
            {$row['description']}<br>
            <small><em>Date: {$row['formattedDate']}, Valid Until: {$row['formattedValidUntil']}</em></small>
        </li>";
    }
} else {
    $announcementsHTML = "<li class='announcement-item'>No announcements available.</li>";
}

// Fetch available jeepneys
$jeepneysHTML = '';
$sqlJeepneys = "SELECT jeepneyID, plateNumber, capacity, occupied, route, type, departure_time FROM jeepney";
$resultJeepneys = $conn->query($sqlJeepneys);

if ($resultJeepneys->num_rows > 0) {
    while ($jeepney = $resultJeepneys->fetch_assoc()) {
        // Format the departure time into 12-hour AM/PM format
        $formattedDepartureTime = date('h:i A', strtotime($jeepney['departure_time'])); // Convert and format to 12-hour format

        $jeepneysHTML .= '
            <tr>
                <td>' . $jeepney['plateNumber'] . '</td>
                <td>' . $jeepney['capacity'] . '</td>
                <td>' . $jeepney['occupied'] . '</td>
                <td>' . $jeepney['route'] . '</td>
                <td>' . $jeepney['type'] . '</td>
                <td>' . $formattedDepartureTime . '</td> <!-- Displaying 12-hour formatted time -->
            </tr>
        ';
    }
} else {
    $jeepneysHTML = '
        <tr>
            <td colspan="7">No available jeepneys.</td>
        </tr>
    ';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Quickie Jeepney</title>
    <link rel="stylesheet" href="manager_menu.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="manager_menu.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>
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
                    <?php
                    // Check if profile image exists and display it, otherwise show default
                    if ($profileImage) {
                        // Display the actual profile image from the database
                        echo '<img src="' . htmlspecialchars($profileImage) . '" alt="Profile Image">';
                    } else {
                        // Display default profile image if no image is found
                        echo '<img src="../../images/profile.png" alt="Profile Image">';
                    }
                    ?>
                </span>
                <div class="text header-text">
                    <h3><?= $fullName; ?></h3>
                    <p><?= $occupation; ?></p>
                </div>
            </a>
        </div>
    </header>

    <nav class="sidebar">
        <div class="menu-title">Menu</div> 
        <hr>
        <ul class="menu-links">
            <li class="nav-link active">
                <a href="../menu/manager_menu.php" class="sidebar-link">
                    <i class="fas fa-home sidebar-icon" class="sidebar-icon"></i>Home
                </a>
            </li>
            <li class="nav-link">
                <a href="../profile/manager_profile.php" class="sidebar-link">
                    <i class="fas fa-user sidebar-icon" class="sidebar-icon"></i>Profile
                </a>
            </li>
            <li class="nav-link">
                <a href="../roles/manager_roles.php" class="sidebar-link">
                    <i class="fas fa-user-shield sidebar-icon" class="sidebar-icon"></i>User Roles
                </a>
            </li>
            <li class="nav-link">
                <a href="../vehicles/manager_vehicles.php" class="sidebar-link">
                    <i class="fas fa-car sidebar-icon" class="sidebar-icon"></i>Vehicles
                </a>
            </li>
            <li class="nav-link">
                <a href="../booking/manager_booking.php" class="sidebar-link">
                    <i class="fas fa-calendar-alt sidebar-icon" class="sidebar-icon"></i>Booking Logs
                </a>
            </li>
        </ul>
    </nav>

    <section class="main-content">
        <!-- Greeting Section -->
        <div class="greeting">
            <h1>Hi, Manager!</h1>
            <p>Today is <?php echo date('l, d F Y'); ?></p>
        </div>

        <div class="dashboard-content">
            <!-- Routes Section -->
            <div class="routes-container">
                <div class="map-container" style="position: relative;">
                    <h2>Routes</h2>
                    <div class="add-route-container">
                        <button type="button" class="add-route-btn">Add Route</button>
                    </div>
                    <div id="map" style="height: 500px; margin-top: 25px;"></div>
                </div>
                <div class="routes-list-container">
                    <h3>Route List</h3>
                    <div class="routes-list">
                        <?php if ($resultRoutes->num_rows > 0): ?>
                            <?php while ($route = $resultRoutes->fetch_assoc()): ?>
                                <div class="route-card">
                                    <p class="route-name"><?php echo htmlspecialchars($route['routeName']); ?></p>
                                    <hr class="route-divider">
                                    <p><strong>From:</strong> <?php echo htmlspecialchars($route['startPoint']); ?><br>
                                    <strong>To:</strong> <?php echo htmlspecialchars($route['endPoint']); ?></p>
                                    <button class="view-route-btn"
                                            data-start-lat="<?php echo $route['startLat']; ?>"
                                            data-start-lng="<?php echo $route['startLng']; ?>"
                                            data-end-lat="<?php echo $route['endLat']; ?>"
                                            data-end-lng="<?php echo $route['endLng']; ?>">
                                        View on Map
                                    </button>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No routes available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Announcements and Jeepney Section -->
            <div class="bottom-container">
                <div class="announcements">
                    <div class="announcements-header">
                        <h2>Announcements</h2>
                        <button class="add-btn add-announcement-btn">Add</button>
                    </div>
                    <ul class="announcement-list">
                        <?php echo $announcementsHTML; ?>
                    </ul>
                </div>
                <div class="available-jeepney">
                    <h2>Available Jeepney</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Plate Number</th>
                                <th>Capacity</th>
                                <th>Occupied</th>
                                <th>Route</th>
                                <th>Type</th>
                                <th>Departure Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo $jeepneysHTML; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    
    <div class="modal-overlay"></div>
    <div class="modal">
        <div class="modal-header">
            <h2>Add New Route</h2>
            <span class="close-btn">&times;</span>
        </div>
        <div class="modal-body">
            <form id="add-route-form" method="POST" action="add_route.php">
                <div class="form-group">
                    <label for="routeName">Route Name:</label>
                    <input type="text" id="routeName" name="routeName" placeholder="Enter Route Name" required>
                </div>
                <div class="form-group">
                    <label for="startPoint">Start Point:</label>
                    <input type="text" id="startPoint" name="startPoint" placeholder="Enter Start Point (Optional)" readonly>
                </div>
                <div class="coordinates-section">
                    <div class="form-group">
                        <label for="startLat">Start Latitude:</label>
                        <input type="text" id="startLat" name="startLat" placeholder="Start Latitude" readonly>
                    </div>
                    <div class="form-group">
                        <label for="startLng">Start Longitude:</label>
                        <input type="text" id="startLng" name="startLng" placeholder="Start Longitude" readonly>
                    </div>
                </div>
                <div class="map-section">
                    <h4>Select Start Point</h4>
                    <div id="start-point-map" style="height: 300px;"></div>
                    <h4>Select End Point</h4>
                    <div id="end-point-map" style="height: 300px; margin-top: 20px;"></div>
                </div>
                <div class="form-group">
                    <label for="endPoint">End Point:</label>
                    <input type="text" id="endPoint" name="endPoint" placeholder="Enter End Point (Optional)" readonly>
                </div>
                <div class="coordinates-section">
                    <div class="form-group">
                        <label for="endLat">End Latitude:</label>
                        <input type="text" id="endLat" name="endLat" placeholder="End Latitude" readonly>
                    </div>
                    <div class="form-group">
                        <label for="endLng">End Longitude:</label>
                        <input type="text" id="endLng" name="endLng" placeholder="End Longitude" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="submit-route-btn">Add Route</button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-announcement">
        <div class="modal-header">
            <h2>Add New Announcement</h2>
            <span class="close-btn-announcement">&times;</span>
        </div>
        <div class="modal-body">
            <form id="add-announcement-form" method="POST" action="add_announcement.php">
                <div class="form-group">
                    <label for="announcementName">Announcement Name:</label>
                    <input type="text" id="announcementName" name="announcementName" placeholder="Enter Announcement Name" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" placeholder="Enter Description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required>
                </div>
                <div class="form-group">
                    <label for="validUntil">Valid Until:</label>
                    <input type="date" id="validUntil" name="validUntil" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="submit-announcement-btn">Add Announcement</button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-overlay-announcement"></div>
</body>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Main map initialization
        const map = L.map("map").setView([16.381371, 120.594478], 14);

        // Add OpenStreetMap tiles to the main map
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 20,
            attribution: "© OpenStreetMap contributors",
        }).addTo(map);

        // View on Map buttons functionality
        document.querySelectorAll(".view-route-btn").forEach((button) => {
            button.addEventListener("click", () => {
                const startLat = parseFloat(button.getAttribute("data-start-lat"));
                const startLng = parseFloat(button.getAttribute("data-start-lng"));
                const endLat = parseFloat(button.getAttribute("data-end-lat"));
                const endLng = parseFloat(button.getAttribute("data-end-lng"));

                // Fit map bounds to show the route
                map.fitBounds([
                    [startLat, startLng],
                    [endLat, endLng],
                ]);

                // Clear existing markers and polylines
                map.eachLayer((layer) => {
                    if (layer instanceof L.Marker || layer instanceof L.Polyline) {
                        map.removeLayer(layer);
                    }
                });

                // Add markers for start and end points
                L.marker([startLat, startLng])
                    .addTo(map)
                    .bindPopup(`<strong>Start Point:</strong> ${button.parentElement.querySelector(".route-name").innerText}`);
                L.marker([endLat, endLng])
                    .addTo(map)
                    .bindPopup(`<strong>End Point:</strong> ${button.parentElement.querySelector(".route-name").innerText}`);

                // Fetch the route from OSRM
                const osrmApiUrl = `https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${endLng},${endLat}?overview=full&geometries=geojson`;

                fetch(osrmApiUrl)
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error(`Error fetching route: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then((data) => {
                        if (data.routes && data.routes.length > 0) {
                            const routeCoordinates = data.routes[0].geometry.coordinates.map((coord) => [
                                coord[1],
                                coord[0],
                            ]);

                            L.polyline(routeCoordinates, {
                                color: "blue",
                                weight: 4,
                                opacity: 0.7,
                            }).addTo(map);
                        } else {
                            console.error("No route found in response data");
                        }
                    })
                    .catch((error) => console.error("Error fetching route:", error));
            });
        });

        // Modal functionality
        const modal = document.querySelector(".modal");
        const modalOverlay = document.querySelector(".modal-overlay");
        const addRouteButton = document.querySelector(".routes-container .add-route-btn");
        const closeButton = document.querySelector(".modal .close-btn");

        // Function to open the modal
        function openModal() {
            modal.classList.add("active");
            modalOverlay.classList.add("active");

            // Fix map rendering issues
            setTimeout(() => {
                startPointMap.invalidateSize();
                endPointMap.invalidateSize();
            }, 200); // Slight delay to ensure modal is fully visible
        }

        // Function to close the modal
        function closeModal() {
            modal.classList.remove("active");
            modalOverlay.classList.remove("active");
        }

        addRouteButton.addEventListener("click", openModal);
        closeButton.addEventListener("click", closeModal);
        modalOverlay.addEventListener("click", closeModal);

        // Initialize maps for Start Point and End Point
        const startPointMap = L.map("start-point-map").setView([16.381371, 120.594478], 14);
        const endPointMap = L.map("end-point-map").setView([16.381371, 120.594478], 14);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 20,
            attribution: "© OpenStreetMap contributors",
        }).addTo(startPointMap);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 20,
            attribution: "© OpenStreetMap contributors",
        }).addTo(endPointMap);

        // Reverse geocoding function
        async function fetchLocationName(lat, lng) {
            const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`;
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error("Failed to fetch location name");
                }
                const data = await response.json();
                return data.display_name || "Unknown Location";
            } catch (error) {
                console.error("Error fetching location name:", error);
                return "Unknown Location";
            }
        }

        // Handle clicks for Start Point Map
        startPointMap.on("click", async (e) => {
            const { lat, lng } = e.latlng;

            if (startMarker) {
                startPointMap.removeLayer(startMarker);
            }
            startMarker = L.marker([lat, lng]).addTo(startPointMap).bindPopup("Start Point").openPopup();

            const locationName = await fetchLocationName(lat, lng);

            document.getElementById("startLat").value = lat;
            document.getElementById("startLng").value = lng;
            document.getElementById("startPoint").value = locationName;
        });

        // Handle clicks for End Point Map
        endPointMap.on("click", async (e) => {
            const { lat, lng } = e.latlng;

            if (endMarker) {
                endPointMap.removeLayer(endMarker);
            }
            endMarker = L.marker([lat, lng]).addTo(endPointMap).bindPopup("End Point").openPopup();

            const locationName = await fetchLocationName(lat, lng);

            document.getElementById("endLat").value = lat;
            document.getElementById("endLng").value = lng;
            document.getElementById("endPoint").value = locationName;
        });

        // Variables to store markers
        let startMarker, endMarker;
    });
   
    document.addEventListener("DOMContentLoaded", () => {
        // Modal elements
        const modalAnnouncement = document.querySelector(".modal-announcement");
        const modalOverlayAnnouncement = document.querySelector(".modal-overlay-announcement");
        const addAnnouncementButton = document.querySelector(".add-btn"); // Button to open the modal
        const closeAnnouncementButton = document.querySelector(".close-btn-announcement"); // Close button inside the modal
        const formAnnouncement = document.getElementById("add-announcement-form");
        const announcementList = document.querySelector(".announcement-list"); // Announcement list container

        // Function to open the modal
        function openAnnouncementModal() {
            modalAnnouncement.classList.add("active");
            modalOverlayAnnouncement.classList.add("active");
        }

        // Function to close the modal
        function closeAnnouncementModal() {
            modalAnnouncement.classList.remove("active");
            modalOverlayAnnouncement.classList.remove("active");
        }

        // Function to fetch and update the announcement list
        function refreshAnnouncements() {
            // Assuming the valid announcements are already filtered in the manager_menu.php page
            fetch("manager_menu.php") // Fetch the page and process only the valid announcements
                .then((response) => {
                    if (!response.ok) {
                        throw new Error("Failed to fetch announcements");
                    }
                    return response.text();  // Get the raw HTML response
                })
                .then((data) => {
                    // Now parse the response to get the announcement list
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(data, "text/html");

                    // Assuming the announcements are inside a container with class `.announcement-list`
                    const updatedAnnouncements = doc.querySelector(".announcement-list");

                    // If there are no announcements, show a message
                    if (!updatedAnnouncements || updatedAnnouncements.innerHTML === "") {
                        announcementList.innerHTML = "<li>No announcements available.</li>";
                    } else {
                        // Otherwise, update the announcements
                        announcementList.innerHTML = updatedAnnouncements.innerHTML;
                    }
                })
                .catch((error) => {
                    console.error("Error fetching announcements:", error);
                    announcementList.innerHTML = "<li>Error loading announcements.</li>";
                });
        }

        // Event listener to open the modal when the "Add" button is clicked
        addAnnouncementButton.addEventListener("click", openAnnouncementModal);

        // Event listener to close the modal when the close button is clicked
        closeAnnouncementButton.addEventListener("click", closeAnnouncementModal);

        // Event listener to close the modal when clicking outside of it
        modalOverlayAnnouncement.addEventListener("click", closeAnnouncementModal);

        // Handle form submission
        formAnnouncement.addEventListener("submit", (e) => {
            e.preventDefault(); // Prevent default form submission

            const formData = new FormData(formAnnouncement);

            const submitButton = formAnnouncement.querySelector(".submit-announcement-btn");
            submitButton.disabled = true;
            submitButton.textContent = "Submitting...";

            fetch("add_announcement.php", {
                method: "POST",
                body: formData,
            })
                .then((response) => response.json())
                .then((data) => {
                    console.log(data); // Log the response for debugging
                    if (data.status === "success") {
                        alert(data.message);
                        formAnnouncement.reset();
                        closeAnnouncementModal();

                        // Refresh the announcement list
                        refreshAnnouncements();
                    } else {
                        alert(data.message || "Failed to add announcement");
                    }
                })
                .catch((error) => {
                    console.error("Error adding announcement:", error);
                    alert("Error adding announcement: " + error.message);
                })
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.textContent = "Add Announcement";
                });
        });

        // Fetch announcements on page load
        refreshAnnouncements();
    });
</script>

</html>