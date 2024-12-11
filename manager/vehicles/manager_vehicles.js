document.addEventListener('DOMContentLoaded', function() {
    // Select the button by its ID and add a click event listener
    const logoutBtn = document.getElementById('logoutBtn');
    
    if (logoutBtn) { // Check if the button exists to avoid errors
        logoutBtn.addEventListener('click', function() {
            console.log('Logout button clicked');
            
            // Sending a logout request via AJAX (fetch)
            fetch('../logout.php', {
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
