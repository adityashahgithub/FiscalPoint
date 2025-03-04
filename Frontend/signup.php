<?php
// Database connection details
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "FiscalPoint"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $Uname = $_POST['Uname'];
    $email = $_POST['email'];
    $Phone_no = $_POST['Phone_no'];
    $Password = $_POST['Password'];

    // Hash the password for security
    $hashed_password = password_hash($Password, PASSWORD_DEFAULT);

    // Get the current timestamp
    $Created_At = date('Y-m-d H:i:s');

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO User (Uname, email, Password, Phone_no, Created_At) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $Uname, $email, $hashed_password, $Phone_no, $Created_At);

    // Execute the statement
    if ($stmt->execute()) {
        header("Location: Dashboard.html");
    exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Fiscal Point</title>
    <link rel="stylesheet" href="css/signup.css">  
</head>
<body>
 <!-- Main Container -->
 <div class="container">
    <!-- Header Section -->
    <header>
       <img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'"> 
       
       <!-- Navigation Menu -->
       <nav class="navbar">
           <ul>
               <li><a href="landing.html">Home</a></li>
               <li><a href="login.php">Expense Tracker</a></li>
               <li><a href="login.php">Cost of Living Calculator</a></li>
           </ul>
       </nav>
   </header>    

    <!-- ======= Signup Form Section ======= -->
    <div class="signup-container">
        <h2>Welcome to Fiscal Point!</h2>

        <!-- Signup Form -->
        <form class="signup-form" action="signup.php" method="POST">
    <!-- Email Input -->
    <label for="email">Enter your email:</label>
    <input type="email" id="email" name="email" placeholder="Email" required>

    <!-- Full Name Input -->
    <label for="Uname">Full Name:</label>
    <input type="text" id="Uname" name="Uname" placeholder="Full Name" required>

    <!-- Phone Number Input --> 
    <label for="Phone_no">Phone Number:</label>
    <input type="tel" id="Phone_no" name="Phone_no" placeholder="Enter Phone Number" required pattern="[0-9]{10}" maxlength="10">

    <!-- Password Input -->
    <label for="Password">Password:</label>
    <input type="password" id="Password" name="Password" placeholder="Password" required>

    <!-- Signup Button -->
    <button type="submit">Get Started</button>
</form>

        <!-- Login Redirect -->
        <p class="login-text">Already a user? <a href="login.php">Login instead</a></p>
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
