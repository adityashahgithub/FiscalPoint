<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
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

// Sanitize input function
function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $uname = sanitize_input($_POST["Uname"]);
    $email = sanitize_input($_POST["email"]);
    $phone = sanitize_input($_POST["Phone_no"]);
    $password = sanitize_input($_POST["Password"]);
    $confirm_password = sanitize_input($_POST["ConfirmPassword"]);
    $role = sanitize_input($_POST["Role"]);
    $created_at = date("Y-m-d H:i:s");

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match. Please try again.'); window.history.back();</script>";
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $check_query = "SELECT * FROM User WHERE email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email already exists. Please use a different email.');</script>";
    } else {
        // Insert new user into database
        $insert_query = "INSERT INTO User (Uname, email, Phone_no, Password, Created_At, Role) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssssss", $uname, $email, $phone, $hashed_password, $created_at, $role);

        if ($stmt->execute()) {
            // Get the inserted user details (including role)
            $user_id = $conn->insert_id;
            $_SESSION["Uid"] = $user_id;
            $_SESSION["Uname"] = $uname;
            $_SESSION["email"] = $email;
            $_SESSION["Role"] = $role;  // Store the role in the session

            // Role-based redirection
            if ($role === "admin") {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            echo "<script>alert('Registration failed. Please try again later.');</script>";
        }
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
    <header>
        <!-- LOGO-->
       <img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'"> 
       
       <!-- NAVIGATION BAR -->
       <nav class="navbar">
           <ul>
           <li><a href="admin_category.php"><i class="fas fa-layer-group"></i> Category</a></li><br>
        <li><a href="admin_registered_users.php"><i class="fas fa-users-cog"></i> Reg Users</a></li><br>
        <li><a href="admin_query.php"><i class="fas fa-user"></i> <strong>Query</strong></a></li><br>
        <li><a href="add_admin.php"><i class="fas fa-user"></i> <strong>Add Admin</strong></a></li><br>
        <li><a href="manage_admin.php"><i class="fas fa-user"></i> <strong>Manage Admin</strong></a></li><br>
        <li><a href="admin_profile.php"><i class="fas fa-user"></i> Profile</a></li><br>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li><br>
           </ul>
       </nav>
   </header>    

    <!--  Signup Form Section -->
    <div class="signup-container">
        <h2>Welcome to Fiscal Point!</h2>

        <!-- Signup Form -->
        <form class="signup-form" action="signup.php" method="POST"method="post" action="signup.php">
    <!-- Email Input -->
    <input type="hidden" name="Role" value="admin">
    <label for="email">Enter admin email:</label>
    <input type="email" id="email" name="email" placeholder="Email" required>
    <span id="email-error" class="error-message"></span>

    <!-- Full Name Input -->
    <label for="Uname">Amin Name:</label>
    <input type="text" id="Uname" name="Uname" placeholder="Full Name" required>

    <!-- Phone Number Input --> 
    <label for="Phone_no">Phone Number:</label>
    <input type="tel" id="Phone_no" name="Phone_no" placeholder="Enter Phone Number" required pattern="[0-9]{10}" maxlength="10">

    <!-- Password Input -->
    <label for="Password">Create Password:</label>
    <input type="password" id="Password" name="Password" placeholder="Password" required>
    <span id="password-error" class="error-message"></span>

    <!-- Confirm Password Input -->
    <label for="ConfirmPassword">Confirm Password:</label>
    <input type="password" id="ConfirmPassword" name="ConfirmPassword" placeholder="Confirm Password" required>
    <span id="confirm-password-error" class="error-message"></span>
    
    

    <!-- Signup Button -->
    <button type="submit">Get Started</button>
    
</form>

        <!-- Login Redirect -->
        <p class="login-text">Already a login? <a href="login.php">Login instead</a></p>
    </div>
</div>

<style>
    .error-message {
        color: red;
        font-size: 0.9em;
    }
</style>

 <!--  JavaScript -->
 <script src="javascript/signup.js"></script>

</body>
</html>
