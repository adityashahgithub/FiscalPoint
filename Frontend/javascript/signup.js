document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector(".signup-form");
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("Password");
    const confirmPasswordInput = document.getElementById("ConfirmPassword");
    const emailError = document.getElementById("email-error");
    const passwordError = document.getElementById("password-error");
    const confirmPasswordError = document.getElementById("confirm-password-error");

    form.addEventListener("submit", function (event) {
        let valid = true;

        // ✅ Email Validation
        let emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!emailRegex.test(emailInput.value)) {
            emailError.innerText = "Please enter a valid email address.";
            emailError.style.color = "red";
            valid = false;
        } else {
            emailError.innerText = "";
        }

        // ✅ Password Validation
        let passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passwordRegex.test(passwordInput.value)) {
            passwordError.innerText = "Password must be at least 8 characters long, include 1 uppercase, 1 lowercase, 1 number, and 1 special character.";
            passwordError.style.color = "red";
            valid = false;
        } else {
            passwordError.innerText = "";
        }

        // ✅ Confirm Password Validation
        if (passwordInput.value !== confirmPasswordInput.value) {
            confirmPasswordError.innerText = "Passwords do not match!";
            confirmPasswordError.style.color = "red";
            valid = false;
        } else {
            confirmPasswordError.innerText = "";
        }

        // ❌ If any validation fails, prevent form submission
        if (!valid) {
            event.preventDefault();
        }
    });
});
