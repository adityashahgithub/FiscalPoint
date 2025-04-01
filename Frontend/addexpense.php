<?php
// Start session to track logged-in user
session_start();

// Database connection parameters
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

// Function to sanitize input data
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Check if user is logged in
if (!isset($_SESSION["Uid"])) {
    echo "<script>alert('Session expired. Please log in again.'); window.location.href='login.php';</script>";
    exit();
}
$uid = $_SESSION["Uid"];

// Fetch current month's budget
$currentMonth = date("F");
$sql_budget = "SELECT Amount FROM Budget WHERE Uid = ? AND Month = ?";
$stmt = $conn->prepare($sql_budget);
$stmt->bind_param("is", $uid, $currentMonth);
$stmt->execute();
$result_budget = $stmt->get_result();
$row_budget = $result_budget->fetch_assoc();
$monthly_budget = isset($row_budget['Amount']) ? $row_budget['Amount'] : "No budget set";
$stmt->close();

// Calculate total expenses for the current month
$sql_expenses = "SELECT SUM(Amount) AS total_expenses FROM Expense WHERE Uid = ? AND MONTH(Date) = MONTH(CURRENT_DATE())";
$stmt = $conn->prepare($sql_expenses);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result_expenses = $stmt->get_result();
$row_expenses = $result_expenses->fetch_assoc();
$total_expenses = isset($row_expenses['total_expenses']) ? $row_expenses['total_expenses'] : 0;
$stmt->close();


// Calculate Remaining Budget
$remaining_budget = ($monthly_budget !== "No budget set") ? $monthly_budget - $total_expenses : "No budget set";

// Handle Expense Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = sanitize_input($_POST["category"]);
    $amount = filter_var(sanitize_input($_POST["cost"]), FILTER_VALIDATE_FLOAT);
    $date = sanitize_input($_POST["date"]);
    $description = sanitize_input($_POST["item"]);
    $payment_method = sanitize_input($_POST["payment_method"]); 

    // Validate amount
    if ($amount === false || $amount <= 0) {
        echo "<script>alert('Invalid amount. Enter a positive number.'); window.history.back();</script>";
        exit();
    }

    // Validate Date Format (YYYY-MM-DD)
    if (!DateTime::createFromFormat('Y-m-d', $date)) {
        echo "<script>alert('Invalid date format. Please enter a valid date.'); window.history.back();</script>";
        exit();
    }

    // Check if expense exceeds budget (only if budget is set)
    if ($monthly_budget !== "No budget set" && $amount > $monthly_budget) {
        echo "<script>alert('Error: Expense exceeds your monthly budget!'); window.history.back();</script>";
        exit();
    }

    // Insert expense data into the database
    $insert_query = "INSERT INTO Expense (Uid, Category, Amount, Date, Description, Payment_Method) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    
    if ($stmt === false) {
        die("Error in SQL query: " . $conn->error);
    }

    $stmt->bind_param("isdsss", $uid, $category, $amount, $date, $description, $payment_method);
    
    if ($stmt->execute()) {
        echo "<script>alert('Expense added successfully!'); window.location.href='addexpense.php';</script>";
    } else {
        error_log("SQL Error: " . $stmt->error, 3, "logs/errors.log");
        echo "<script>alert('Error adding expense. Please try again.'); window.history.back();</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expense</title>
    <link rel="stylesheet" href="css/addexpense.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <!-- Logo with click functionality to redirect to landing page -->
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
    
    <li>
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
    <div class="main-content">
   

        <div class="form-container">
            <h1>Add Expense:</h1>
            <div>
                <h3 class="budget-text">Your Budget for <?php echo $currentMonth; ?>:</h3>
                <div class="Budget">
                    <p><?php echo $monthly_budget; ?></p>
                </div>
            </div>
            <br>
            <form action="addexpense.php" method="POST" onsubmit="return validateDate(event)" id="expenseForm">
                <label for="date">Date:</label>
                <input type="date" id="date" name="date" required>
                
                <label for="category">Category:</label>
                <select id="category" name="category" class="category" required>
                    <option value="Housing">Housing</option>
                    <option value="Food">Food</option>
                    <option value="Transportation">Transportation</option>
                    <option value="Healthcare">Healthcare</option>
                    <option value="Education">Education</option>
                    <option value="Entertainment">Entertainment</option>
                    <option value="Personal Care">Personal Care</option>
                    <option value="Debt Repayment">Debt Repayment</option>
                    <option value="Savings & Investments">Savings & Investments</option>
                    <option value="Insurance">Insurance</option>
                    <option value="Childcare/Dependents">Childcare/Dependents</option>
                    <option value="Gifts & Donations">Gifts & Donations</option>
                    <option value="Miscellaneous">Miscellaneous</option>
                </select>
                
                <label for="description">Description:</label>
                <input type="text" id="item" name="item" required>
                
                <label for="amount">Amount Spent:</label>
                <input type="number" id="cost" name="cost" step="0.01" required>

                <!-- Display Total Expenses (Hidden) -->
                <input type="hidden" id="total_expenses" value="<?php echo $total_expenses; ?>">

                <label for="remain-budget">Remaining Budget for <?php echo $currentMonth; ?>:</label>
                <input type="text" id="remaining_budget" name="remaining_budget" value="<?php echo $remaining_budget; ?>" readonly>
                
                <label for="payment_method">Payment Method:</label>
                <select id="payment_method" name="payment_method" onchange="toggleOtherPayment()" required class="payment-method">
                    <option value="Cash">Cash</option>
                    <option value="Credit / Debit card">Credit/Debit card</option>
                    <option value="UPI Payment">UPI Payment</option>

                    <option value="Other">Other</option>
                </select>
                
                <div id="otherPaymentDiv" style="display: none;">
                    <label for="other_payment">Specify Payment Method:</label>
                    <input type="text" id="other_payment" name="other_payment">
                </div>
                
                <button type="submit" class="add-expense-btn">Add Expense</button>
            </form>
        </div>
    </div>

    <div id="budgetBox" style="display: none; background-color: #6B6487; padding: 10px; border-radius: 8px; text-align: center; color: white; margin-right: 10rem;" class="budget-box">
        <p>Your budget for this month is: <span id="budgetAmount">0</span></p>
    </div>
    
     <!-- Linking External JavaScript -->
     <script src="javascript/addexpense.js"></script>
     <script>

    </script>

</body>
</html>
