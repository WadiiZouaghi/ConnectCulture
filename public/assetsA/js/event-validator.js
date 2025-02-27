document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (!form) return;

    // Function to show error messages
    function showError(input, message) {
        const formGroup = input.closest('.form-group');
        input.classList.add('is-invalid');
        
        let feedback = formGroup.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            input.parentNode.appendChild(feedback);
        }
        feedback.innerHTML = `<i class="fa fa-exclamation-circle"></i> ${message}`;
    }

    // Function to clear error messages
    function clearError(input) {
        const formGroup = input.closest('.form-group');
        input.classList.remove('is-invalid');
        const feedback = formGroup.querySelector('.invalid-feedback');
        if (feedback) feedback.remove();
    }

    // Validate all inputs
    function validateForm() {
        let isValid = true;
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            clearError(input);
            
            if (input.required && !input.value.trim()) {
                showError(input, `${input.name.replace('event[', '').replace(']', '')} is required`);
                isValid = false;
            }
            
            if (input.minLength && input.value.length < input.minLength) {
                showError(input, `Minimum ${input.minLength} characters required`);
                isValid = false;
            }
            
            if (input.type === 'file' && input.files.length > 0) {
                const file = input.files[0];
                const maxSize = 2 * 1024 * 1024; // 2MB
                
                if (file.size > maxSize) {
                    showError(input, 'File size cannot exceed 2MB');
                    isValid = false;
                }
                
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    showError(input, 'Please upload a valid image file (JPEG, PNG, GIF)');
                    isValid = false;
                }
            }
        });
        
        return isValid;
    }

    // Real-time validation
    form.querySelectorAll('input, select, textarea').forEach(input => {
        input.addEventListener('input', () => {
            clearError(input);
            if (input.required && !input.value.trim()) {
                showError(input, `${input.name.replace('event[', '').replace(']', '')} is required`);
            }
        });
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            // Scroll to first error
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    // Update timestamp every second
    function updateDateTime() {
        const now = new Date();
        const dateTimeString = 'Current Date and Time (UTC - YYYY-MM-DD HH:MM:SS formatted): ' + 
                             now.getUTCFullYear() + '-' + 
                             String(now.getUTCMonth() + 1).padStart(2, '0') + '-' +
                             String(now.getUTCDate()).padStart(2, '0') + ' ' +
                             String(now.getUTCHours()).padStart(2, '0') + ':' +
                             String(now.getUTCMinutes()).padStart(2, '0') + ':' +
                             String(now.getUTCSeconds()).padStart(2, '0');
        
        document.querySelector('.system-info-text').textContent = dateTimeString + '\n' + 
            'Current User\'s Login: ' + (document.querySelector('[data-user]')?.dataset.user || 'Guest');
    }

    updateDateTime();
    setInterval(updateDateTime, 1000);
});