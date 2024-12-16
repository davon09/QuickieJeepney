<?php
session_start();
include '../../dbConnection/dbConnection.php';  

// // Check if user is logged in (ensure the userID is in the session)
// if (!isset($_SESSION['userID'])) {
//     header("Location: /QuickieJeepney/index.php"); // Redirect to login page if not logged in
//     exit();
// }

// Fetch logged-in user's details including the occupation
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

$userDetailsHTML = '
    <span class="name">' . $fullName . '</span>
    <br>
    <span class="occupation">' . $occupation . '</span>
    <br>
';

$sql = "
    SELECT 
        b.bookingID,
        CONCAT(u.lastName, ', ', u.firstName) AS customerName,  
        j.plateNumber AS jeepneyPlate,
        CONCAT(d.firstName, ' ', d.lastName) AS driverName,
        j.route,
        b.status,
        b.departure,
        p.paymentID,
        p.paymentStatus,
        p.paymentMethod,
        p.amount
    FROM 
        booking b
    JOIN 
        payment p ON p.bookingID = b.bookingID
    JOIN
        jeepney j ON b.jeepneyID = j.jeepneyID
    JOIN 
        driver d ON j.driverID = d.driverID
    JOIN 
        user u ON b.userID = u.userID  
    WHERE 
        b.status IN ('available', 'departed', 'unavailable')
    ORDER BY 
        b.bookingID DESC;
";
$result = $conn->query($sql);

