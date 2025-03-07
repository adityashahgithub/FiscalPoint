<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session
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
    die("Database Connection Failed: " . $conn->connect_error);
}

// Function to sanitize input data
function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST["email"]);
    $password = sanitize_input($_POST["password"]);

    // Fetch user data from the database
    $query = "SELECT Uid, Uname, Password FROM User WHERE email = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Prepare Statement Failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // If user found
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user["Password"])) {
            // Store user details in session
            $_SESSION["Uid"] = $user["Uid"];
            $_SESSION["Uname"] = $user["Uname"];
            $_SESSION["email"] = $email;

            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<script>alert('Invalid email or password!'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid email or password!'); window.location.href='login.php';</script>";
    }

    // Close statement
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
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <label for="email">Enter your email:</label>
                <input type="email" id="email" placeholder="Email" required>
                
                <label for="Password">Password:</label>
                <input type="password" id="password" placeholder="Password" required>
                
                <button type="submit">Login</button>
            </form>
            
            <!-- Signup Link -->
            <p class="signup-text">New user? <a href="signup.php">Sign up instead</a></p>

        </div> <!-- End of Login Box -->
    
    </div> <!-- End of Main Container -->

</body>
</html>
