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
    return htmlspecialchars(trim($data));
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = sanitize_input($_POST["category"]);
    $amount = filter_var(sanitize_input($_POST["cost"]), FILTER_VALIDATE_FLOAT); // Correct field name
    $date = sanitize_input($_POST["date"]);
    $description = sanitize_input($_POST["item"]); // Correct field name
    $uid = $_SESSION["Uid"]; // Get logged-in user's ID

    // Validate amount
    if ($amount === false || $amount <= 0) {
        echo "<script>alert('Invalid amount entered. Please enter a positive number.'); window.history.back();</script>";
        exit();
    }

    // Insert expense data into the database
    $insert_query = "INSERT INTO Expense (Uid, Category, Amount, Date, Description) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    
    if ($stmt === false) {
        die("Error in SQL query: " . $conn->error);
    }

    $stmt->bind_param("isdss", $uid, $category, $amount, $date, $description);
    
    if ($stmt->execute()) {
        echo "<script>alert('Expense added successfully!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Error adding expense. Please try again.'); window.history.back();</script>";
    }

    // Close statement
    $stmt->close();
}

// Close database connection
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
            <li><p> <span style="font-size: 20px;">Name</span></p></li>
            <li> <a href="dashboard.html">Dashboard</a></li><br>
            <li> <a href="Expense.html">Expense Report</a></li><br>
            <li> <a href="profile.html">Profile</a></li><br>
            <li> <a href="logout.html">Logout</a></li><br>
        </ul>
    </aside>
    
    <div class="main-content">
        <div class="form-container">
            <h1>Add Expense:</h1>
            <div id="budget-box" class="budget-box" style="display: none;">
                <h2>Your Budget for This Month:</h2>
                <p id="budget-display">$0.00</p>
            </div>
            <form action="addexpense.php" method="POST" onsubmit="return validateDate(event)">
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
                
                <label for="amount">Cost of Item:</label>
                <input type="number" id="cost" name="cost" step="0.01" required>
                
                <label for="payment_method">Payment Method:</label>
                <select id="payment_method" name="payment_method" onchange="toggleOtherPayment()" required class="payment-method">
                    <option value="Cash">Cash</option>
                    <option value="Credit / Debit card">Credit/Debit card</option>
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
</body>
</html>
