<?php
// Start session
session_start();

// Database connection parameters
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

// Enable detailed error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $uname = sanitize_input($_POST["Uname"]);
    $email = sanitize_input($_POST["email"]);
    $phone = sanitize_input($_POST["Phone_no"]);
    $password = sanitize_input($_POST["Password"]);
    $created_at = date("Y-m-d H:i:s");

    // Debugging - Check if values are being captured correctly
    error_log("Uname: " . $uname);
    error_log("Email: " . $email);
    error_log("Phone: " . $phone);
    error_log("Password: " . $password);

    // Hash the password before storing it in the database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists in the database
    $check_query = "SELECT * FROM User WHERE email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email already signed up. Please use a different email.');</script>";
    } else {
        // Insert new user data into database
        $insert_query = "INSERT INTO User (`Uname`, `email`, `Phone_no`, `Password`, `Created_At`) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        
        if ($stmt === false) {
            die("Error in SQL query: " . $conn->error);
        }

        $stmt->bind_param("sssss", $uname, $email, $phone, $hashed_password, $created_at);
        
        if ($stmt->execute()) {
            // Store user session (optional)
            $_SESSION['Uid'] = $conn->insert_id;
            $_SESSION['Uname'] = $uname;

            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<script>alert('Registration failed. Please try again later.');</script>";
        }
    }

    // Close the statement
    $stmt->close();
}

// Close database connection
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
               <li><a href="landing.html#aboutus">About Us</a></li> 
           </ul>
       </nav>
   </header>    

    <!-- ======= Signup Form Section ======= -->
    <div class="signup-container">
        <h2>Welcome to Fiscal Point!</h2>

        <!-- Signup Form -->
        <form class="signup-form" action="signup.php" method="POST"method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <!-- Email Input -->
    <label for="email">Enter your email:</label>
    <input type="email" id="email" name="email" placeholder="Email" required>
    <span id="email-error" class="error-message"></span>

    <!-- Full Name Input -->
    <label for="Uname">Full Name:</label>
    <input type="text" id="Uname" name="Uname" placeholder="Full Name" required>

    <!-- Phone Number Input --> 
    <label for="Phone_no">Phone Number:</label>
    <input type="tel" id="Phone_no" name="Phone_no" placeholder="Enter Phone Number" required pattern="[0-9]{10}" maxlength="10">

    <!-- Password Input -->
    <label for="Password">Password:</label>
    <input type="password" id="Password" name="Password" placeholder="Password" required>
    <span id="password-error" class="error-message"></span>
    <label for="ConfirmPassword">Confirm Password:</label>
        <input type="password" id="ConfirmPassword" name="ConfirmPassword" placeholder="Confirm Password" required>
        <span id="confirm-password-error" class="error-message"></span>
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
