document.addEventListener('DOMContentLoaded', () => {
    const vehicleTypeSelect = document.getElementById('vehicle-type');
    const sortBySelect = document.getElementById('sort-by');
    const jeepneyCardsContainer = document.querySelector('.jeepney-cards'); // Container for the cards
    let vehicleCards = Array.from(document.querySelectorAll('.jeepney-card')); // Store all cards initially

    // Function to filter vehicle cards based on type
    function filterVehicles() {
        const selectedType = vehicleTypeSelect.value;
        
        // Filter and display cards based on the selected type
        vehicleCards.forEach(card => {
            const vehicleType = card.getAttribute('data-type');
            if (selectedType === 'all' || vehicleType === selectedType) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Function to sort vehicle cards by departure time or seats
    function sortVehicles() {
        const sortBy = sortBySelect.value;

        const sortedCards = vehicleCards.slice().sort((a, b) => {
            if (sortBy === 'departure') {
                const timeA = a.getAttribute('data-departure');
                const timeB = b.getAttribute('data-departure');
                return new Date('1970/01/01 ' + timeA) - new Date('1970/01/01 ' + timeB);
            } else if (sortBy === 'seats') {
                const seatsA = parseInt(a.getAttribute('data-seats'));
                const seatsB = parseInt(b.getAttribute('data-seats'));
                return seatsB - seatsA; // Sort in descending order of seats
            }
        });

        // Clear the container and re-append sorted cards
        jeepneyCardsContainer.innerHTML = '';
        sortedCards.forEach(card => jeepneyCardsContainer.appendChild(card));
    }

    // Event listeners for filter and sort dropdowns
    vehicleTypeSelect.addEventListener('change', () => {
        filterVehicles();
        sortVehicles(); // Ensure cards are sorted after filtering
    });

    sortBySelect.addEventListener('change', sortVehicles);

    // Initial render and sort on page load
    filterVehicles(); // Ensure only relevant cards are displayed initially
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
