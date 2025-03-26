<?php
session_start();

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "FiscalPoint"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$uid = $_SESSION["Uid"];
$current_month = date("Y-m"); // Get current month in format YYYY-MM

// Fetch Budget for the User and Month
$budget = 0; // Default budget if not found
$budget_query = "SELECT Amount FROM Budget WHERE Uid = ? AND Month = ?";
$stmt = $conn->prepare($budget_query);
$stmt->bind_param("is", $uid, $current_month);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $budget = $row["Amount"];
}
$stmt->close();

// Handle Expense Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = filter_var($_POST["cost"], FILTER_VALIDATE_FLOAT);
    $date = $_POST["date"];
    $description = $_POST["item"];
    $category = $_POST["category"];
    $payment_method = $_POST["payment_method"];

    if ($amount === false || $amount <= 0) {
        echo "<script>alert('Invalid amount. Enter a positive number.'); window.history.back();</script>";
        exit();
    }

    if ($amount > $budget) {
        echo "<script>alert('Error: Expense exceeds your monthly budget!'); window.history.back();</script>";
        exit();
    }

    $insert_query = "INSERT INTO Expense (Uid, Category, Amount, Date, Description, Payment_Method) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("isdsss", $uid, $category, $amount, $date, $description, $payment_method);
    
    if ($stmt->execute()) {
        echo "<script>alert('Expense added successfully!'); window.location.href='addexpense.php';</script>";
    } else {
        echo "<script>alert('Error adding expense. Please try again.'); window.history.back();</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expense</title>
    <link rel="stylesheet" href="css/addexpense.css">
</head>
<body>

<header>
    <img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'">
</header>

<aside class="sidebar">
    <ul class="menu">
        <li><a href="dashboard.php"><b>Dashboard</b></a></li>
        <li><a href="setbudget.php"><b>Budget</b></a></li>
        <li><a href="addexpense.php"><b>Add Expense</b></a></li>
        <li><a href="profile.php"><b>Profile</b></a></li>
        <li><a href="logout.php"><b>Logout</b></a></li>
    </ul>
</aside>

<div class="main-content">
    <div class="form-container">
        <h1>Add Expense:</h1>

        <div id="budget-box">
            <h2>Your Budget for This Month:</h2>
            <p id="budget-display">₹<?php echo $budget; ?></p>
        </div>

        <form id="expenseForm" action="addexpense.php" method="POST">
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required>

            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="Housing">Housing</option>
                <option value="Food">Food</option>
                <option value="Transportation">Transportation</option>
                <option value="Healthcare">Healthcare</option>
                <option value="Education">Education</option>
                <option value="Entertainment">Entertainment</option>
                <option value="Miscellaneous">Miscellaneous</option>
            </select>

            <label for="description">Description:</label>
            <input type="text" id="item" name="item" required>

            <label for="amount">Cost of Item:</label>
            <input type="number" id="cost" name="cost" step="0.01" required>

            <label for="payment_method">Payment Method:</label>
            <select id="payment_method" name="payment_method" required>
                <option value="Cash">Cash</option>
                <option value="Credit / Debit card">Credit/Debit card</option>
                <option value="UPI Payment">UPI Payment</option>
                <option value="Other">Other</option>
            </select>

            <button type="submit" class="add-expense-btn">Add Expense</button>
        </form>
    </div>
</div>

<script>
document.getElementById("expenseForm").addEventListener("submit", function(event) {
    event.preventDefault();
    
    let budgetAmount = parseFloat(document.getElementById("budget-display").innerText.replace("₹", ""));
    let expenseAmount = parseFloat(document.getElementById("cost").value);

    if (isNaN(expenseAmount) || expenseAmount <= 0) {
        alert("Please enter a valid expense amount.");
        return;
    }

    if (expenseAmount > budgetAmount) {
        alert("Warning: Expense exceeds budget!");
        return;
    }

    let confirmation = confirm(`Are you sure you want to deduct ₹${expenseAmount} from your budget of ₹${budgetAmount}?`);
    if (confirmation) {
        this.submit();
    }
});
</script>

</body>
</html>
