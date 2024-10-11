document.addEventListener('DOMContentLoaded', function() {
    // Load available rides dynamically from server (PHP)
    fetch('get_rides.php')
        .then(response => response.json())
        .then(rides => {
            const rideList = document.getElementById('ride-list');
            const rideSelect = document.getElementById('rideSelect');
            
            rides.forEach(ride => {
                // Populate ride list
                const rideDiv = document.createElement('div');
                rideDiv.textContent = `Route: ${ride.route} - Time: ${ride.time} - Seats Available: ${ride.seats}`;
                rideList.appendChild(rideDiv);

                // Populate ride select options
                const option = document.createElement('option');
                option.value = ride.route;
                option.textContent = `Route: ${ride.route} - Time: ${ride.time}`;
                rideSelect.appendChild(option);
            });
        });

    // Handle booking submission
    const form = document.getElementById('bookingForm');
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(form);
        
        // Send booking data to PHP
        fetch('book_ride.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            document.getElementById('confirmation').textContent = result;
        });
    });
});
