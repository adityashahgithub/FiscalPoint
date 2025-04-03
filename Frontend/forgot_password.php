<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use Dotenv\Dotenv;

session_start();

// Enable Error Reporting and Logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure Composer's autoload is included
require_once __DIR__ . '/../vendor/autoload.php';

// Load Environment Variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Database Credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Only process POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["email"])) {
        die("<script>alert('Email field cannot be empty.'); window.history.back();</script>");
    }

    $email = trim($_POST["email"]);

    // Check if the email exists
    $query = "SELECT email FROM User WHERE email = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(50));
        $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token valid for 1 hour

        // Store token in database
        $updateQuery = "UPDATE User SET reset_token=?, token_expires=? WHERE email=?";
        $stmt = $conn->prepare($updateQuery);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sss", $token, $expires_at, $email);
        $stmt->execute();

        // Close session before sending email
        session_write_close();

        // Ensure reset_password.php file exists
        $resetPasswordPath = __DIR__ . "/reset_password.php";
        if (!file_exists($resetPasswordPath)) {
            die("<script>alert('Reset password page is missing. Please contact support.'); window.location.href='login.php';</script>");
        }

        // Get user's IP address
        $user_ip = $_SERVER['REMOTE_ADDR'];

        // Construct password reset link
        $resetLink = "http://localhost/FiscalPoint/frontend/reset_password.php?token=$token";

        // Personalize the email body
        $subject = "Password Reset Request";
        $body = "Hello,

A password reset request was made for the account associated with this email.

User Email: $email
Request IP: $user_ip
Request Time: " . date("Y-m-d H:i:s") . "

Click the link below to reset your password:
$resetLink

If you did not request this, please ignore this email.

This link is valid for 1 hour.

Best Regards,
FiscalPoint Support";

        // Send email
        if (sendMail($email, $subject, $body)) {
            echo "<script>alert('Password reset link sent! Check your email.'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Error sending email. Please try again later.');</script>";
        }
    } else {
        echo "<script>alert('Email not found. Please check and try again.'); window.history.back();</script>";
    }
}

// Close database connection
$conn->close();

// Function to send email
function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['EMAIL_USERNAME']; // Load from .env
        $mail->Password   = $_ENV['EMAIL_PASSWORD']; // Load from .env
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587; 
        $mail->SMTPDebug  = 0;  // Disable debugging in production

        // Sender & Recipient
        $mail->setFrom($_ENV['EMAIL_USERNAME'], 'FiscalPoint Support');
        $mail->addAddress($to);

        // Email Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        return $mail->send();
    } catch (Exception $e) {
        error_log("Mail error: " . $mail->ErrorInfo);
        return false;
    }
}
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