function generateHistogram() {
    // Include database connection
    include '../../dbConnection/dbConnection.php';

    // Query the database to get counts of each status
    $sql = "SELECT status, COUNT(*) as count FROM booking GROUP BY status";
    $result = $conn->query($sql);

    // Initialize counts
    $statusCounts = [
        'available' => 0,
        'departed' => 0,
        'unavailable' => 0,
        'unknown' => 0
    ];

    // Process the result set
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['status'] == 'available') {
                $statusCounts['available'] = $row['count'];
            } elseif ($row['status'] == 'departed') {
                $statusCounts['departed'] = $row['count'];
            } elseif ($row['status'] == 'unavailable') {
                $statusCounts['unavailable'] = $row['count'];
            } else {
                $statusCounts['unknown'] += $row['count'];
            }
        }
    }

    // Total for scaling purposes
    $maxCount = max($statusCounts) ?: 1; // Avoid division by zero

    // Create the image
    $width = 700; // Image width
    $height = 500; // Image height
    $padding = 60; // Padding around the chart
    $barWidth = 100; // Width of each bar
    $spacing = 40; // Space between bars

    $image = imagecreatetruecolor($width, $height);

    // Colors
    $bgColor = imagecolorallocate($image, 240, 240, 240); // Light gray background
    $barColor1 = imagecolorallocate($image, 60, 179, 113); // Green for available
    $barColor2 = imagecolorallocate($image, 105, 105, 105); // Gray for departed
    $barColor3 = imagecolorallocate($image, 220, 20, 60);   // Red for unavailable
    $barColor4 = imagecolorallocate($image, 255, 215, 0);   // Gold for unknown
    $gridColor = imagecolorallocate($image, 200, 200, 200); // Light gray for grid lines
    $textColor = imagecolorallocate($image, 0, 0, 0); // Black for text

    // Fill the background
    imagefill($image, 0, 0, $bgColor);

    // Draw grid lines
    for ($i = 0; $i <= 5; $i++) {
        $y = $height - $padding - ($i * ($height - 2 * $padding) / 5);
        imageline($image, $padding, $y, $width - $padding, $y, $gridColor);
        imagestring($image, 3, 20, $y - 8, ($maxCount / 5) * $i, $textColor);
    }

    // Draw bars
    $x = $padding * 2;
    foreach ($statusCounts as $status => $count) {
        $barHeight = ($count / $maxCount) * ($height - 2 * $padding);
        $y = $height - $padding - $barHeight;

        // Cast coordinates to integers
        $x1 = (int)$x;
        $y1 = (int)$y;
        $x2 = (int)($x + $barWidth);
        $y2 = (int)($height - $padding);

        // Choose bar color based on status
        $barColor = ($status == 'available') ? $barColor1 :
                    (($status == 'departed') ? $barColor2 :
                    (($status == 'unavailable') ? $barColor3 : $barColor4));

        // Draw bar
        imagefilledrectangle($image, $x1, $y1, $x2, $y2, $barColor);

        // Add count above the bar
        imagestring($image, 5, $x1 + ($barWidth / 2) - 10, $y1 - 25, $count, $textColor);

        // Add status label below the bar
        imagestring($image, 4, $x1 + ($barWidth / 2) - 25, $height - $padding + 10, ucfirst($status), $textColor);

        // Move to the next bar position
        $x += $barWidth + $spacing;
    }

    // Capture the image as a base64 string
    ob_start();
    imagepng($image);
    $imageData = ob_get_contents();
    ob_end_clean();

    // Clean up
    imagedestroy($image);
    $conn->close();

    // Return the base64-encoded image string
    return base64_encode($imageData);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Quickie Jeepney</title>
    <link rel="stylesheet" href="manager_booking.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="manager_booking.js"></script>
    <style>
        /* General status badge styling */
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            font-size: 14px;
            width: 130px;
            font-weight: bold;
            text-transform: uppercase;
            color: white;
            border-radius: 5px;
            text-align: center;
        }

        /* Available status - green */
        .status-on-the-way {
            background-color: #28a745; /* Green */
            color: #fff; /* White text */
        }

        /* Departed status - gray */
        .status-departed {
            background-color: #6c757d; /* Gray */
            color: #fff; /* White text */
        }

        /* Unavailable status - red */
        .status-unavailable {
            background-color: #dc3545; /* Red */
            color: #fff; /* White text */
        }

        /* Unknown status - yellow */
        .status-unknown {
            background-color: #ffc107; /* Yellow */
            color: #212529; /* Dark text */
        }
    </style>
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
            <li class="nav-link">
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
                <a href="../vehicles/manager_vehicles.php" class="sidebar-link">
                    <i class="fas fa-car sidebar-icon" class="sidebar-icon"></i>Vehicles
                </a>
            </li>
            <li class="nav-link active">
                <a href="../booking/manager_booking.php" class="sidebar-link">
                    <i class="fas fa-calendar-alt sidebar-icon" class="sidebar-icon"></i>Booking Logs
                </a>
            </li>
        </ul>
    </nav>
    <section class="main-content">
        <!-- Header Section -->
        <div class="header">
            <h1>Booking Log Summary Report</h1>
            <div class="export-buttons">
                <button onclick="exportToExcel()">Export to Excel</button>
                <button onclick="exportToCSV()">Export to CSV</button>
                <button onclick="exportToPDF()">Export to PDF</button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters">
            <label for="dateFrom">From Date: </label>
            <input type="date" id="dateFrom">
            <label for="dateTo">To Date: </label>
            <input type="date" id="dateTo">
            
            <label for="status">Status: </label>
            <select id="status">
                <option value="">All</option>
                <option value="available">Available</option>
                <option value="departed">Departed</option>
                <option value="unavailable">Unavailable</option>
            </select>
            
            <button onclick="fetchData()">Apply Filters</button>
        </div>

        <section class="statistics">
            <h2>Booking Status Statistics</h2>
            <!-- Image for booking pie chart -->
            <img id="bookingHistogram" src="data:image/png;base64,<?php echo generateHistogram(); ?>" alt="Booking Status Histogram">
        </section>

        <!-- Report Table -->
        <table id="reportTable">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer Name</th>
                    <th>Jeepney Plate Number</th>
                    <th>Driver Name</th>
                    <th>Jeepney Route</th>
                    <th>Status</th>
                    <th>Departure Time</th>
                    <th>Payment Status</th>
                    <th>Payment Method</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $statusClass = '';
                            if ($row['status'] === 'available') {
                                $statusClass = 'status-on-the-way';
                            } elseif ($row['status'] === 'departed') {
                                $statusClass = 'status-departed';
                            } elseif ($row['status'] === 'unavailable') {
                                $statusClass = 'status-unavailable';
                            }else {
                                $statusClass = 'status-unknown';
                            }

                            // Decode the departure JSON
                            $departureData = json_decode($row['departure'], true);

                            // Prepare a variable for formatted departure info
                            $departureString = '';

                            if (is_array($departureData)) {
                                // If you want to ensure a specific order of days, define it:
                                $dayOrder = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
                                uksort($departureData, function($a, $b) use ($dayOrder) {
                                    $posA = array_search($a, $dayOrder);
                                    $posB = array_search($b, $dayOrder);
                                    return $posA <=> $posB;
                                });

                                foreach ($departureData as $day => $time) {
                                    // $time might be a string or an array, depending on your data structure
                                    // If it's guaranteed to be a single string (like "20:00"), just print it
                                    $departureString .= "<b>" . htmlspecialchars($day) . "</b>: " . htmlspecialchars($time) . "<br>";
                                }
                            } else {
                                // If not a valid array, just output the raw data safely
                                $departureString = htmlspecialchars($row['departure']);
                            }

                            echo "<tr>
                                <td>" . htmlspecialchars($row['bookingID']) . "</td>
                                <td>" . htmlspecialchars($row['customerName']) . "</td>
                                <td>" . htmlspecialchars($row['jeepneyPlate']) . "</td>
                                <td>" . htmlspecialchars($row['driverName']) . "</td>
                                <td>" . htmlspecialchars($row['route']) . "</td>
                                <td><span class='status-badge $statusClass'>" . ucfirst(htmlspecialchars($row['status'])) . "</span></td>
                                <td>$departureString</td>
                                <td>" . htmlspecialchars($row['paymentStatus']) . "</td>
                                <td>" . htmlspecialchars($row['paymentMethod']) . "</td>
                                <td>" . htmlspecialchars($row['amount']) . "</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10'>No records found.</td></tr>";
                    }

                    $conn->close();
                ?>
            </tbody>
        </table>

        <div id="pagination">
            <button id="prevPage" onclick="changePage('prev')" disabled></button>
            <span id="pageNumbers"></span>
            <button id="nextPage" onclick="changePage('next')"></button>
        </div>
    </section>
