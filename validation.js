// Client-side validation for forms
document.addEventListener('DOMContentLoaded', function() {
    
    // Login form validation
    const loginForm = document.querySelector('form[action="login.php"]');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = loginForm.querySelector('input[name="username"]');
            const password = loginForm.querySelector('input[name="password"]');
            
            if (!username.value.trim()) {
                e.preventDefault();
                showError(loginForm, 'Username is required.');
                return;
            }
            
            if (!password.value) {
                e.preventDefault();
                showError(loginForm, 'Password is required.');
                return;
            }
        });
    }
    
    // Register form validation
    const registerForm = document.querySelector('form[action="register.php"]');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const username = registerForm.querySelector('input[name="username"]');
            const email = registerForm.querySelector('input[name="email"]');
            const password = registerForm.querySelector('input[name="password"]');
            const confirm = registerForm.querySelector('input[name="confirm_password"]');
            
            if (!username.value.trim() || username.value.trim().length < 3) {
                e.preventDefault();
                showError(registerForm, 'Username must be at least 3 characters.');
                return;
            }
            
            if (!email.value || !isValidEmail(email.value)) {
                e.preventDefault();
                showError(registerForm, 'Please enter a valid email address.');
                return;
            }
            
            if (!password.value || password.value.length < 8) {
                e.preventDefault();
                showError(registerForm, 'Password must be at least 8 characters.');
                return;
            }
            
            if (!/[A-Z]/.test(password.value) || !/[a-z]/.test(password.value) || !/[0-9]/.test(password.value)) {
                e.preventDefault();
                showError(registerForm, 'Password must include uppercase, lowercase, and numbers.');
                return;
            }
            
            if (password.value !== confirm.value) {
                e.preventDefault();
                showError(registerForm, 'Passwords do not match.');
                return;
            }
        });
    }
    
    // Checkout form validation
    const checkoutForm = document.querySelector('form[action="checkout.php"]');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const fullName = checkoutForm.querySelector('input[name="full_name"]');
            const address = checkoutForm.querySelector('input[name="address"]');
            const city = checkoutForm.querySelector('input[name="city"]');
            const zip = checkoutForm.querySelector('input[name="zip"]');
            const country = checkoutForm.querySelector('input[name="country"]');
            
            if (!fullName.value.trim()) {
                e.preventDefault();
                showError(checkoutForm, 'Full name is required.');
                return;
            }
            
            if (!address.value.trim()) {
                e.preventDefault();
                showError(checkoutForm, 'Address is required.');
                return;
            }
            
            if (!city.value.trim()) {
                e.preventDefault();
                showError(checkoutForm, 'City is required.');
                return;
            }
            
            if (!zip.value.trim()) {
                e.preventDefault();
                showError(checkoutForm, 'ZIP code is required.');
                return;
            }
            
            if (!country.value.trim()) {
                e.preventDefault();
                showError(checkoutForm, 'Country is required.');
                return;
            }
        });
    }
    
    // Account form validation
    const accountForm = document.querySelector('form[action="account.php"]');
    if (accountForm) {
        accountForm.addEventListener('submit', function(e) {
            const email = accountForm.querySelector('input[name="email"]');
            
            if (!email.value || !isValidEmail(email.value)) {
                e.preventDefault();
                showError(accountForm, 'Please enter a valid email address.');
                return;
            }
        });
    }
});

// Helper functions
function showError(form, message) {
    // Remove any existing error
    const existing = form.querySelector('.client-error');
    if (existing) existing.remove();
    
    // Create new error element
    const error = document.createElement('div');
    error.className = 'client-error';
    error.textContent = message;
    form.prepend(error);
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Real-time validation for password strength
document.addEventListener('input', function(e) {
    if (e.target.name === 'password') {
        const password = e.target.value;
        const strengthBar = document.getElementById('password-strength');
        
        if (strengthBar) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            
            strengthBar.style.width = (strength * 25) + '%';
            
            if (strength <= 1) {
                strengthBar.style.backgroundColor = '#dc3545';
            } else if (strength <= 3) {
                strengthBar.style.backgroundColor = '#ffc107';
            } else {
                strengthBar.style.backgroundColor = '#28a745';
            }
        }
    }
});