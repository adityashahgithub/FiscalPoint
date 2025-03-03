<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'Fiscalpoint');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch the user's credentials from the database
    $sql = "SELECT Password FROM User WHERE Email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashedPassword = $row['Password'];

        // Verify the password
        if (password_verify($password, $hashedPassword)) {
            echo "Login successful! Welcome, $email.";
        } else {
            echo "Invalid password. Please try again.";
        }
    } else {
        echo "No user found with this email.";
    }
    $conn->close();
}
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
            <form>
                <label for="email">Enter your email:</label>
                <input type="email" id="email" placeholder="Email" required>
                
                <label for="password">Password:</label>
                <input type="password" id="password" placeholder="Password" required>
                
                <button type="submit">Login</button>
            </form>
            
            <!-- Signup Link -->
            <p class="signup-text">New user? <a href="signup.html">Sign up instead</a></p>

        </div> <!-- End of Login Box -->
    
    </div> <!-- End of Main Container -->

</body>
</html>
