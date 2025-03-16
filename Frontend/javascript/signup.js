document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".signup-form").addEventListener("submit", function (event) {
        if (!validateForm()) {
            event.preventDefault(); // Prevent form submission if validation fails
        }
    });
});

function validateForm() {
    let email = document.getElementById("email").value;
    let password = document.getElementById("Password").value; // Fix: Corrected `id` to match HTML
    let emailError = document.getElementById("email-error");
    let passwordError = document.getElementById("password-error");
    let valid = true;

    // Email Validation
    let emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(email)) {
        emailError.innerText = "Please enter a valid email address.";
        emailError.style.color = "red";
        valid = false;
    } else {
        emailError.innerText = "";
    }

    // Password Validation
    let passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if (!passwordRegex.test(password)) {
        passwordError.innerText = "Password must be at least 8 characters long, include 1 uppercase, 1 lowercase, 1 number, and 1 special character.";
        passwordError.style.color = "red";
        valid = false;
    } else {
        passwordError.innerText = "";
    }

    document.addEventListener("DOMContentLoaded", function () {
        const form = document.querySelector(".signup-form");
        const passwordInput = document.getElementById("Password");
        const confirmPasswordInput = document.getElementById("ConfirmPassword");
        const confirmPasswordError = document.getElementById("confirm-password-error");
    
        form.addEventListener("submit", function (event) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordError.textContent = "Passwords do not match!";
                event.preventDefault(); // Prevent form submission
            } else {
                confirmPasswordError.textContent = ""; // Clear error message
            }
        });
    });
    
}

