<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start(); // Start session

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // Prepare and execute SQL statement
        $stmt = $conn->prepare("SELECT Uid, Uname, Password FROM User WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            // Check if user exists
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // Verify password
                if (password_verify($password, $row['Password'])) {
                    session_regenerate_id(true); // Prevent session fixation attacks
                    $_SESSION['Uid'] = $row['Uid']; 
                    $_SESSION['Uname'] = $row['Uname'];

                    // Redirect to Dashboard
                    header("Location: Dashboard.html");
                    exit();
                } else {
                    echo "<script>alert('Invalid email or password'); window.location.href='login.php';</script>";
                }
            } else {
                echo "<script>alert('Invalid email or password'); window.location.href='login.php';</script>";
            }
            $stmt->close();
        } else {
            die("Database query failed: " . $conn->error);
        }
    } else {
        echo "<script>alert('Please fill in all fields'); window.location.href='login.php';</script>";
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="css/login.css">  <!-- Linking CSS File -->
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
                <li><a href="#">Expense Tracker</a></li>
                <li><a href="#">Cost of Living Calculator</a></li>
            </ul>
        </nav>
    </header> 

        <!-- Login Box -->
        <div class="login-box">
            
            <!-- User Avatar -->
            <div class="avatar">
                <img src="css/userpfp.png" alt="User Icon">
            </div>
            
            <!-- Login Form -->
            <form action="login.php" method="POST">
                <label for="email">Enter your email:</label>
                <input type="email" id="email" name="email" placeholder="Email" required>
                
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Password" required>
                
                <button type="submit">Login</button>
            </form>
            
            <!-- Signup Link -->
            <p class="signup-text">New user? <a href="signupt.php">Sign up instead</a></p>

        </div> <!-- End of Login Box -->
    
    </div> <!-- End of Main Container -->

</body>
</html>
