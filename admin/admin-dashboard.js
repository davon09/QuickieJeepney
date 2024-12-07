document.addEventListener('DOMContentLoaded', function () {
    const logoutButton = document.querySelector('a[href="/logout"]');

    logoutButton.addEventListener('click', function(event) {
        event.preventDefault();

        fetch('/logout', {
            method: 'POST'
        })
        .then(response => {
            if (response.ok) {
                window.location.href = '/';
            } else {
                alert('Failed to log out. Please try again.');
            }
        })
        .catch(error => console.error('Error logging out:', error));
    });

    // Example of dynamically updating content (if applicable)
    fetch('/api/stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalUsers').textContent = data.totalUsers;
            document.getElementById('totalReviews').textContent = data.totalReviews;
        })
        .catch(error => console.error('Error fetching stats:', error));
});
