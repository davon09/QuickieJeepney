document.addEventListener('DOMContentLoaded', () => {
    const vehicleTypeSelect = document.getElementById('vehicle-type');
    const sortBySelect = document.getElementById('sort-by');
    const jeepneyCardsContainer = document.getElementById('jeepney-cards');
    let vehicleCards = Array.from(document.querySelectorAll('.jeepney-card'));

    // Function to render the filtered and sorted cards
    function renderCards(cards) {
        jeepneyCardsContainer.innerHTML = ''; // Clear the container
        cards.forEach(card => jeepneyCardsContainer.appendChild(card)); // Append cards
    }

    // Function to filter the cards based on the selected vehicle type
    function filterVehicles() {
        const selectedType = vehicleTypeSelect.value;

        return vehicleCards.filter(card => {
            const vehicleType = card.getAttribute('data-type');
            return selectedType === 'all' || vehicleType === selectedType;
        });
    }

    // Function to sort the cards based on the selected option
    function sortVehicles(cards) {
        const sortBy = sortBySelect.value;

        return cards.sort((a, b) => {
            if (sortBy === 'departure') {
                const timeA = new Date(`1970/01/01 ${a.getAttribute('data-departure')}`);
                const timeB = new Date(`1970/01/01 ${b.getAttribute('data-departure')}`);
                return timeA - timeB; // Ascending order by departure
            } else if (sortBy === 'seats') {
                const seatsA = parseInt(a.getAttribute('data-seats'), 10);
                const seatsB = parseInt(b.getAttribute('data-seats'), 10);
                return seatsB - seatsA; // Descending order by seats
            }
        });
    }

    // Function to refresh the cards based on the filter and sort options
    function refreshCards() {
        const filteredCards = filterVehicles(); // Filter the cards
        const sortedCards = sortVehicles(filteredCards); // Sort the filtered cards
        renderCards(sortedCards); // Render the sorted and filtered cards
    }

    // Event listeners for filter and sort dropdowns
    vehicleTypeSelect.addEventListener('change', refreshCards);
    sortBySelect.addEventListener('change', refreshCards);

    // Initial render on page load
    refreshCards();
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

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.book-now').forEach(button => {
        button.addEventListener('click', () => {
            const jeepneyId = button.getAttribute('data-id');
            window.location.href = `../booking/book-now.php?id=${jeepneyId}`;
        });
    });
});