<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Sanitize input
function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

// On form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST["email"]);
    $password = sanitize_input($_POST["password"]);

    // Fetch user with role
    $query = "SELECT Uid, Uname, Password, Role FROM User WHERE email = ?";
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

        if (password_verify($password, $user["Password"])) {
            $_SESSION["Uid"] = $user["Uid"];
            $_SESSION["Uname"] = $user["Uname"];
            $_SESSION["email"] = $email;
            $_SESSION["Role"] = $user["Role"]; // Store role

            // Role-based redirect
            if ($user["Role"] === "admin") {
                header("Location: admin_registered_users.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            echo "<script>alert('Invalid password!'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid email!'); window.location.href='login.php';</script>";
    }

    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <!-- Linking CSS File -->
    <link rel="stylesheet" href="css/login.css"> 
</head>
<body>

    <div class="container">
        <header>
            <!-- LOGO-->
            <img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'">
            <!-- NAVIGATION BAR -->  
            <nav class="navbar">
                <ul>
                    <li><a href="landing.html">Home</a></li>
                    <li><a href="login.php">Expense Tracker</a></li>
                    <li><a href="landing.html#aboutus">About Us</a></li> 
                </ul>
            </nav>
        </header>
        <div class="login-box">
            <div class="avatar">
                <img src="css/profile.png" alt="User Icon">
            </div>
            <!-- LOGIN FORM -->
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <label for="email">Enter your email:</label>
                <input type="email" id="email" name="email" placeholder="Email" required>
                
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Password" required>
                
                <button type="submit">Login</button>
            </form>
            
            <!-- Signup Link -->
            <p class="signup-text">New user? <a href="signup.php">Sign up instead</a></p>

             <!-- Forgot Password Link -->
             <p class="forgotpassword-text"><a href="forgot_password.php">Forgot Password?</a></p>

        </div>
    </div>
   
</body>
</html>
