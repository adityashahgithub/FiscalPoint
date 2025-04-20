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

// Get selected month
$selected_month = isset($_POST["month"]) ? $_POST["month"] : date("Y-m");

// Fetch Expenses (Grouping by Category for the selected month)
$sql_expense = "SELECT category, SUM(amount) AS total_cost 
                FROM Expense 
                WHERE Uid = ? AND DATE_FORMAT(date, '%Y-%m') = ?
                GROUP BY category";

$stmt_expense = $conn->prepare($sql_expense);
$stmt_expense->bind_param("is", $user_id, $selected_month);
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

// Check if there are expenses
$hasExpenses = count($categories) > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Pie Chart</title>
    <link rel="stylesheet" href="css/piegraph.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
    <li><a href="insights.php"><i class="fas fa-robot"></i> <strong>Insights</strong></a></li><br>
    <li><a href="predictions.php"><i class="fas fa-robot"></i> <strong>Predictions</strong></a></li><br>
    <li><a href="profile.php"><i class="fas fa-user"></i> <strong>Profile</strong></a></li><br>
    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <strong>Logout</strong></a></li><br>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="chart-container" style="width: 50%; margin: auto; height: 70%;">
        <h2>Category-Wise Expense Distribution</h2>

        <!-- Month Selection Form -->
        <form method="POST" action="" class="filter-form">
            <label for="month">Select Month:</label>
            <input type="month" id="month" name="month" value="<?php echo $selected_month; ?>">
            <button type="submit">Filter</button>
        </form>

        <?php if ($hasExpenses): ?>
            <canvas id="expensePieGraph"></canvas>
        <?php else: ?>
            <p style="text-align: center; font-size: 18px; color: red;">No expenses were made in the selected month.</p>
        <?php endif; ?>
    </div>
        
    <?php if ($hasExpenses): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script> <!-- Add this line -->

<script>
    // Pie Chart Data
    const ctx = document.getElementById('expensePieGraph').getContext('2d');
    Chart.register(ChartDataLabels); // Register the plugin

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
                datalabels: {
                    color: 'black', // Text color
                    anchor: 'end', // Positioning
                    align: 'start',
                    font: {
                        weight: 'bold',
                        size: 14
                    },
                    formatter: (value) => `₹${value}` // Format the values with currency
                }
            }
        }
    });
</script>

    <?php endif; ?>
</body>
</html>
