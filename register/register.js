// Handle form submission
document.getElementById('signupForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const fullname = document.getElementById('fullname').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const phone = document.getElementById('phone').value;
    const occupation = document.getElementById('occupation').value;
    const terms = document.getElementById('terms').checked;

    // Validation message containers
    const validationMessage = document.getElementById('validationMessage');

    // Clear previous messages
    validationMessage.innerHTML = '';

    // Basic validation for terms and fields
    if (!terms) {
        validationMessage.textContent = 'You must agree to the terms and privacy.';
        return;
    }

    // Check if any required field is empty
    if (!fullname || !email || !password || !phone || !occupation) {
        validationMessage.textContent = 'Please fill in all fields.';
        return;
    }

    // If validation passes, submit the form
    this.submit();
});

// Close form simulation
document.querySelector('.close-btn').addEventListener('click', function() {
    // Optionally, you can handle any closing functionality here
    console.log('Close button clicked');
});
