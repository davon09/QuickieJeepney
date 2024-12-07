document.getElementById('loginForm').addEventListener('submit', (e) => {
    e.preventDefault();  // Prevent default form submission to handle with fetch

    // Get the values from the form inputs
    const email = document.getElementById('email').value;  // Use email instead of username
    const password = document.getElementById('password').value;

    // Send login credentials to the server via POST
    fetch('/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email, password })  // Send email and password in the request body
    })
    .then(response => response.json())  // Ensure the server returns a JSON response
    .then(data => {
        console.log(data);  // Log the response from the server for debugging
        if (data.success) {
            window.location.href = '/admin'; // Redirect to the admin page if login is successful
        } else {
            alert(data.message || 'Invalid login credentials'); // Show an alert with the message from server
        }
    })
    .catch(error => {
        console.error('Error:', error);  // Log any errors
        alert('An error occurred, please try again.');  // Show error to user
    });
});
