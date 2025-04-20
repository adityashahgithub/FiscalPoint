<?php
session_start();
if (!isset($_SESSION["Uid"]) || $_SESSION["Role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Today's Expense
$sql = "SELECT SUM(amount) AS total FROM Expense WHERE DATE(Date) = CURDATE()";
$today_expense = $conn->query($sql)->fetch_assoc()['total'] ?? 0;

// Yesterday's Expense
$sql = "SELECT SUM(amount) AS total FROM Expense WHERE DATE(Date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
$yesterday_expense = $conn->query($sql)->fetch_assoc()['total'] ?? 0;

// Last 7 Days Expense
$sql = "SELECT SUM(amount) AS total FROM Expense WHERE Date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$last7_expense = $conn->query($sql)->fetch_assoc()['total'] ?? 0;

// Last 30 Days Expense
$sql = "SELECT SUM(amount) AS total FROM Expense WHERE Date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$last30_expense = $conn->query($sql)->fetch_assoc()['total'] ?? 0;

// This Year Expense
$sql = "SELECT SUM(amount) AS total FROM Expense WHERE YEAR(Date) = YEAR(CURDATE())";
$yearly_expense = $conn->query($sql)->fetch_assoc()['total'] ?? 0;

// Total Expenses
$sql = "SELECT SUM(amount) AS total FROM Expense";
$total_expense = $conn->query($sql)->fetch_assoc()['total'] ?? 0;

// Total Listed Categories
$sql = "SELECT COUNT(DISTINCT Category) AS total FROM Expense";
$total_categories = $conn->query($sql)->fetch_assoc()['total'] ?? 0;

// Total Registered Users
$sql = "SELECT COUNT(*) AS total FROM User";
$total_users = $conn->query($sql)->fetch_assoc()['total'] ?? 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Fiscal Point</title>
    <link rel="stylesheet" href="css/admin_dashboard.css"> <!-- Your existing CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<header>
    <img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'">
</header>

<div style="display: flex; width: 100%; height: 100vh; padding: 20px; box-sizing: border-box;">
    
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="profile">
            <img src="css/profile.png" alt="Admin Profile" class="avatar">
        </div>
        <ul class="menu">
            <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li><br>
            <li><a href="admin_category.php"><i class="fas fa-layer-group"></i> Category</a></li><br>
            <li><a href="admin_registered_users.php"><i class="fas fa-users-cog"></i> Reg Users</a></li><br>
            <li><a href="admin_profile.php"><i class="fas fa-user"></i> Profile</a></li><br>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li><br>
        </ul>
    </aside>

    <div class="content">
        <h1>Welcome, Admin <?php echo $_SESSION["Uname"]; ?>!</h1>
    <!-- Dashboard Content -->
    <main class="dashboard">
    <div class="grid-container">
        <div class="box">
            <h3>Today's Expense:</h3>
            <div class="content-box">
                <p><?php echo $today_expense; ?></p>
            </div>
        </div>
        <div class="box">
            <h3>Yesterday's Expense:</h3>
            <div class="content-box">
                <p><?php echo $yesterday_expense; ?></p>
            </div>
        </div>
        <div class="box">
            <h3>Last 7day's Expense:</h3>
            <div class="content-box">
                <p><?php echo $last7_expense; ?></p>
            </div>
        </div>
        <div class="box">
            <h3>Last 30day's Expense:</h3>
            <div class="content-box">
                <p><?php echo $last30_expense; ?></p>
            </div>
        </div>
        <div class="box">
            <h3>This Year Expense:</h3>
            <div class="content-box">
                <p><?php echo $yearly_expense; ?></p>
            </div>
        </div>
        <div class="box">
            <h3>Total Expenses:</h3>
            <div class="content-box">
                <p><?php echo $total_expense; ?></p>
            </div>
        </div>
        <div class="box">
            <h3>Total Listed Categories:</h3>
            <div class="content-box">
                <p><?php echo $total_categories; ?></p>
            </div>
        </div>
        <div class="box">
            <h3>Total Registred Users:</h3>
            <div class="content-box">
                <p><?php echo $total_users; ?></p>
            </div>
        </div>
    </div>
   
</body>
</html>
