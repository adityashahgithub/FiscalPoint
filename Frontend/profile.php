<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Check if the user is logged in
if (!isset($_SESSION["Uid"])) {
    die("Error: User not logged in. <a href='login.php'>Login here</a>");
}

$user_id = $_SESSION["Uid"];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch User Details (including email, password, and phone number)
$sql_user = "SELECT Uname, email, Password, Phone_no FROM User WHERE Uid = ?";
$stmt_user = $conn->prepare($sql_user);
if (!$stmt_user) {
    die("User Query preparation failed: " . $conn->error);
}
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_details = $result_user->fetch_assoc();
$stmt_user->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
    <header>
        <img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'">
    </header>
    
    <aside class="sidebar">
        <div class="profile">
            <img src="css/profile.png" alt="Profile Image" class="avatar">
        </div>
        <ul class="menu">
            <li><a href="dashboard.php">Dashboard</a></li><br>
            <li><a href="setbudget.php">Budget</a></li><br>
            <li><a href="addexpense.php">Add Expense</a></li><br>
            <li><a href="linegraph.php">Line Graph Report</a></li><br>
            <li><a href="piegraph.php">Pie Graph Report</a></li><br>
            <li><a href="tabularreport.php">Tabular Category Wise Report</a></li><br>
            <li><a href="profile.html">Profile</a></li><br>
            <li><a href="logout.php">Logout</a></li><br>
        </ul>
    </aside>
    
    <div class="user-container">
    <h2>User Profile</h2>
        <div class="user-details">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user_details['Uname']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user_details['email']); ?></p>
            <p><strong>Password:</strong> <?php echo htmlspecialchars($user_details['Password']); ?></p>
            <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($user_details['Phone_no']); ?></p>
        </div>
    </div>
</body>
</html>
