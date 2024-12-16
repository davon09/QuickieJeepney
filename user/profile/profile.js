document.addEventListener('DOMContentLoaded', function() {
    const passwordFields = document.querySelectorAll('.password-toggle'); // Select all password toggle icons
    const profilePicToggle = document.querySelector('.edit-icon'); // Select the profile picture icon
    const profilePicInput = document.querySelector('input[name="profile_image"]');
const profilePicPreview = document.querySelector('.profile-pic');
    
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


profilePicInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            profilePicPreview.src = event.target.result;
        }
        reader.readAsDataURL(file);
    }
});

profilePicToggle.addEventListener('click', function() {
    document.getElementById('profile_image').click();
});

profilePicInput.addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        // Check if the file is an image
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            
            // Once the file is read, update the image preview
            reader.onload = function (e) {
                document.getElementById('imagePreview').src = e.target.result;
            };
            
            // Read the image as a data URL
            reader.readAsDataURL(file);
        } else {
            // If the file is not an image, show an error
            alert('Please upload a valid image file.');
        }
    }
});
});