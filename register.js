// Handle form submission
document.getElementById('signupForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const fullname = document.getElementById('fullname').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const phone = document.getElementById('phone').value;
    const occupation = document.getElementById('occupation').value;
    const terms = document.getElementById('terms').checked;

    if (terms) {
        alert(`Account created for ${fullname}!`);
        // You can handle form submission logic here (e.g., send data to a server)
    } else {
        alert('You must agree to the terms and privacy.');
    }
});

// Close form (for now, just simulates closing)
document.querySelector('.close-btn').addEventListener('click', function() {
    alert('Close button clicked');
});
