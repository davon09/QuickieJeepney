document.addEventListener('DOMContentLoaded', () => {
    const vehicleTypeSelect = document.getElementById('vehicle-type');
    const sortBySelect = document.getElementById('sort-by');
    const vehicleCards = document.querySelectorAll('.jeepney-card');

    // Function to filter vehicle cards based on type
    function filterVehicles() {
        const selectedType = vehicleTypeSelect.value;

        vehicleCards.forEach(card => {
            const vehicleType = card.getAttribute('data-type');
            if (selectedType === 'all' || vehicleType === selectedType) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Function to sort vehicle cards by departure time
    function sortVehicles() {
        const sortedCards = Array.from(vehicleCards).sort((a, b) => {
            const timeA = a.getAttribute('data-departure');
            const timeB = b.getAttribute('data-departure');

            return timeA.localeCompare(timeB); // Sorts times in ascending order
        });

        const jeepneyList = document.querySelector('.jeepney-cards');
        jeepneyList.innerHTML = ''; // Clear the list

        sortedCards.forEach(card => {
            jeepneyList.appendChild(card); // Re-append in sorted order
        });
    }

    // Event listeners for filter and sort
    vehicleTypeSelect.addEventListener('change', () => {
        filterVehicles();
        sortVehicles(); // Ensure it's sorted after filtering
    });

    sortBySelect.addEventListener('change', () => {
        sortVehicles(); // Sort whenever the sort option is changed
    });

    // Initial sorting on page load
    sortVehicles();
});

document.addEventListener('DOMContentLoaded', function () {
    const logoutBtn = document.getElementById('logoutBtn');
    const modal = document.getElementById('confirmLogout');
    const confirmYes = document.getElementById('confirmYes');
    const confirmNo = document.getElementById('confirmNo');

    // Show modal when clicking on logout button
    logoutBtn.addEventListener('click', function () {
        modal.style.display = 'block';
    });

    // If user confirms logout
    confirmYes.addEventListener('click', function () {
        window.location.href = '../../index.php'; // Redirect to logout page
    });

    // If user cancels logout
    confirmNo.addEventListener('click', function () {
        modal.style.display = 'none'; // Close the modal
    });

    // Close the modal if user clicks outside of it
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});

// Handle Logout Modal Logic
document.addEventListener('DOMContentLoaded', function () {
    const logoutButton = document.getElementById('logoutButton');
    const logoutModal = document.getElementById('logoutModal');
    const confirmLogout = document.getElementById('confirmLogout');
    const cancelLogout = document.getElementById('cancelLogout');

    // Show the modal when logout button is clicked
    logoutButton.addEventListener('click', () => {
        logoutModal.style.display = 'flex';
    });

    // Hide the modal when cancel button is clicked
    cancelLogout.addEventListener('click', () => {
        logoutModal.style.display = 'none';
    });

    // Perform the logout when confirm button is clicked
    confirmLogout.addEventListener('click', () => {
        window.location.href = '../index.php'; // Redirect to the logout page
    });
});
