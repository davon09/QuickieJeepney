document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const togglePassword = document.createElement('span');
    togglePassword.innerHTML = `<i class="fa fa-eye-slash"></i>`;
    passwordInput.parentNode.insertBefore(togglePassword, passwordInput.nextSibling);
    
    togglePassword.addEventListener('click', function() {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            togglePassword.innerHTML = `<i class="fa fa-eye"></i>`;
        } else {
            passwordInput.type = 'password';
            togglePassword.innerHTML = `<i class="fa fa-eye-slash"></i>`;
        }
    });
});
