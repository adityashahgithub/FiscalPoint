<?php
session_start();
if (!isset($_SESSION["Uid"]) || $_SESSION["Role"] !== "admin") {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Fiscal Point</title>
    <link rel="stylesheet" href="css/dashboard.css"> <!-- Use your existing CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container {
            margin-left: 250px;
            padding: 40px;
            color: #333;
        }
        .admin-container h1 {
            font-size: 32px;
            margin-bottom: 20px;
        }
        .admin-boxes {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .admin-box {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
            flex: 1 1 300px;
        }
        .admin-box h3 {
            margin-top: 0;
        }
    </style>
</head>
<body>

<header>
    <img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'">
</header>

<aside class="sidebar">
    <div class="profile">
        <img src="css/profile.png" alt="Admin Profile" class="avatar">
    </div>
    <ul class="menu">
        <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> <strong>Admin Dashboard</strong></a></li><br>
        <li><a href="manage_users.php"><i class="fas fa-users-cog"></i> <strong>Manage Users</strong></a></li><br>
        <li><a href="view_reports.php"><i class="fas fa-chart-bar"></i> <strong>Reports</strong></a></li><br>
        <li><a href="profile.php"><i class="fas fa-user"></i> <strong>Profile</strong></a></li><br>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <strong>Logout</strong></a></li><br>
    </ul>
</aside>

<div class="admin-container">
    <h1>👋 Welcome, Admin <?php echo $_SESSION["Uname"]; ?>!</h1>

    <div class="admin-boxes">
        <div class="admin-box">
            <h3>🔧 Manage Users</h3>
            <p>View and control user accounts.</p>
        </div>
        <div class="admin-box">
            <h3>📈 View Reports</h3>
            <p>Analyze financial and usage data.</p>
        </div>
        <div class="admin-box">
            <h3>⚙️ System Settings</h3>
            <p>Configure system preferences and data.</p>
        </div>
    </div>
</div>

</body>
</html>
