document.addEventListener('DOMContentLoaded', function() {
    const dropdownBtn = document.getElementById('dropdownBtn');
    const dropdownContent = document.getElementById('dropdownContent');

    // Toggle dropdown visibility
    dropdownBtn.addEventListener('click', function(e) {
        e.preventDefault(); // Prevent form submit on button click
        dropdownContent.style.display = (dropdownContent.style.display === 'block') ? 'none' : 'block';
    });

    // Close the dropdown if clicked outside
    window.addEventListener('click', function(e) {
        if (!e.target.matches('#dropdownBtn')) {
            dropdownContent.style.display = 'none';
        }
    });

    // Auto-close dropdown when a payment method is selected
    const radios = document.querySelectorAll('input[name="paymentMethod"]');
    radios.forEach(radio => {
        radio.addEventListener('click', function() {
            dropdownBtn.textContent = this.value; // Update dropdown button text to selected method
            dropdownContent.style.display = 'none'; // Close dropdown
        });
    });
});