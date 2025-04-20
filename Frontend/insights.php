<?php
session_start();
$user_id = $_SESSION["Uid"];

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB Connection
$conn = new mysqli("localhost", "root", "", "FiscalPoint");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$currentMonthNum = date('m');
$currentMonthName = date('F');

// Budget for this month
$sqlBudget = "SELECT Amount FROM Budget WHERE Uid = ? AND Month = ?";
$stmt = $conn->prepare($sqlBudget);
$stmt->bind_param("is", $user_id, $currentMonthName);
$stmt->execute();
$resultBudget = $stmt->get_result();
$budgetAmount = ($resultBudget->num_rows > 0) ? $resultBudget->fetch_assoc()['Amount'] : 0;

// Income for this month
$sqlIncome = "SELECT SUM(Income) AS Income FROM Income WHERE Uid = ? AND Month = ?";
$stmtIncome = $conn->prepare($sqlIncome);
$stmtIncome->bind_param("is", $user_id, $currentMonthName);
$stmtIncome->execute();
$resultIncome = $stmtIncome->get_result();
$totalIncome = ($resultIncome->num_rows > 0) ? $resultIncome->fetch_assoc()['Income'] : 0;

// Expenses by category this month
$sqlExpenses = "SELECT category, SUM(amount) AS totalExpense 
                FROM Expense WHERE Uid = ? AND MONTH(Date) = ? GROUP BY category";
$stmt = $conn->prepare($sqlExpenses);
$stmt->bind_param("ii", $user_id, $currentMonthNum);
$stmt->execute();
$resultExpenses = $stmt->get_result();

$totalExpense = 0;
$expensesByCategory = [];
$categoryLabels = [];
$categoryValues = [];

while ($row = $resultExpenses->fetch_assoc()) {
    $totalExpense += $row['totalExpense'];
    $expensesByCategory[$row['category']] = $row['totalExpense'];
    $categoryLabels[] = $row['category'];
    $categoryValues[] = $row['totalExpense'];
}

// Spending percentage
$percentageSpent = ($budgetAmount > 0) ? ($totalExpense / $budgetAmount) * 100 : 0;

// Spending status
if ($totalExpense < ($budgetAmount * 0.7)) {
    $status = "Excellent Budget Management";
    $statusMessage = "Great job! You have spent wisely and saved a good amount.";
    $statusColor = "green";
} elseif ($totalExpense <= $budgetAmount) {
    $status = "On-Track Spending";
    $statusMessage = "You are managing your budget well.";
    $statusColor = "blue";
} else {
    $status = "Over Budget";
    $statusMessage = "You have exceeded your budget.";
    $statusColor = "red";
}

// Top category
$topCategory = array_keys($expensesByCategory, max($expensesByCategory))[0] ?? null;
$topCategoryAmount = $expensesByCategory[$topCategory] ?? 0;

// Breakdown insights
$insights = "";
foreach ($expensesByCategory as $category => $amount) {
    $categoryPercentage = ($amount / $totalExpense) * 100;
    $insights .= "<li><strong>$category:</strong> " . number_format($categoryPercentage, 2) . "%</li>";
}

// Recommendations
$recommendation = "";
if ($topCategory) {
    $recommendation .= "<p><strong>Top Spending Category:</strong> $topCategory (₹" . number_format($topCategoryAmount, 2) . ")</p>";
    if ($topCategory == "Dining") {
        $recommendation .= "<p>🍽️ Try home-cooked meals.</p>";
    } elseif ($topCategory == "Shopping") {
        $recommendation .= "<p>🛍️ Limit impulse purchases.</p>";
    } elseif ($topCategory == "Transport") {
        $recommendation .= "<p>🚗 Try carpooling or public transport.</p>";
    }
}

// Additional Insights
$monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
$monthlyExpense = [];
$scatterData = [];

// Expense summary
$sqlMonthlyExpense = "SELECT MONTH(Date) as m, SUM(amount) as total FROM Expense WHERE Uid = ? GROUP BY MONTH(Date)";
$stmt = $conn->prepare($sqlMonthlyExpense);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resAvg = $stmt->get_result();

$monthCount = $resAvg->num_rows;
$totalForAvg = 0;

