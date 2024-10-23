document.addEventListener('DOMContentLoaded', () => {
    const typeFilter = document.getElementById('type');
    const sortBy = document.getElementById('sort-by');
    const jeepneyCards = document.querySelectorAll('.jeepney-card');

    function filterAndSortJeepneys() {
        const selectedType = typeFilter.value.toLowerCase();
        const selectedSort = sortBy.value;

        const jeepneyArray = Array.from(jeepneyCards);

        jeepneyArray.forEach(jeepney => {
            const vehicleType = jeepney.getAttribute('data-type').toLowerCase();
            if (selectedType === 'all' || selectedType === vehicleType) {
                jeepney.style.display = 'block';
            } else {
                jeepney.style.display = 'none';
            }
        });

        const sortedJeepneys = jeepneyArray
            .filter(jeepney => jeepney.style.display === 'block') 
            .sort((a, b) => {
                if (selectedSort === 'seats') {
                    const seatsA = parseInt(
                        a.querySelector('h2').textContent.replace('Seats Available: ', '')
                    );
                    const seatsB = parseInt(
                        b.querySelector('h2').textContent.replace('Seats Available: ', '')
                    );
                    return seatsB - seatsA; 
                } else if (selectedSort === 'departure') {
                    const timeA = convertToComparableTime(a.getAttribute('data-departure'));
                    const timeB = convertToComparableTime(b.getAttribute('data-departure'));
                    return timeA - timeB;
                }
                return 0; 
            });

        const jeepneyContainer = document.querySelector('.jeepney-cards');
        jeepneyContainer.innerHTML = ''; 
        sortedJeepneys.forEach(jeepney => jeepneyContainer.appendChild(jeepney));
    }


    function convertToComparableTime(timeStr) {
        const [time, modifier] = timeStr.split(' '); 
        let [hours, minutes] = time.split(':').map(Number);

        if (modifier === 'PM' && hours < 12) {
            hours += 12;
        } else if (modifier === 'AM' && hours === 12) {
            hours = 0; 
        }

        return hours * 60 + minutes;
    }

    typeFilter.addEventListener('change', filterAndSortJeepneys);
    sortBy.addEventListener('change', filterAndSortJeepneys);

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

// document.getElementById('logoutBtn').addEventListener('click', () => {
//     window.location.href = 'logout.php'; 
// });

document.addEventListener('DOMContentLoaded', function() {
    // Select the button by its ID and add a click event listener
    const logoutBtn = document.getElementById('logoutBtn');
    
    if (logoutBtn) { // Check if the button exists to avoid errors
        logoutBtn.addEventListener('click', function() {
            console.log('Logout button clicked');
            
            // Sending a logout request via AJAX (fetch)
            fetch('logout.php', {
                method: 'POST',
            })
            .then(response => response.text())
            .then(data => {
                if (data === 'logged_out') {
                    // Redirect to the login page or any other page after logout
                    window.location.href = '../../index.php'; // Adjust the URL to your login page
                } else {
                    alert('Logout failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    }
});