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

// Fetch Expenses for the selected month
$sql_expense = "SELECT DATE(date) AS expense_date, SUM(amount) AS total_cost 
                FROM Expense 
                WHERE Uid = ? AND DATE_FORMAT(date, '%Y-%m') = ? 
                GROUP BY expense_date 
                ORDER BY expense_date ASC";

// Check if query prepares correctly
if (!$stmt_expense = $conn->prepare($sql_expense)) {
    die("SQL Error: " . $conn->error);
}

$stmt_expense->bind_param("is", $user_id, $selected_month);
$stmt_expense->execute();
$result_expense = $stmt_expense->get_result();

$dates = [];
$costs = [];

// Fetch data for Line Graph
while ($row = $result_expense->fetch_assoc()) {
    $dates[] = $row["expense_date"];
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
    <title>Expense Line Graph</title>
    <link rel="stylesheet" href="css/linegraph.css">
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
            <li><a href="dashboard.php">Dashboard</a></li><br>
            <li><a href="setbudget.php">Budget</a></li><br>
            <li><a href="addexpense.php">Add Expense</a></li><br>
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
            <li><a href="profile.php">Profile</a></li><br>
            <li><a href="logout.php">Logout</a></li><br>
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
       // Line Graph Data
const ctx = document.getElementById('expenseLineChart').getContext('2d');
const expenseChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($dates); ?>,
        datasets: [{
            label: 'Total Expenses (₹)',
            data: <?php echo json_encode($costs); ?>,
            fill: false,
            borderColor: '#ffffff', // White line color
            backgroundColor: '#86a69c', // Background color for data points
            tension: 0.1
        }]
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
                text: 'Expenses Over Time',
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