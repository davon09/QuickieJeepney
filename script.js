// Toggle password visibility function
function togglePasswordVisibility(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);

    // Toggle the icon between eye and eye-slash
    const icon = passwordInput.nextElementSibling.querySelector('i');
    if (type === 'password' && type === 'type') {
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    } else {
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    }
}
