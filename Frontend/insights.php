<?php
// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



// Fetch Budget for Current Month
$currentMonth = date('m');
$sqlBudget = "SELECT amount FROM Budget WHERE Uid = $uid AND MONTH(date) = $currentMonth";
$resultBudget = $conn->query($sqlBudget);
$budgetAmount = ($resultBudget->num_rows > 0) ? $resultBudget->fetch_assoc()['amount'] : 0;

// Fetch Expenses and Categorized Spending
$sqlExpenses = "SELECT category, SUM(amount) AS totalExpense 
                FROM Expense WHERE Uid = $uid AND MONTH(date) = $currentMonth GROUP BY category";
$resultExpenses = $conn->query($sqlExpenses);

$totalExpense = 0;
$expensesByCategory = [];

while ($rowExpense = $resultExpenses->fetch_assoc()) {
    $totalExpense += $rowExpense['totalExpense'];
    $expensesByCategory[$rowExpense['category']] = $rowExpense['totalExpense'];
}

// Calculate Percentage Spent
$percentageSpent = ($budgetAmount > 0) ? ($totalExpense / $budgetAmount) * 100 : 0;

// Determine Spending Status
if ($totalExpense < ($budgetAmount * 0.7)) {
    $status = "Excellent Budget Management";
    $statusMessage = "Great job! You have spent wisely and saved a good amount.";
    $statusColor = "green";
} elseif ($totalExpense <= $budgetAmount) {
    $status = "On-Track Spending";
    $statusMessage = "You are managing your budget well. Keep monitoring your expenses.";
    $statusColor = "blue";
} else {
    $status = "Over Budget";
    $statusMessage = "You have exceeded your budget. Consider cutting down on unnecessary expenses.";
    $statusColor = "red";
}

// Find Top Spending Category
$topCategory = array_keys($expensesByCategory, max($expensesByCategory))[0] ?? null;
$topCategoryAmount = $expensesByCategory[$topCategory] ?? 0;

// Spending Breakdown
$insights = "";
foreach ($expensesByCategory as $category => $amount) {
    $categoryPercentage = ($amount / $totalExpense) * 100;
    $insights .= "<li><strong>$category:</strong> " . number_format($categoryPercentage, 2) . "% of total spending</li>";
}

// Recommendations
$recommendation = "";
if ($topCategory) {
    $recommendation .= "<p><strong>Highest Spending Category:</strong> $topCategory (₹" . number_format($topCategoryAmount, 2) . ")</p>";
    if ($topCategory == "Dining") {
        $recommendation .= "<p>🍽️ Consider home-cooked meals to save money.</p>";
    } elseif ($topCategory == "Shopping") {
        $recommendation .= "<p>🛍️ Limit impulse purchases and prioritize essential shopping.</p>";
    } elseif ($topCategory == "Transport") {
        $recommendation .= "<p>🚗 Try public transport or carpooling to cut costs.</p>";
    }
}

// Close DB Connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="css/profile.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .report-container {
            background: #fff;
            padding: 20px;
            width: 500px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin-left: 250px;
            margin-top: 50px;
        }
        .report-container h2 {
            text-align: center;
            color: #333;
        }
        .status {
            font-size: 18px;
            font-weight: bold;
            padding: 10px;
            color: white;
            text-align: center;
            border-radius: 5px;
        }
        .status.green { background-color: #28a745; }
        .status.blue { background-color: #007bff; }
        .status.red { background-color: #dc3545; }
        .budget-details {
            margin: 15px 0;
        }
        .category-list {
            padding: 0;
            list-style: none;
        }
        .category-list li {
            padding: 5px 0;
        }
        .recommendation {
            background: #f8f9fa;
            padding: 10px;
            border-left: 4px solid #ffc107;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'">

<aside class="sidebar">
    <div class="profile">
        <img src="css/profile.png" alt="Profile Image" class="avatar">
    </div>
    <ul class="menu">
        <li><a href="dashboard.php"><span style="font-weight: bold;">Dashboard</span></a></li><br>
        <li><a href="setbudget.php"><span style="font-weight: bold;">Budget</span></a></li><br>
        <li><a href="addexpense.php"><span style="font-weight:bold;">Add Expense</span></a></li><br>
        <li class="dropdown">
            <a href="#"><span style="font-style: italic; font-weight: bold;">Graph Reports:</span></a>
            <ul>
                <li><a href="linegraph.php">Line Graph Report</a></li>
                <li><a href="piegraph.php">Pie Graph Report</a></li>
            </ul>
        </li>
        <br>
        <li>
            <a href="#"><span style="font-style: italic; font-weight: bold;">Tabular Reports:</span></a><br>
            <ul>
                <li><a href="tabularreport.php">All Expenses</a></li>
                <li><a href="categorywisereport.php">Category wise Expense</a></li>
            </ul>
        </li><br>
        <li><a href="profile.php"><span style="font-weight:bold;">Profile</span></a></li><br>
        <li><a href="logout.php"><span style="font-weight:bold;">Logout</span></a></li><br>
    </ul>
</aside>

<!-- Budget Report Section -->
<div class="report-container">
    <h2>📊 Monthly Budget Report</h2>
    
    <div class="status <?php echo $statusColor; ?>">
        <?php echo $status; ?>
    </div>
    
    <div class="budget-details">
        <p><strong>Total Budget:</strong> ₹<?php echo number_format($budgetAmount, 2); ?></p>
        <p><strong>Total Spent:</strong> ₹<?php echo number_format($totalExpense, 2); ?> (<?php echo number_format($percentageSpent, 2); ?>% of budget)</p>
    </div>

    <h3>Spending Analysis</h3>
    <ul class="category-list">
        <?php echo $insights; ?>
    </ul>

    <div class="recommendation">
        <h4>Recommendations</h4>
        <p><?php echo $statusMessage; ?></p>
        <?php echo $recommendation; ?>
    </div>
</div>

</body>
</html>
