<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Change if needed
$password = ""; // Change if needed
$database = "FiscalPoint"; // Your database name

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['Uid'])) {
    die("User not logged in.");
}

$uid = $_SESSION['Uid']; // Fetch logged-in user's ID
$message = ""; // Message for success or errors

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $month = $_POST['month'];
    $amount = $_POST['amount'];

    // Validate input
    if (empty($month) || empty($amount) || !is_numeric($amount)) {
        $message = "Invalid input. Please enter a valid amount.";
    } else {
        // Insert data into Budget table
        $sql = "INSERT INTO Budget (Uid, Month, Amount) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $uid, $month, $amount);

        if ($stmt->execute()) {
            $message = "Budget set successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Budget</title>
    <link rel="stylesheet" href="css/setbudget.css">
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
            <li><p> <span style="font-size: 20px;">Name</span></p></li>
            <li> <a href="dashboard.html">Dashboard</a></li><br>
            <li> <a href="Expense.html">Expense Report</a></li><br>
            <li> <a href="profile.html">Profile</a></li><br>
            <li> <a href="logout.html">Logout</a></li><br>
        </ul>
    </aside>
    
    <div class="main-content">
        <div class="form-container">
            <h1>Set Budget:</h1>
            <form id="budgetForm" onsubmit="saveBudget(event)" id="budgetForm" action="setbudget.php" method="POST"></form>>
                <label for="month">Select Month:</label>
                <input type="month" id="month" name="month" required onchange="checkExistingBudget()">
                
                <label for="budget">Enter Budget Amount:</label>
                <input type="number" id="budget" name="budget" step="0.01" required>
                
                <button type="submit" class="set-budget-btn">Set Budget</button>
                <button type="button" id="resetBtn" class="reset-budget-btn" style="display: none;" onclick="resetBudget()">Reset Budget</button>
            </form>
        </div>
    </div>
    
     <!-- Linking External JavaScript -->
     <script src="javascript/setbudget.js"></script>
</body>
</html>
