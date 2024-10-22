document.addEventListener('DOMContentLoaded', function() {
    const passwordFields = document.querySelectorAll('.password-toggle'); // Select all password toggle icons
    
    passwordFields.forEach(toggle => {
        const passwordInput = toggle.previousElementSibling; // The password input field right before the toggle icon
        
        toggle.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggle.innerHTML = `<i class="fa fa-eye"></i>`; 
            } else {
                passwordInput.type = 'password';
                toggle.innerHTML = `<i class="fa fa-eye-slash"></i>`; 
            }
        });
    });
});
