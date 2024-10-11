document.addEventListener('DOMContentLoaded', function() {

    fetch('get_rides.php')
        .then(response => response.json())
        .then(rides => {
            const rideList = document.getElementById('ride-list');
            const rideSelect = document.getElementById('rideSelect');
            
            rides.forEach(ride => {

                const rideDiv = document.createElement('div');
                rideDiv.textContent = `Route: ${ride.route} - Time: ${ride.time} - Seats Available: ${ride.seats}`;
                rideList.appendChild(rideDiv);

                const option = document.createElement('option');
                option.value = ride.route;
                option.textContent = `Route: ${ride.route} - Time: ${ride.time}`;
                rideSelect.appendChild(option);
            });
        });

    const form = document.getElementById('bookingForm');
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(form);

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
