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

// Fetch User Details
$sql_user = "SELECT Uname FROM User WHERE Uid = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_name = ($result_user->num_rows > 0) ? $result_user->fetch_assoc()["Uname"] : "Unknown User";
$stmt_user->close();

// Fetch Expenses (Grouping by Category)
$sql_expense = "SELECT category, SUM(amount) AS total_cost FROM Expense WHERE Uid = ? GROUP BY category";
$stmt_expense = $conn->prepare($sql_expense);
$stmt_expense->bind_param("i", $user_id);
$stmt_expense->execute();
$result_expense = $stmt_expense->get_result();

$categories = [];
$costs = [];

// Fetch data for Pie Chart
while ($row = $result_expense->fetch_assoc()) {
    $categories[] = $row["category"];
    $costs[] = $row["total_cost"];
}
$stmt_expense->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Pie Chart</title>
    <link rel="stylesheet" href="css/tabularreport.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Include Chart.js -->
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
            <li><p> <span style="font-size: 20px;">Name:</span> <?php echo htmlspecialchars($user_name); ?></p></li>
           

            <li> <a href="dashboard.html">Dashboard</a></li><br>
            <li> <a href="setbudget.php">Budget</a></li><br>
            <li> <a href="addexpense.php">Add Expense </a></li><br>
            <li> <a href="linegraph.php">Line Graph Report </a></li><br>
            <li> <a href="linegraph.php">Pie Graph Report </a></li><br>
            <li> <a href="tabularreport.php">Tabular Category wise report </a></li><br>
            <li> <a href="profile.html">Profile</a></li><br>
            <li> <a href="logout.php">Logout</a></li><br>
        </ul>
    </aside>

    <div class="chart-container" style="width: 50%; margin: auto;">
        <h2>Expense Distribution</h2>
        <canvas id="expensePieChart"></canvas>
    </div>

    <script>
        // Pie Chart Data
        const ctx = document.getElementById('expensePieChart').getContext('2d');
        const expenseChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($categories); ?>,
                datasets: [{
                    label: 'Expenses',
                    data: <?php echo json_encode($costs); ?>,
                    backgroundColor: [
                        '#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff', 
                        '#ff9f40', '#ffcd56', '#c9cbcf', '#ff66a3', '#66ff66'
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Expense Breakdown by Category'
                    }
                }
            }
        });
    </script>
</body>
</html>
