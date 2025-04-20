<?php 
// Start session to track logged-in user
session_start();

// Database connection parameters
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

// Check if user is logged in
if (!isset($_SESSION["Uid"])) {
    echo "<script>alert('Session expired. Please log in again.'); window.location.href='login.php';</script>";
    exit();
}

$uid = $_SESSION["Uid"];  // Fetch the logged-in user's ID

// Flask API URL (Fixing the variable issue)
$api_url = "http://127.0.0.1:5000/predict_budget?user_id=" . $uid;

// Call Flask API using file_get_contents
$response = file_get_contents($api_url);

// Convert JSON response to PHP array
$prediction = json_decode($response, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expense</title>
    <link rel="stylesheet" href="css/addincome.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'"> 
    </header>

    <aside class="sidebar">
        <div class="profile">
            <img src="css/profile.png" alt="Profile Image" class="avatar">
        </div>
        <ul class="menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <strong>Dashboard</strong></a></li><br>
            <li><a href="addincome.php"><i class="fas fa-wallet"></i> <strong>Income</strong></a></li><br>
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
            <li><a href="insights.php"><i class="fas fa-robot"></i> <strong>Insights</strong></a></li><br>
            <li><a href="predictions.php"><i class="fas fa-robot"></i> <strong>Predictions</strong></a></li><br>
            <li><a href="profile.php"><i class="fas fa-user"></i> <strong>Profile</strong></a></li><br>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <strong>Logout</strong></a></li><br>
        </ul>
    </aside>

    <!-- Budget Prediction Display -->
    <div class="prediction-card">
        <h3>Next Month's Predicted Expense</h3>
        <?php 
            if (isset($prediction['predicted_expense'])) {
                echo "<p>₹" . $prediction['predicted_expense'] . " estimated for " . $prediction['month'] . "/" . $prediction['year'] . "</p>";
            } else {
                echo "<p>" . $prediction['message'] . "</p>";
            }
        ?>
    </div>

</body>
</html>
