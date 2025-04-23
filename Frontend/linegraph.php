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

// Convert the selected month to the format stored in the database (e.g., "January", "February", etc.)
$month_name = date("F", strtotime($selected_month . "-01"));

// Fetch Monthly Budget
$sql_budget = "SELECT Amount FROM Budget WHERE Uid = ? AND Month = ?";
$stmt_budget = $conn->prepare($sql_budget);
$stmt_budget->bind_param("is", $user_id, $month_name);
$stmt_budget->execute();
$result_budget = $stmt_budget->get_result();
$row_budget = $result_budget->fetch_assoc();
$monthly_budget = isset($row_budget['Amount']) ? $row_budget['Amount'] : 0;
$stmt_budget->close();

// Calculate total expenses for the selected month
$sql_total_expenses = "SELECT SUM(Amount) AS total_expenses FROM Expense WHERE Uid = ? AND DATE_FORMAT(Date, '%Y-%m') = ?";
$stmt_total = $conn->prepare($sql_total_expenses);
$stmt_total->bind_param("is", $user_id, $selected_month);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$row_total = $result_total->fetch_assoc();
$total_spent = isset($row_total['total_expenses']) ? $row_total['total_expenses'] : 0;
$stmt_total->close();

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
$daily_expenses = [];

// Fetch data for Line Graph
while ($row = $result_expense->fetch_assoc()) {
    $dates[] = $row["expense_date"];
    $daily_expenses[] = $row["total_cost"];
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
    <li><a href="insights.php"><i class="fas fa-lightbulb"></i> <strong>Insights</strong></a></li><br>
    <li><a href="predictions.php"><i class="fas fa-chart-line"></i> <strong>Predictions</strong></a></li><br>
    <li><a href="profile.php"><i class="fas fa-user"></i> <strong>Profile</strong></a></li><br>
    <li><a href="query.php"><i class="fas fa-question-circle"></i> <strong>Query</strong></a></li><br>

    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <strong>Logout</strong></a></li><br>
        </ul>
    </aside>
   
    <!-- Create a content wrapper div for all main content -->
    <div class="content-wrapper">
        <h2>Expense Trend Over Time</h2>
            
        <!-- Month Selection Form -->
        <form method="POST" action="" class="filter-form">
            <label for="month">Select Month:</label>
            <input type="month" id="month" name="month" value="<?php echo $selected_month; ?>">
            <button type="submit">Filter</button>
        </form>

        <div class="budget-info">
            <p>Month: <?php echo $month_name; ?></p>
            <p>Monthly Budget: ₹<?php echo number_format($monthly_budget, 2); ?></p>
            <p>Total Expenses: ₹<?php echo number_format($total_spent, 2); ?></p>
            <p>Remaining: ₹<?php echo number_format($monthly_budget - $total_spent, 2); ?></p>
        </div>
            
        <div class="chart-container">
            <canvas id="expenseLineChart"></canvas>
        </div>
    </div>

    <script>
const ctx = document.getElementById('expenseLineChart').getContext('2d');
const expenseChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($dates); ?>,
        datasets: [
            {
                label: 'Daily Expenses (₹)',
                data: <?php echo json_encode($daily_expenses); ?>,
                fill: false,
                borderColor: '#ffffff',
                backgroundColor: '#86a69c',
                tension: 0.1,
                borderWidth: 3,
                pointRadius: 5,
                pointHoverRadius: 8
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    color: '#ffffff',
                    font: {
                        size: 14,
                        weight: 'bold'
                    }
                }
            },
            title: {
                display: true,
                text: 'Daily Expenses for <?php echo $month_name; ?>',
                color: '#ffffff',
                font: {
                    size: 18,
                    weight: 'bold'
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        // Expense dataset
                        return 'Daily Expense: ₹' + parseFloat(context.raw).toFixed(2);
                    }
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'nearest'
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Date',
                    color: '#ffffff'
                },
                ticks: {
                    color: '#ffffff',
                    maxRotation: 45,
                    minRotation: 45
                },
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Amount (₹)',
                    color: '#ffffff'
                },
                ticks: {
                    color: '#ffffff',
                    callback: function(value) {
                        return '₹' + value;
                    }
                },
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                },
                beginAtZero: true
            }
        }
    }
});

// Set background color of the canvas
document.getElementById('expenseLineChart').style.backgroundColor = '#86a69c';
    </script>
    
<style>
/* Add wrapper styling for main content */
.content-wrapper {
    margin-left: 18%; /* Provide space for the sidebar */
    padding: 20px;
    width: 80%;
}

h2 {
    text-align: center;
    margin-bottom: 20px;
    top: 78px;
}

.filter-form {
    text-align: center;
    margin: 20px auto;
    padding: 10px;
    background-color: #484258;
    border-radius: 10px;
    width: 85%;
    max-width: 500px;
}

.filter-form label {
    color: white;
    margin-right: 10px;
    font-weight: bold;
}

.filter-form input {
    padding: 5px;
    margin-right: 10px;
    border-radius: 5px;
    border: none;
}

.filter-form button {
    padding: 5px 15px;
    background-color: #E5C4C4;
    color: black;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.filter-form button:hover {
    background-color:rgb(207, 165, 165);
}

.budget-info {
    background-color: #86a69c;
    border-radius: 10px;
    padding: 15px;
    margin: 20px auto;
    width: 85%;
    max-width: 800px;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    color: white;
    font-weight: bold;
}

.budget-info p {
    margin: 5px 0;
    font-size: 16px;
    flex-basis: 48%;
    text-align: center;
}

.chart-container {
    height: 500px;
    width: 85%;
    max-width: 900px;
    margin: 20px auto;
    background-color: #86a69c;
    border-radius: 10px;
    padding: 10px;
}

@media (max-width: 768px) {
    .content-wrapper {
        margin-left: 0;
        width: 100%;
        padding: 10px;
    }
    
    .budget-info {
        flex-direction: column;
        width: 90%;
    }
    
    .budget-info p {
        flex-basis: 100%;
        margin: 5px 0;
    }
    
    .chart-container {
        width: 95%;
    }
}
</style>
</body>
</html>