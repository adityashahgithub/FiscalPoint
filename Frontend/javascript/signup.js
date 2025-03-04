function validateForm() {
    let email = document.getElementById("email").value;
    let age = document.getElementById("age").value;
    let password = document.getElementById("password").value;
    let emailError = document.getElementById("email-error");
    let ageError = document.getElementById("age-error");
    let passwordError = document.getElementById("password-error");
    let valid = true;

    // Email Validation (Basic Format Check)
    let emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(email)) {
        emailError.innerText = "Please enter a valid email address.";
        valid = false;
    } else {
        emailError.innerText = "";
    }

  
    // Password Validation (8+ characters, uppercase, lowercase, number, special char)
    let passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if (!passwordRegex.test(password)) {
        passwordError.innerText = "Password must be at least 8 characters long, include 1 uppercase, 1 lowercase, 1 number, and 1 special character.";
        valid = false;
    } else {
        passwordError.innerText = "";
    }

    return valid; // Only submit if all validations pass
}