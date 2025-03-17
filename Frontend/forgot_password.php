<?php
session_start();
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    // Check if the email exists
    $query = "SELECT * FROM User WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(50));
        $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token valid for 1 hour

        // Store token in database
        $insertQuery = "UPDATE User SET reset_token=?, token_expires=? WHERE email=?";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("sss", $token, $expires_at, $email);
        $stmt->execute();

        // Send reset link (Replace this with actual email sending logic)
        $resetLink = "http://yourwebsite.com/reset_password.php?token=$token";
        echo "<script>alert('A password reset link has been sent to your email.');</script>";

        // Here you can use PHPMailer or `mail()` function to send the email
    } else {
        echo "<script>alert('Email not found. Please check and try again.');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/forgot_password.css">
</head>
<body>
<div class="container">
        <header>
            <!-- LOGO -->
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

    <div class="forgot-password-box">
        <h2>Forgot Password</h2>
        <p>Enter your email address and we will send you a link to reset your password.</p>

        <form action="forgot_password.php" method="POST">
            <label for="email">Email Address:</label>
            <input type="email" name="email" id="email" required>
            <button type="submit">Send Reset Link</button>
        </form>

        <p><a href="login.php">Back to Login</a></p>
    </div>
</body>
</html>
