<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";

// Create connection
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
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token mismatch!");
    }

    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    // Validate password strength
    if (strlen($newPassword) < 8 || !preg_match('/[A-Z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
        $error = "Password must be at least 8 characters long, contain an uppercase letter and a number.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match!";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE User SET Password = ? WHERE Uid = ?");
        $stmt->bind_param("si", $hashedPassword, $uid);

        if ($stmt->execute()) {
            session_regenerate_id(true); // Prevent session fixation
            session_destroy(); // Logout user after password reset
            echo "<script>
                    alert('Password reset successfully! Please log in.');
                    window.location.href = 'login.php';
                  </script>";
            exit();
        } else {
            $error = "Failed to reset password.";
        }
        $stmt->close();
    }
}

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/reset_password.css">
    <script>
        function validateForm() {
            let password = document.getElementById("new_password").value;
            let confirmPassword = document.getElementById("confirm_password").value;
            let errorBox = document.getElementById("error-box");

            if (password.length < 8 || !/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
                errorBox.innerText = "Password must be at least 8 characters, contain an uppercase letter and a number.";
                return false;
            }
            if (password !== confirmPassword) {
                errorBox.innerText = "Passwords do not match!";
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

    <div class="container">
        <header>
            <img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'">
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

            <p id="error-box" style="color:red;"><?php echo $error; ?></p>

            <form method="POST" onsubmit="return validateForm();">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

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
