<?php 
session_start();

// DB connection
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "FiscalPoint"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if logged in
if (!isset($_SESSION["Uid"])) {
    echo "<script>alert('Session expired. Please log in again.'); window.location.href='login.php';</script>";
    exit();
}

$uid = $_SESSION["Uid"];
$api_url = "http://127.0.0.1:5000/predict_budget?user_id=" . $uid;

$response = @file_get_contents($api_url);  // Suppress warning

if ($response === FALSE) {
    $prediction = ['message' => 'Unable to connect to prediction server. Please try again later.'];
} else {
    $prediction = json_decode($response, true);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Predictions</title>
    <link rel="stylesheet" href="css/predictions.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'"> 
    </header>

    <!-- Sidebar -->
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

    <!-- Prediction Section -->
    <div class="prediction-card">
        <h3>🧠 AI-Powered Budget Prediction</h3>

        <?php 
            if (isset($prediction['summary'])) {
                echo "<div class='summary-text'>" . htmlspecialchars($prediction['summary']) . "</div>";
            } else {
                echo "<p><strong>⚠️ " . htmlspecialchars($prediction['message']) . "</strong></p>";
            }

            if (isset($prediction['graph_base64'])) {
                echo "<img src='data:image/png;base64," . $prediction['graph_base64'] . "' class='prediction-graph' alt='Expense Prediction Graph'>";
            }
        ?>
    </div>
    
</body>
</html>
