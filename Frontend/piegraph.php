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
    <link rel="stylesheet" href="css/piegraph.css">
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
            <li><a href="dashboard.php"><span style="font-weight: bold;">Dashboard</span></a></li><br>
            <li><a href="setbudget.php"><span style="font-weight: bold";>Budget</span></a></li><br>
            <li><a href="addexpense.php"><span style="font-weight:bold";>Add Expense</span></a></li><br>
            <li>
            <li class="dropdown">
            <a href="#"><span style="font-style: italic; font-weight: bold;">Graph Reports:</span></a>
            <ul>
            <li><a href="linegraph.php">Line Graph Report</a></li>
            <li><a href="piegraph.php">Pie Graph Report</a></li>
        </ul>
            </li>
            <br>
    <li>
        <a href="#"> <span style="font-style: italic; font-weight: bold;">Tabular Reports:</span></a><br>
        <ul>
            <li><a href="tabularreport.php">All Expenses</a></li>
            <li><a href="categorywisereport.php">Category wise Expense</a></li>
        </ul>
    </li><br>
            <li><a href="profile.php"><span style="font-weight:bold;">Profile</span></a></li><br>
            <li><a href="logout.php"><span style="font-weight:bold";>Logout</span></a></li><br>
        </ul>
    </aside>

    <div class="chart-container" style="width: 50%; margin: auto; height: 70%;">
        <h2 >CategoryWise Expense Distribution</h2>

        <form method="POST" action="" class="filter-form">
            <label for="month">Select Month:</label>
            <input type="month" id="month" name="month" value="<?php echo $selected_month; ?>">
            <button type="submit">Filter</button>
        </form>
        <canvas id="expensePieGraph"></canvas>
    </div>
        
    <script>
        // Pie Chart Data
        const ctx = document.getElementById('expensePieGraph').getContext('2d');
        const expenseChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($categories); ?>,
                datasets: [{
                    label: 'Expenses',
                    data: <?php echo json_encode($costs); ?>,
                    backgroundColor: [
                        "#fd7f6f", "#7eb0d5", "#b2e061",
                         "#bd7ebe", "#ffb55a", "#ffee65", 
                         "#beb9db", "#fdcce5", "#8bd3c7",
                         "#ff677d", "#56c1ff", "#a0e65d", "#d39cd3"


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
                    
                }
            }
        });
    </script>
   <script src="javascript/piechart.js"></script>
</body>
</html>
