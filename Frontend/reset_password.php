<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed.");
}

// Ensure user is logged in
if (!isset($_SESSION['Uid'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['Uid'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match!";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE User SET Password = ? WHERE Uid = ?");
        $stmt->bind_param("si", $hashedPassword, $uid);

        if ($stmt->execute()) {
            $success = "Password reset successfully!";
        } else {
            $error = "Failed to reset password.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/reset_password.css"> <!-- Link to new CSS -->
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
        
        <div class="reset-password-box">
            <div class="avatar">
                <img src="css/profile.png" alt="User Icon">
            </div>
            <h2>Reset Password</h2>

            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>

            <form method="POST">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
                
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                
                <button type="submit">Reset Password</button>
            </form>
            
            <p class="back-to-profile"><a href="profile.php">Back to Profile</a></p>
        </div>
    </div>

</body>
</html>
