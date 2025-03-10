<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    
    <aside class="sidebar">
        <div class="profile">
            <img src="css/profile.png" alt="Profile Image" class="avatar">
        </div>
        <ul class="menu">
            <li><p> <span style="font-size: 20px;">Name</span></p></li>
            <li> <a href="dashboard.html">Dashboard</a></li><br>
            <li> <a href="addexpense.php">Add Expense </a></li><br>
            <li> <a href="graphdashboard.html">Graph Report </a></li><br>
            <li> <a href="profile.html">Profile</a></li><br>
            <li> <a href="logout.php">Logout</a></li><br>
        </ul>
    </aside>
        <main class="dashboard">
            <div>
                <h3 class="budget-text">Your Budget :</h3>
                <div class="Budget">
            </div>
        </div><br>
            <div class="expense-box">
                <h3>Today's Expense:</h3>
                <div class="expense-card"></div>
            </div>
            <div class="expense-box">
                <h3>Yesterday's Expense:</h3>
                <div class="expense-card"></div>
            </div>
            <div class="expense-box">
                <h3>Monthly Expense:</h3>
                <div class="expense-card"></div>
            </div>
            <div class="expense-box">
                <h3>This Year Expense:</h3>
                <div class="expense-card"></div>
            </div>
        </main>
    </div>
</body>
</html>
