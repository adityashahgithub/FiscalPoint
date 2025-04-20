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

// Get the selected month (default: current month)
$selected_month = isset($_POST['month']) ? $_POST['month'] : date('Y-m');

// Fetch Monthly Budget
$sql_budget = "SELECT Amount FROM Budget WHERE Uid = ? AND Month = ?";
$stmt_budget = $conn->prepare($sql_budget);
$stmt_budget->bind_param("is", $user_id, $selected_month);
$stmt_budget->execute();
$result_budget = $stmt_budget->get_result();
$row_budget = $result_budget->fetch_assoc();
$monthly_budget = isset($row_budget['Amount']) ? $row_budget['Amount'] : 0;
$stmt_budget->close();

// Fetch Expenses for the selected month
$sql_expense = "SELECT DATE(date) AS expense_date, SUM(amount) AS total_cost 
                FROM Expense 
                WHERE Uid = ? AND DATE_FORMAT(date, '%Y-%m') = ? 
                GROUP BY expense_date 
                ORDER BY expense_date ASC";

$stmt_expense = $conn->prepare($sql_expense);
$stmt_expense->bind_param("is", $user_id, $selected_month);
$stmt_expense->execute();
$result_expense = $stmt_expense->get_result();

$dates = [];
$costs = [];
$remaining_budget = [];
$total_spent = 0;
$current_budget = $monthly_budget; // Initialize with total budget
$budget_line = []; // New array for flat monthly budget

// Fetch data for Line Graph
while ($row = $result_expense->fetch_assoc()) {
    $dates[] = $row["expense_date"];
    $costs[] = $row["total_cost"];
    $budget_line[] = $monthly_budget; // Fill the array with the same budget amount
    
    // Calculate remaining budget dynamically
    $total_spent += $row["total_cost"];
    $current_budget = max($monthly_budget - $total_spent, 0); // Update dynamically
    $remaining_budget[] = $current_budget;
}
$stmt_expense->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Line Graph</title>
    <link rel="stylesheet" href="css/linegraph.css">
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
    <li><a href="query.php"><i class="fas fa-user"></i> <strong>Query</strong></a></li><br>

    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <strong>Logout</strong></a></li><br>
        </ul>
    </aside>
   
    <h2>Expense Trend Over Time</h2>
        
        <!-- Month Selection Form -->
        <form method="POST" action=""class="filter-form">
            <label for="month">Select Month:</label>
            <input type="month" id="month" name="month" value="<?php echo $selected_month; ?>">
            <button type="submit">Filter</button>
        </form>

        
    <div class="chart-container" style="width: 60%; margin: 2px;">
       

        <canvas id="expenseLineChart"></canvas>
    </div>

    <script>
const ctx = document.getElementById('expenseLineChart').getContext('2d');
const expenseChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($dates); ?>,
        datasets: [
            {
                label: 'Total Expenses (₹)',
                data: <?php echo json_encode($costs); ?>,
                fill: false,
                borderColor: '#ffffff', // White line color for expenses
                backgroundColor: '#86a69c', // Background color for data points
                tension: 0.1
            },
            {
                label: 'Budget Line (₹)',
                data: <?php echo json_encode($budget_line); ?>,
                fill: false,
                borderColor: '#ff0000', // Red line for budget
                borderDash: [5, 5], // Dashed line
                tension: 0.1
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    color: '#ffffff' // White legend text
                }
            },
            title: {
                display: true,
                text: 'Expenses vs Remaining Budget Over Time',
                color: '#ffffff' // White title text
            }
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Date',
                    color: '#ffffff' // White x-axis label
                },
                ticks: {
                    color: '#ffffff' // White x-axis values
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Amount (₹)',
                    color: '#ffffff' // White y-axis label
                },
                ticks: {
                    color: '#ffffff' // White y-axis values
                }
            }
        }
    }
});
// Set background color of the canvas
document.getElementById('expenseLineChart').style.backgroundColor = '#86a69c';

    </script>
    
</body>
</html>