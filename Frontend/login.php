<?php
// Start the session
session_start();

// Database connection parameters
$servername = "localhost"; // Replace with your server name
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "FiscalPoint"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input data
function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST["email"]);
    $password = sanitize_input($_POST["password"]);

    // Fetch user data from the database
    $query = "SELECT Uid, Uname, Password FROM User WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Fetch user data
        $user = $result->fetch_assoc();

        // Verify the hashed password
        if (password_verify($password, $user["Password"])) {
            // Store user data in session
            $_SESSION["Uid"] = $user["Uid"];
            $_SESSION["Uname"] = $user["Uname"];
            $_SESSION["email"] = $email;

            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<script>alert('Invalid credentials. Please try again.'); window.location.href='login.html';</script>";
        }
    } else {
        echo "<script>alert('Invalid credentials. Please try again.'); window.location.href='login.html';</script>";
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
                
                <label for="password">Password:</label>
                <input type="password" id="password" placeholder="Password" required>
                
                <button type="submit">Login</button>
            </form>
            
            <!-- Signup Link -->
            <p class="signup-text">New user? <a href="signup.php">Sign up instead</a></p>

        </div> <!-- End of Login Box -->
    
    </div> <!-- End of Main Container -->

</body>
</html>
