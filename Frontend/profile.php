<?php
// Start session
session_start();

// Database connection
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "FiscalPoint"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['Uid'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['Uid'];

// Fetch user details
$query = "SELECT Uname, email, Phone_no FROM User WHERE Uid = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found!";
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'">

    <aside class="sidebar">
        <div class="profile">
            <img src="css/profile.png" alt="Profile Image" class="avatar">
        </div>
        <ul class="menu">
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <strong>Dashboard</strong></a></li><br>
    <li><a href="addincome.php"><i class="fas fa-wallet"></i> <span style="font-weight: bold;">Income</span></a></li><br>
    <li><a href="setbudget.php"><i class="fas fa-coins"></i> <strong>Budget</strong></a></li><br>
    <li><a href="addexpense.php"><i class="fas fa-plus-circle"></i> <strong>Add Expense</strong></a></li><br>
    
    <li class="dropdown">
                <a href="#"><i class="fas fa-chart-bar"></i> <strong><em>Graph Reports:</em></strong></a>
                <ul>
                    <li><a href="linegraph.php"><i class="fas fa-chart-line"></i> Line Graph Report</a></li>
                    <li><a href="piegraph.php"><i class="fas fa-chart-pie"></i> Pie Graph Report</a></li>
                </ul>
    </li><br>
    <li class="dropdown">
                <a href="#"><i class="fas fa-table"></i> <strong><em>Tabular Reports:</em></strong></a><br>
                <ul>
                    <li><a href="tabularreport.php"><i class="fas fa-list-alt"></i> All Expenses</a></li>
                    <li><a href="categorywisereport.php"><i class="fas fa-layer-group"></i> Category-wise Expense</a></li>
                </ul>
    </li><br>
    <li><a href="insights.php"><i class="fas fa-lightbulb"></i> <strong>Insights</strong></a></li><br>
    <li><a href="predictions.php"><i class="fas fa-chart-line"></i> <strong>Predictions</strong></a></li><br>
    <li><a href="profile.php"><i class="fas fa-user"></i> <strong>Profile</strong></a></li><br>
    <li><a href="query.php"><i class="fas fa-question-circle"></i> <strong>Query</strong></a></li><br>

    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <strong>Logout</strong></a></li><br>
        </ul>
    </aside>

    <div class="profile-container">
        <div class="profile-card">
            <h1>User Details</h1>
            <h2>Name:</h2>
            <div class="input-field"><?php echo htmlspecialchars($user['Uname']); ?></div>

            <h2>Email:</h2>
            <div class="input-field"><?php echo htmlspecialchars($user['email']); ?></div>

            <h2>Phone Number:</h2>
            <div class="input-field"><?php echo htmlspecialchars($user['Phone_no']); ?></div>
            <br>
            <div class="button-group">
                <!-- Update reset password button to link to reset_password.php -->
                <a href="reset_password.php" class="btn reset-btn">Reset Password</a>
                <a href="forgot_password.php" class="btn forgot-btn">Forgot Password</a>
                <button class="btn delete-btn" onclick="confirmDelete()">Delete Account</button>
            </div>
        </div>
    </div>

<!-- JavaScript for Delete Account Confirmation -->
<script>
function confirmDelete() {
    let confirmation = confirm("Are you sure you want to delete your account?");
    if (confirmation) {
        fetch('delete_account.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ delete: true })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Account deleted successfully!");
                window.location.href = "landing.html"; // Redirect to landing page
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
</script>

</body>
</html>