</body>
<script>
    let currentPage = 1;
    const rowsPerPage = 5;  // Set the number of rows per page
    let tableData = [];  // All the rows in the table
    let filteredData = [];  // Filtered rows based on the filter criteria
    // Function to fetch all the rows from the table and store in the tableData array
    function getTableData() {
        tableData = [];
        const rows = document.querySelectorAll('#reportTable tbody tr');
        rows.forEach(row => {
            tableData.push(row);
        });
        filteredData = [...tableData];  // Initially, all data is filtered
        updateTable();
    }
    // Function to apply filters and update filteredData array
    function fetchData() {
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        const status = document.getElementById('status').value;
        
        // Reset the filtered data to the complete table data initially
        filteredData = [...tableData];
        
        // Filter based on date and status
        filteredData = filteredData.filter(row => {
            const bookingDate = row.cells[2].textContent.trim();  // Booking Date in column 3 (index 2)
            const bookingStatus = row.cells[5].textContent.trim();  // Status in column 6 (index 5)
            let isValid = true;
            // Filter by date
            if (dateFrom && new Date(bookingDate) < new Date(dateFrom)) {
                isValid = false;
            }
            if (dateTo && new Date(bookingDate) > new Date(dateTo)) {
                isValid = false;
            }
            // Filter by status
            if (status && bookingStatus.toLowerCase() !== status.toLowerCase()) {
                isValid = false;
            }
            return isValid;
        });
        currentPage = 1;  // Reset to the first page when applying a new filter
        updateTable();
    }
    // Function to update the table with the current page's rows
    function updateTable() {
        const tableBody = document.querySelector('#reportTable tbody');
        tableBody.innerHTML = '';  // Clear existing rows
        
        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = Math.min(startIndex + rowsPerPage, filteredData.length);
        // Add rows to the table based on filtered data
        for (let i = startIndex; i < endIndex; i++) {
            tableBody.appendChild(filteredData[i]);
        }
        // Update the pagination numbers
        updatePageNumbers();
    }
    // Function to update page numbers (pagination buttons)
    function updatePageNumbers() {
        const pageNumbers = document.getElementById('pageNumbers');
        pageNumbers.innerHTML = '';  // Clear existing page numbers
        
        const totalPages = Math.ceil(filteredData.length / rowsPerPage);
        
        // Add pagination buttons
        for (let i = 1; i <= totalPages; i++) {
            const pageButton = document.createElement('button');
            pageButton.textContent = i;
            pageButton.onclick = function() {
                currentPage = i;
                updateTable();
            };
            if (i === currentPage) {
                pageButton.style.fontWeight = 'bold'; // Highlight the current page
            }
            pageNumbers.appendChild(pageButton);
        }
        
        // Enable/Disable previous/next buttons
        document.getElementById('prevPage').disabled = currentPage === 1;
        document.getElementById('nextPage').disabled = currentPage === totalPages;
    }
    // Change page function (Previous/Next buttons)
    function changePage(direction) {
        const totalPages = Math.ceil(filteredData.length / rowsPerPage);
        if (direction === 'prev' && currentPage > 1) {
            currentPage--;
        } else if (direction === 'next' && currentPage < totalPages) {
            currentPage++;
        }
        updateTable();
    }
    // Export table data to Excel, CSV, and PDF
    function exportToExcel() {
        const table = document.getElementById("reportTable");
        const wb = XLSX.utils.table_to_book(table);
        XLSX.writeFile(wb, "Booking_Report.xlsx");
    }
  
    function exportToCSV() {
        const table = document.getElementById("reportTable");
        const csv = XLSX.utils.table_to_csv(table);
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        saveAs(blob, "Booking_Report.csv");
    }
    
    function exportToPDF() {
        const doc = new jsPDF();
        const table = document.getElementById("reportTable");
        doc.autoTable({ html: table });
        doc.save('Booking_Report.pdf');
    }
    // Initialize table data when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        getTableData();  // Fetch and load all table data initially
    });
    // Attach event listeners to filter inputs
    document.getElementById('dateFrom').addEventListener('change', fetchData);
    document.getElementById('dateTo').addEventListener('change', fetchData);
    document.getElementById('status').addEventListener('change', fetchData);


</script>
</html>