<?php
// Start session and enable error reporting
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch Uid from session
$uid = $_SESSION['Uid'];

// Fetch today's expense
$sql_today = "SELECT SUM(amount) AS total_today FROM Expense WHERE Uid = ? AND DATE(Date) = CURDATE()";
$stmt = $conn->prepare($sql_today);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result_today = $stmt->get_result();
$row_today = $result_today->fetch_assoc();
$today_expense = isset($row_today['total_today']) ? $row_today['total_today'] : 0;

// Fetch yesterday's expense
$sql_yesterday = "SELECT SUM(amount) AS total_yesterday FROM Expense WHERE Uid = ? AND DATE(Date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
$stmt = $conn->prepare($sql_yesterday);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result_yesterday = $stmt->get_result();
$row_yesterday = $result_yesterday->fetch_assoc();
$yesterday_expense = isset($row_yesterday['total_yesterday']) ? $row_yesterday['total_yesterday'] : 0;

// Fetch monthly expense
$sql_monthly = "SELECT SUM(amount) AS total_monthly FROM Expense WHERE Uid = ? AND MONTH(Date) = MONTH(CURDATE())";
$stmt = $conn->prepare($sql_monthly);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result_monthly = $stmt->get_result();
$row_monthly = $result_monthly->fetch_assoc();
$monthly_expense = isset($row_monthly['total_monthly']) ? $row_monthly['total_monthly'] : 0;

// Fetch yearly expense
$sql_yearly = "SELECT SUM(amount) AS total_yearly FROM Expense WHERE Uid = ? AND YEAR(Date) = YEAR(CURDATE())";
$stmt = $conn->prepare($sql_yearly);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result_yearly = $stmt->get_result();
$row_yearly = $result_yearly->fetch_assoc();
$yearly_expense = isset($row_yearly['total_yearly']) ? $row_yearly['total_yearly'] : 0;

// Fetch user's budget for the current month
$currentMonth = date("F");
$sql_budget = "SELECT Amount FROM Budget WHERE Uid = ? AND Month = ?";
$stmt = $conn->prepare($sql_budget);
$stmt->bind_param("is", $uid, $currentMonth);
$stmt->execute();
$result_budget = $stmt->get_result();
$row_budget = $result_budget->fetch_assoc();
$monthly_budget = isset($row_budget['Amount']) ? $row_budget['Amount'] : "No budget set";

// Fetch user's Income for the current month
$currentMonth = date("F");
$sql_budget = "SELECT Income FROM Income WHERE Uid = ? AND Month = ?";
$stmt = $conn->prepare($sql_budget);
$stmt->bind_param("is", $uid, $currentMonth);
$stmt->execute();
$result_income = $stmt->get_result();
$row_income = $result_income->fetch_assoc();
$monthly_income = isset($row_income['Income']) ? $row_income['Income'] : "No budget set";

// Determine text color for monthly expense
$expense_color = 'white'; // Default color
if ($monthly_budget !== "No budget set") {
    $expense_color = ($monthly_expense > $monthly_budget) ? 'red' : 'green';
}
// Query: Get expense totals per category
$sql = "SELECT Category, SUM(amount) AS total FROM Expense WHERE Uid = ? GROUP BY Category";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();
// Prepare data arrays
$categories = [];
$amounts = [];

while ($row = $result->fetch_assoc()) {
    $categories[] = $row['Category'];  // Expense categories
    $amounts[] = $row['total'];        // Total amount spent per category
}

// Convert PHP arrays to JSON for JavaScript
$categories_json = json_encode($categories);
$amounts_json = json_encode($amounts);


// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css"> <!-- External CSS file -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>
    <header> 
        <!-- LOGO -->
        <img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'">
    </header>

    <!-- SIDE BAR -->
    <aside class="sidebar">
        <div class="profile">
            <img src="css/profile.png" alt="Profile Image" class="avatar">
        </div>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

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
    
    <li><a href="profile.php"><i class="fas fa-user"></i> <strong>Profile</strong></a></li><br>
    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <strong>Logout</strong></a></li><br>
</ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="dashboard">
    <div class="grid-container">

        <!-- Budget Box -->
      
        <div class="box">
            <h3>Your Budget for <?php echo $currentMonth; ?>:</h3>
            <div class="content-box">
                <p><?php echo $monthly_budget; ?></p>
            </div>
        
        </div>
        <!-- Income Box -->
        <div class="box">
            <h3>Your Income for <?php echo $currentMonth; ?>:</h3>
            <div class="content-box">
                <p><?php echo $monthly_income; ?></p>
            </div>
        </div>

        <!-- Today's Expense -->
        <div class="box">
            <h3>Today's Expense:</h3>
            <div class="content-box">
                <p><?php echo $today_expense; ?></p>
            </div>
        </div>

        <!-- Yesterday's Expense -->
        <div class="box">
            <h3>Yesterday's Expense:</h3>
            <div class="content-box">
                <p><?php echo $yesterday_expense; ?></p>
            </div>
        </div>

        <!-- Monthly Expense -->
        <div class="box">
            <h3>Monthly Expense:</h3>
            <div class="content-box" style="color: <?php echo $expense_color; ?>;">
                <p><?php echo $monthly_expense; ?></p>
            </div>
        </div>

        <!-- This Year Expense -->
        <div class="box">
            <h3>This Year Expense:</h3>
            <div class="content-box">
                <p><?php echo $yearly_expense; ?></p>
            </div>
        </div>
    </div>
    <div class="chart-container">
    <canvas id="expenseChart"></canvas>
</div>
<script>
  document.addEventListener("DOMContentLoaded", function() {
      let ctx = document.getElementById('expenseChart').getContext('2d');
      
      // Get PHP data passed as JSON
      let categories = <?php echo $categories_json; ?>;
      let amounts = <?php echo $amounts_json; ?>;

      // Define colors for different categories
      let colors = ["#fd7f6f", "#7eb0d5", "#b2e061",
                    "#bd7ebe", "#ffb55a", "#ffee65", 
                    "#beb9db", "#fdcce5", "#8bd3c7",
                    "#ff677d", "#56c1ff", "#a0e65d", "#d39cd3"];

      // Create the chart
      new Chart(ctx, {
          type: 'doughnut',
          data: {
              labels: categories,
              datasets: [{
                  data: amounts,
                  backgroundColor: colors,
                  borderWidth: 2,
                  borderColor: '#222',
              }]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                  legend: {
                      display: true,
                      position: 'bottom',
                      labels: {
                          color: 'white',
                          font: { size: 14 }
                      }
                  }
              },
              cutout: '70%' // Makes it a donut chart
          }
      });
  });
</script>

</main>


 
</body>
</html>
