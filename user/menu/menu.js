document.addEventListener('DOMContentLoaded', () => {
    const typeFilter = document.getElementById('type');
    const sortBy = document.getElementById('sort-by');
    const jeepneyCards = document.querySelectorAll('.jeepney-card');

    // Function to filter and sort jeepneys
    function filterAndSortJeepneys() {
        const selectedType = typeFilter.value.toLowerCase();
        const selectedSort = sortBy.value;

        // Convert NodeList to an array for sorting
        const jeepneyArray = Array.from(jeepneyCards);

        // Filter jeepneys by selected vehicle type
        jeepneyArray.forEach(jeepney => {
            const vehicleType = jeepney.getAttribute('data-type').toLowerCase();
            if (selectedType === 'all' || selectedType === vehicleType) {
                jeepney.style.display = 'block';
            } else {
                jeepney.style.display = 'none';
            }
        });

        // Sort jeepneys based on selected criteria (either by seats or departure time)
        const sortedJeepneys = jeepneyArray
        .filter(jeepney => jeepney.style.display === 'block')
        .sort((a, b) => {
            if (selectedSort === 'seats') {
                const seatsA = parseInt(a.querySelector('h3').textContent.replace('Seats Available: ', ''));
                const seatsB = parseInt(b.querySelector('h3').textContent.replace('Seats Available: ', ''));
                return seatsB - seatsA; // Sort in descending order
            } else if (selectedSort === 'departure') {
                const timeA = convertToComparableTime(a.getAttribute('data-departure'));
                const timeB = convertToComparableTime(b.getAttribute('data-departure'));
                return timeA - timeB; // Sort by time, ascending
            }
        });

        // Clear the container and append the sorted jeepneys
        const jeepneyContainer = document.querySelector('.jeepney-cards');
        jeepneyContainer.innerHTML = '';
        sortedJeepneys.forEach(jeepney => jeepneyContainer.appendChild(jeepney));
    }

    // Helper function to convert AM/PM time to total minutes for sorting
    function convertToComparableTime(timeStr) {
        // Example: "2:30 PM" => "14:30", or convert it to minutes since midnight
        const [time, modifier] = timeStr.split(' '); // Split time and AM/PM
        let [hours, minutes] = time.split(':').map(Number);
    
        if (modifier === 'PM' && hours < 12) {
            hours += 12;
        } else if (modifier === 'AM' && hours === 12) {
            hours = 0; // Midnight case
        }
    
        // Convert hours and minutes to total minutes since midnight
        return hours * 60 + minutes;
    }

    // Add event listeners for filtering and sorting
    typeFilter.addEventListener('change', filterAndSortJeepneys);
    sortBy.addEventListener('change', filterAndSortJeepneys);

    // Initial call to apply filters and sorting
    filterAndSortJeepneys();
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.book-now').forEach(button => {
        button.addEventListener('click', () => {
            const jeepneyId = button.getAttribute('data-id');
            window.location.href = `../booking/book-now.php?id=${jeepneyId}`;
        });
    });
});

document.getElementById('logoutBtn').addEventListener('click', () => {
    window.location.href = '../logout/logout.php'; // Adjust path to logout page
});