<?php
// Start session and enable error reporting
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
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

// Fetch Uid from session
$uid = $_SESSION['Uid'];

// Fetch today's expense
$sql_today = "SELECT SUM(amount) AS total_today FROM Expense WHERE Uid = ? AND DATE(Date) = CURDATE()";
$stmt = $conn->prepare($sql_today);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result_today = $stmt->get_result();
$row_today = $result_today->fetch_assoc();
$today_expense = isset($row_today['total_today']) ? $row_today['total_today'] : 0;

// Fetch yesterday's expense
$sql_yesterday = "SELECT SUM(amount) AS total_yesterday FROM Expense WHERE Uid = ? AND DATE(Date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
$stmt = $conn->prepare($sql_yesterday);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result_yesterday = $stmt->get_result();
$row_yesterday = $result_yesterday->fetch_assoc();
$yesterday_expense = isset($row_yesterday['total_yesterday']) ? $row_yesterday['total_yesterday'] : 0;

// Fetch monthly expense
$sql_monthly = "SELECT SUM(amount) AS total_monthly FROM Expense WHERE Uid = ? AND MONTH(Date) = MONTH(CURDATE())";
$stmt = $conn->prepare($sql_monthly);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result_monthly = $stmt->get_result();
$row_monthly = $result_monthly->fetch_assoc();
$monthly_expense = isset($row_monthly['total_monthly']) ? $row_monthly['total_monthly'] : 0;

// Fetch yearly expense
$sql_yearly = "SELECT SUM(amount) AS total_yearly FROM Expense WHERE Uid = ? AND YEAR(Date) = YEAR(CURDATE())";
$stmt = $conn->prepare($sql_yearly);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result_yearly = $stmt->get_result();
$row_yearly = $result_yearly->fetch_assoc();
$yearly_expense = isset($row_yearly['total_yearly']) ? $row_yearly['total_yearly'] : 0;

// Fetch user's budget for the current month
$currentMonth = date("F");
$sql_budget = "SELECT Amount FROM Budget WHERE Uid = ? AND Month = ?";
$stmt = $conn->prepare($sql_budget);
$stmt->bind_param("is", $uid, $currentMonth);
$stmt->execute();
$result_budget = $stmt->get_result();
$row_budget = $result_budget->fetch_assoc();
$monthly_budget = isset($row_budget['Amount']) ? $row_budget['Amount'] : "No budget set";

// Determine text color for monthly expense
$expense_color = ($monthly_expense > $monthly_budget && $monthly_budget != "No budget set") ? 'red' : 'green';

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css"> <!-- External CSS file -->
</head>
<body>
    <header> 
        <!-- LOGO -->
        <img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'">
    </header>

    <!-- SIDE BAR -->
    <aside class="sidebar">
        <div class="profile">
            <img src="css/profile.png" alt="Profile Image" class="avatar">
        </div>
        <ul class="menu">
            <li><a href="dashboard.php"><strong>Dashboard</strong></a></li><br>
            <li><a href="setbudget.php"><strong>Budget</strong></a></li><br>
            <li><a href="addexpense.php"><strong>Add Expense</strong></a></li><br>
            <li class="dropdown">
                <a href="#"><strong><em>Graph Reports:</em></strong></a>
                <ul>
                    <li><a href="linegraph.php">Line Graph Report</a></li>
                    <li><a href="piegraph.php">Pie Graph Report</a></li>
                </ul>
            </li><br>
            <li>
                <a href="#"><strong><em>Tabular Reports:</em></strong></a><br>
                <ul>
                    <li><a href="tabularreport.php">All Expenses</a></li>
                    <li><a href="categorywisereport.php">Category wise Expense</a></li>
                </ul>
            </li><br>
            <li><a href="profile.php"><strong>Profile</strong></a></li><br>
            <li><a href="logout.php"><strong>Logout</strong></a></li><br>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="dashboard">
        <div>
            <h3 class="budget-text">Your Budget for <?php echo $currentMonth; ?>:</h3>
            <div class="Budget">
                <p><?php echo $monthly_budget; ?></p>
            </div>
        </div><br>

        <div class="expense-box">
            <h3>Today's Expense:</h3>
            <div class="expense-card">
                <p style="text-align: center; font-weight: bold;"><?php echo $today_expense; ?></p>
            </div>
        </div>

        <div class="expense-box">
            <h3>Yesterday's Expense:</h3>
            <div class="expense-card"><?php echo $yesterday_expense; ?></div>
        </div>

        <div class="expense-box">
            <h3>Monthly Expense:</h3>
            <div class="expense-card" style="color: <?php echo $expense_color; ?>;">
                <?php echo $monthly_expense; ?>
            </div>
        </div>

        <div class="expense-box">
            <h3>This Year Expense:</h3>
            <div class="expense-card"><?php echo $yearly_expense; ?></div>
        </div>
    </main>
</body>
</html>
