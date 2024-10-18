// Function to display popup messages with an Okay button
function showPopup(message) {
    const popup = document.getElementById('popupMessage');
    popup.innerHTML = message + '<br><button id="okayButton">Okay</button>';
    popup.style.display = 'block'; // Show the popup

    // Add event listener to the "Okay" button
    document.getElementById('okayButton').addEventListener('click', function() {
        // Redirect back to register.php when "Okay" is clicked
        window.location.href = 'register.php';
    });
}

// Check if the URL contains the error parameter
document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    if (params.has('error') && params.get('error') === 'email_exists') {
        showPopup('An account with this email already exists. Please use a different email.');
    }
});

// Form validation and submission
document.getElementById('signupForm').addEventListener('submit', function(event) {
    const lastName = document.getElementById('lastName').value;
    const firstName = document.getElementById('firstName').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const phone = document.getElementById('phone').value;
    const occupation = document.getElementById('occupation').value;
    const terms = document.getElementById('terms').checked;

    const validationMessage = document.getElementById('validationMessage');
    validationMessage.innerHTML = '';  // Clear previous messages

    if (!terms) {
        validationMessage.textContent = 'You must agree to the terms and privacy.';
        event.preventDefault();
        return;
    }

    if (!lastName || !firstName || !email || !password || !phone || !occupation) {
        validationMessage.textContent = 'Please fill in all fields.';
        event.preventDefault();
        return;
    }
    
    // If all validation passes, the form will submit to the backend
});
