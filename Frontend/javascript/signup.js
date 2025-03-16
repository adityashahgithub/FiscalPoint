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

      
      // Confirm Password Validation
      if (password !== confirmPassword) {
          confirmPasswordError.innerText = "Passwords do not match.";
          confirmPasswordError.style.color = "red";
          valid = false;
      } else {
          confirmPasswordError.innerText = "";
      }
  
      return valid;
}

