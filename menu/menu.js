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
