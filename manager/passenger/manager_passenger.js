document.addEventListener('DOMContentLoaded', function () {
    // Handle Manage Passengers button clicks
    const manageButtons = document.querySelectorAll('.manage-passengers-btn');

    manageButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent default link behavior

            const jeepneyID = this.getAttribute('href').split('=')[1];
            alert(`Redirecting to Manage Passengers for Jeepney ID: ${jeepneyID}`);
        });
    });

    // Logout button functionality
    const logoutBtn = document.getElementById('logoutBtn');

    if (logoutBtn) {
        logoutBtn.addEventListener('click', function () {
            fetch('../logout.php', {
                method: 'POST',
            })
                .then(response => response.text())
                .then(data => {
                    if (data === 'logged_out') {
                        window.location.href = '../../index.php'; // Redirect to login page
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

    // Dropdown menu for future functionality (if needed)
    const actionButtons = document.querySelectorAll('.action-btn');

    actionButtons.forEach(button => {
        button.addEventListener('click', function () {
            this.nextElementSibling.classList.toggle('show');
        });
    });

    document.addEventListener('click', function (event) {
        if (!event.target.matches('.action-btn')) {
            document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
    });
});