while ($row = $resAvg->fetch_assoc()) {
    $monthlyExpense[$row['m']] = $row['total'];
    $totalForAvg += $row['total'];
}
$avgExpense = ($monthCount > 0) ? $totalForAvg / $monthCount : 0;

// Saving = Income - Expense per month
$sqlIncomeMonths = "SELECT Month, SUM(Income) AS total FROM Income WHERE Uid = ? GROUP BY Month";
$stmt = $conn->prepare($sqlIncomeMonths);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resIncomes = $stmt->get_result();

$savings = [];

while ($row = $resIncomes->fetch_assoc()) {
    $monthStr = $row['Month'];
    $monthIndex = array_search($monthStr, $monthNames) + 1;
    $income = $row['total'];
    $expense = $monthlyExpense[$monthIndex] ?? 0;
    $savings[$monthStr] = $income - $expense;
    $scatterData[] = ["x" => $expense, "y" => $income];
}

arsort($savings);
$bestSavingMonth = !empty($savings) ? array_key_first($savings) : 'N/A';

// Highest income month
$highestIncomeMonth = 'N/A';
if ($resIncomes->num_rows > 0) {
    $resIncomes->data_seek(0);
    $row = $resIncomes->fetch_assoc();
    $highestIncomeMonth = $row['Month'];
}

$conn->close();
?>

<!-- HTML + CHARTS -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Insights</title>
    <link rel="stylesheet" href="css/insights.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <li><a href="addincome.php"><i class="fas fa-wallet"></i> <strong>Income</strong></a></li><br>
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
        <li><a href="predictions.php"><i class="fas fa-robot"></i> <strong>Predictions</strong></a></li><br>
        <li><a href="profile.php"><i class="fas fa-user"></i> <strong>Profile</strong></a></li><br>
        <li><a href="query.php"><i class="fas fa-user"></i> <strong>Query</strong></a></li><br>

        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <strong>Logout</strong></a></li><br>
    </ul>
</aside>

<div class="report-container">
    <h2>📈 Monthly Financial Insights</h2>

    <div class="status <?php echo $statusColor; ?>"><?php echo $status; ?></div>

    <p><strong>Total Budget:</strong> ₹<?php echo number_format($budgetAmount, 2); ?></p>
    <p><strong>Total Spent:</strong> ₹<?php echo number_format($totalExpense, 2); ?> (<?php echo number_format($percentageSpent, 2); ?>%)</p>
    <p><strong>Total Income (This Month):</strong> ₹<?php echo number_format($totalIncome, 2); ?></p>

    <h3>Spending Breakdown</h3>
    <ul><?php echo $insights; ?></ul>

    <div class="recommendation">
        <h4>Recommendations</h4>
        <p><?php echo $statusMessage; ?></p>
        <?php echo $recommendation; ?>
    </div>

    <div class="extra-insights">
        <h4>📊 Additional Insights</h4>
        <p><strong>Average Monthly Expense:</strong> ₹<?php echo number_format($avgExpense, 2); ?></p>
        <p><strong>Best Saving Month:</strong> <?php echo $bestSavingMonth; ?></p>
        <p><strong>Highest Income Month:</strong> <?php echo $highestIncomeMonth; ?></p>
    </div>

    <div class="chart-section">
        <h4>📊 Bar Chart - Expenses by Category</h4>
        <canvas id="barChart" height="120"></canvas>
    </div>

    <div class="chart-section">
        <h4>📊 Scatter Chart - Monthly Expense vs Income</h4>
        <canvas id="scatterChart" height="150"></canvas>
    </div>
</div>

<script>
    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($categoryLabels); ?>,
            datasets: [{
                label: 'Expenses (₹)',
                data: <?php echo json_encode($categoryValues); ?>,
                backgroundColor: '#007bff'
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    new Chart(document.getElementById('scatterChart'), {
        type: 'scatter',
        data: {
            datasets: [{
                label: 'Income vs Expense',
                data: <?php echo json_encode($scatterData); ?>,
                backgroundColor: '#dc3545'
            }]
        },
        options: {
            scales: {
                x: {
                    title: { display: true, text: 'Expenses (₹)' },
                    beginAtZero: true
                },
                y: {
                    title: { display: true, text: 'Income (₹)' },
                    beginAtZero: true
                }
            }
        }
    });
</script>

</body>
</html>
