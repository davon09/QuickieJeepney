function showPopup(message, success = false) {
    const popup = document.getElementById('popupMessage');
    popup.innerHTML = message;
    popup.style.display = 'block'; 

    if (success) {
        popup.style.backgroundColor = '#d4edda'; 
        popup.style.color = '#155724';
    } else {
        popup.style.backgroundColor = '#ffcccb';  
        popup.style.color = '#333'; 
    }
}
document.getElementById('signupForm').addEventListener('submit', function(event) {
    event.preventDefault(); 

    document.querySelectorAll('.error-popup').forEach(function(popup) {
        popup.remove();
    });

    const lastName = document.getElementById('lastName').value.trim();
    const firstName = document.getElementById('firstName').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const retypePassword = document.getElementById('retypePassword').value.trim();
    const contactNumber = document.getElementById('contactNumber').value.trim();
    const occupation = document.getElementById('occupation').value;
    const terms = document.getElementById('terms').checked;

    const phoneRegex = /^09\d{9}$/;
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

    function showError(field, message) {
        const errorPopup = document.createElement('div');
        errorPopup.classList.add('error-popup');
        errorPopup.innerText = message;
        field.parentElement.appendChild(errorPopup);

        errorPopup.style.position = 'absolute';
        errorPopup.style.backgroundColor = '#ffcccb';
        errorPopup.style.color = '#333';
        errorPopup.style.padding = '10px';
        errorPopup.style.borderRadius = '5px';
        errorPopup.style.fontSize = '12px';
        errorPopup.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.1)';
        errorPopup.style.top = `${field.offsetTop + field.offsetHeight + 5}px`;
        errorPopup.style.left = `${field.offsetLeft}px`;

        field.addEventListener('focus', function() {
            errorPopup.remove();
        });
    }

    function clearConfirmPassword() {
        document.getElementById('retypePassword').value = ''; 
    }

    if (!lastName) {
        showError(document.getElementById('lastName'), 'Last name is required.');
        return;
    }

    if (!firstName) {
        showError(document.getElementById('firstName'), 'First name is required.');
        return;
    }

    if (!email) {
        showError(document.getElementById('email'), 'Email is required.');
        return;
    }

    if (password !== retypePassword) {
        showError(document.getElementById('password'), 'Passwords do not match.');
        clearConfirmPassword();  
        return;
    }

    if (!passwordRegex.test(password)) {
        showError(document.getElementById('password'), 'Password must be at least 8 characters long, and include uppercase, lowercase, number, and special character.');
        clearConfirmPassword();  
        return;
    }

    if (!phoneRegex.test(contactNumber)) {
        showError(document.getElementById('contactNumber'), 'Please enter a valid Philippine phone number.');
        return;
    }

    if (!occupation) {
        showError(document.getElementById('occupation'), 'Please select an occupation.');
        return;
    }

    if (!terms) {
        showError(document.getElementById('terms'), 'You must agree to the Terms & Privacy to proceed.');
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'register.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = xhr.responseText.trim(); 
            console.log("Server response:", response);  

            if (response === 'email_exists') {
                showError(document.getElementById('email'), 'Email already exists.');
            } else if (response === 'success') {
                showPopup('Registration successful!', true);

                setTimeout(function() {
                    window.location.href = '../index.php';  
                }, 2000); 
            } else {
                showPopup('An error occurred. Please try again.');
            }
        }
    };

    const postData = `lastName=${encodeURIComponent(lastName)}&firstName=${encodeURIComponent(firstName)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&contactNumber=${encodeURIComponent(contactNumber)}&occupation=${encodeURIComponent(occupation)}`;
    
    xhr.send(postData);
});

function togglePasswordVisibility(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const icon = passwordInput.nextElementSibling.querySelector('i');
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);

    if (type === 'password') {
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}


