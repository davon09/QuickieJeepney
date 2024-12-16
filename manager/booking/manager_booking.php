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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx-style/0.8.13/xlsx-style.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <script src="manager_booking.js"></script>
    <style>
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

        .status-on-the-way {
            background-color: #28a745;
            color: #fff; 
        }

        .status-departed {
            background-color: #6c757d; 
            color: #fff; 
        }

        .status-unavailable {
            background-color: #dc3545;
            color: #fff; 
        }

        .status-unknown {
            background-color: #ffc107;
            color: #212529; 
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
            <label for="dayFrom">From Day: </label>
            <select id="dayFrom">
                <option value="All">All</option>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
                <option value="Saturday">Saturday</option>
                <option value="Sunday">Sunday</option>
            </select>

            <label for="dayTo">To Day: </label>
            <select id="dayTo">
                <option value="All">All</option>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
                <option value="Saturday">Saturday</option>
                <option value="Sunday">Sunday</option>
            </select>
            
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
        const dayFrom = document.getElementById('dayFrom').value; // From Day
        const dayTo = document.getElementById('dayTo').value;     // To Day
        const status = document.getElementById('status').value;   // Status filter

        // Reset the filtered data to the complete table data initially
        filteredData = [...tableData];

        // Define day order for comparison
        const dayOrder = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

        // Filter based on day and status
        filteredData = filteredData.filter(row => {
            const departureText = row.cells[6].textContent.trim(); // Departure Time column (index 6)
            const bookingStatus = row.cells[5].textContent.trim(); // Status column (index 5)
            let isValid = true;

            // Extract the day from the departure text (e.g., "Saturday: 20:00")
            const bookingDay = departureText.split(':')[0].trim();

            // Check if "All" is selected, then skip day filtering
            if (dayFrom !== "All" && dayTo !== "All") {
                const dayFromIndex = dayOrder.indexOf(dayFrom);
                const dayToIndex = dayOrder.indexOf(dayTo);
                const bookingDayIndex = dayOrder.indexOf(bookingDay);

                // Validate day range
                if (bookingDayIndex < dayFromIndex || bookingDayIndex > dayToIndex) {
                    isValid = false;
                }
            }

            // Validate status
            if (status && bookingStatus.toLowerCase() !== status.toLowerCase()) {
                isValid = false;
            }

            return isValid;
        });

        currentPage = 1; // Reset to the first page when applying a new filter
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
        // Get table data
        const table = document.getElementById("reportTable");
        const rows = table.querySelectorAll("tr");

        // Prepare data for Excel
        let excelData = [["Quickie Jeepney Booking Logs"]]; // Main header row
        rows.forEach((row, index) => {
            const rowData = [];
            row.querySelectorAll("th, td").forEach((cell) => {
                rowData.push(cell.textContent.trim());
            });
            if (index === 0) {
                excelData.push(rowData); // Headers
            } else {
                excelData.push(rowData); // Data rows
            }
        });

        // Create workbook and worksheet
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(excelData);

        // Merge the main header (first row, A1 to J1)
        if (!ws["!merges"]) ws["!merges"] = [];
        ws["!merges"].push({
            s: { r: 0, c: 0 }, // Start cell (Row 0, Column 0)
            e: { r: 0, c: 9 }, // End cell (Row 0, Column 9)
        });

        // Add styles
        const headerStyle = {
            font: { bold: true, color: { rgb: "FFFFFF" } },
            fill: { fgColor: { rgb: "16A085" } }, // Teal background
            alignment: { horizontal: "center", vertical: "center" },
        };

        const columnHeaderStyle = {
            font: { bold: true, color: { rgb: "FFFFFF" } },
            fill: { fgColor: { rgb: "2C3E50" } }, // Dark teal for column headers
            alignment: { horizontal: "center", vertical: "center" },
        };

        // Apply styles
        ws['A1'].s = headerStyle; // Main header
        for (let col = 0; col <= 9; col++) {
            const cellAddress = XLSX.utils.encode_cell({ r: 1, c: col }); // Row 1 (headers)
            if (ws[cellAddress]) ws[cellAddress].s = columnHeaderStyle;
        }

        // Adjust column widths
        ws['!cols'] = [
            { wch: 10 }, // Booking ID
            { wch: 20 }, // Customer Name
            { wch: 18 }, // Jeepney Plate Number
            { wch: 20 }, // Driver Name
            { wch: 30 }, // Jeepney Route
            { wch: 15 }, // Status
            { wch: 20 }, // Departure Time
            { wch: 20 }, // Payment Status
            { wch: 20 }, // Payment Method
            { wch: 10 }, // Amount
        ];

        // Create filename
        const today = new Date();
        const formattedDate = today.toISOString().split('T')[0]; // YYYY-MM-DD
        const filename = `QuickieJeepney_Log-${formattedDate}.xlsx`;

        // Write the Excel file with xlsx-style
        XLSX.utils.book_append_sheet(wb, ws, "BookingLogs");
        XLSX.writeFile(wb, filename);
    }
    
    function exportToCSV() {
        const table = document.getElementById("reportTable");

        if (!table) {
            alert("Error: Table not found!");
            return;
        }

        // Prepare the data for CSV
        let csvContent = "data:text/csv;charset=utf-8,";

        // Add the main header
        csvContent += "Quickie Jeepney Booking Logs\n"; // Add title with extra space

        // Extract table rows
        const rows = table.querySelectorAll("tr");

        // Format and add rows to the CSV
        rows.forEach((row, index) => {
            const rowData = [];
            row.querySelectorAll("th, td").forEach((cell) => {
                rowData.push(`"${cell.textContent.trim()}"`); // Escape content safely
            });

            // Add spacing after the header row
            if (index === 0) {
                csvContent += rowData.join(",") + "\n\n"; // Add space after column headers
            } else {
                csvContent += rowData.join(",") + "\n";
            }
        });

        // Add export date at the bottom
        const today = new Date();
        const formattedDate = today.toISOString().split("T")[0]; // Format: YYYY-MM-DD
        csvContent += `\nExported on: ${formattedDate}`;

        // Generate a filename
        const filename = `QuickieJeepney_Log-${formattedDate}.csv`;

        // Create and trigger a download link
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", filename);
        document.body.appendChild(link); // Append to DOM for Firefox compatibility
        link.click();
        document.body.removeChild(link); // Clean up
    }

    
    function exportToPDF() {
        // Check for jsPDF availability
        if (!window.jspdf || !window.jspdf.jsPDF) {
            console.error("jsPDF library is not loaded.");
            alert("Error: jsPDF library is missing!");
            return;
        }

        // Initialize jsPDF in landscape mode
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF("landscape"); // Set orientation to landscape

        // Title and export date
        const title = "Quickie Jeepney - Booking Log Report";
        const today = new Date();
        const formattedDate = today.toISOString().split("T")[0]; // Format: YYYY-MM-DD
        const displayDate = `Exported Date: ${formattedDate}`;

        // Add title and date
        doc.setFontSize(16);
        doc.text(title, 14, 15);
        doc.setFontSize(12);
        doc.text(displayDate, 14, 22);

        // Extract table data
        const table = document.getElementById("reportTable");
        if (!table) {
            alert("Error: Table not found!");
            return;
        }

        let pdfData = [];
        const rows = table.querySelectorAll("tr");
        rows.forEach((row) => {
            const rowData = [];
            row.querySelectorAll("th, td").forEach((cell) => {
                rowData.push(cell.textContent.trim());
            });
            pdfData.push(rowData);
        });

        // AutoTable options for better layout
        doc.autoTable({
            head: [pdfData[0]], // Table headers
            body: pdfData.slice(1), // Table rows
            startY: 30, // Start position
            theme: "grid", // Add borders
            styles: {
                fontSize: 8, // Smaller font size
                cellPadding: 2, // Reduce cell padding
                valign: "middle",
                halign: "center",
            },
            headStyles: {
                fillColor: [22, 160, 133], // Teal header background
                textColor: 255, // White text
                fontStyle: "bold",
            },
            columnStyles: {
                0: { cellWidth: 20 }, // Booking ID
                1: { cellWidth: 27 }, // Customer Name
                2: { cellWidth: 35 }, // Jeepney Plate Number
                3: { cellWidth: 40 }, // Driver Name
                4: { cellWidth: 45 }, // Jeepney Route
                5: { cellWidth: 20 }, // Status
                6: { cellWidth: 35 }, // Departure Time
                7: { cellWidth: 20 }, // Payment Status
                8: { cellWidth: 20 }, // Payment Method
                9: { cellWidth: 15 }, // Amount
            },
            margin: { top: 30, left: 10, right: 10 }, // Reduce margins
            didDrawPage: function (data) {
                // Add export date at the bottom of each page
                doc.setFontSize(10);
                const pageHeight = doc.internal.pageSize.height;
                doc.text(`Quickie Jeepney 2024`, 10, pageHeight - 10);
            },
        });

        // Save the PDF
        const filename = `QuickieJeepney_Log-${formattedDate}.pdf`;
        doc.save(filename);
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