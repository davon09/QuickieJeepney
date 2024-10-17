// When the DOM content is fully loaded
document.addEventListener('DOMContentLoaded', function() {

    // Fetch ride data from get_rides.php
    fetch('get_rides.php')
        .then(response => response.json())
        .then(rides => {
            const rideList = document.getElementById('ride-list');
            const rideSelect = document.getElementById('rideSelect');
            
            // Populate the ride list and select dropdown
            rides.forEach(ride => {
                // Create a div for each ride and append to the ride list
                const rideDiv = document.createElement('div');
                rideDiv.textContent = `Route: ${ride.route} - Time: ${ride.time} - Seats Available: ${ride.seats}`;
                rideList.appendChild(rideDiv);

                // Create an option for each ride and append to the ride select dropdown
                const option = document.createElement('option');
                option.value = ride.route;
                option.textContent = `Route: ${ride.route} - Time: ${ride.time}`;
                rideSelect.appendChild(option);
            });
        });

    // Handle the booking form submission
    const form = document.getElementById('bookingForm');
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(form);

        // Send the booking data to book_ride.php
        fetch('book_ride.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            // Display the confirmation result
            document.getElementById('confirmation').textContent = result;
        });
    });
});