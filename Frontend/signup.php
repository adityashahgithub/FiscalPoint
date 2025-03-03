<?php
// Step 1: Establish a database connection
$servername = "localhost";  // MySQL server (usually localhost)
$username = "root";         // MySQL username (usually 'root')
$password = "";             // MySQL password (usually empty for local setup)
$dbname = "fiscalpoint";    // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 2: Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Step 3: Get the form data
    $email = $_POST['email'];
    $name = $_POST['name'];
    $age = $_POST['age'];
    $password = $_POST['password'];

    // Step 4: Validate form data (you can add more validations as needed)
    if (empty($email) || empty($name) || empty($age) || empty($password)) {
        echo "All fields are required!";
        exit();
    }

    // Step 5: Hash the password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Step 6: Prepare the SQL query to insert data into the 'user' table
    $stmt = $conn->prepare("INSERT INTO user (email, name, age, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $email, $name, $age, $hashedPassword); // 's' = string, 'i' = integer

    // Step 7: Execute the query and check if the insertion is successful
    if ($stmt->execute()) {
        echo "Registration successful!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Step 8: Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Fiscal Point</title>
    <link rel="stylesheet" href="css/signup.css">  <!-- Linking External CSS -->
</head>
<body>
 <!-- Main Container -->
 <div class="container">
    <!-- Header Section -->
    <header>
       <img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'"> <!-- Company Logo -->
       
       <!-- Navigation Menu -->
       <nav class="navbar">
           <ul>
               <li><a href="landing.html">Home</a></li>
               <li><a href="login.html">Expense Tracker</a></li>
               <li><a href="login.html">Cost of Living Calculator</a></li>
           </ul>
       </nav>
   </header>    

    <!-- ======= Signup Form Section ======= -->
    <div class="signup-container">
        <h2>Welcome to Fiscal Point!</h2>

        <!-- Signup Form -->
        <form class="signup-form" onsubmit="return validateForm()">
            <!-- Email Input -->
            <label for="email">Enter your email:</label>
            <input type="email" id="email" placeholder="Email" required>
            <small id="email-error" class="error-message"></small>

            <!-- Full Name Input -->
            <label for="fullname">Full Name:</label>
            <input type="text" id="fullname" placeholder="Full Name" required>

            <!-- Age Input -->
            <label for="age">Age:</label>
            <input type="number" id="age" placeholder="Age" required min="18" max="100">
            <small id="age-error" class="error-message"></small>

            <!-- Password Input -->
            <label for="password">Password:</label>
            <input type="password" id="password" placeholder="Password" required>
            <small id="password-error" class="error-message"></small>

            <!-- Signup Button -->
            <button type="submit">Get Started</button>
        </form>

        <!-- Login Redirect -->
        <p class="login-text">Already a user? <a href="login.html">Login instead</a></p>
    </div>
</div>

<style>
    .error-message {
        color: red;
        font-size: 0.9em;
    }
</style>

 <!-- Linking External JavaScript -->
 <script src="javascript/signup.js"></script>

</body>
</html>
