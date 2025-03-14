<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Check if user is logged in
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

// Handle form submission (Set Budget)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["setBudget"])) {
    $month = $_POST["month"];
    $monthName = date("F", strtotime($month . "-01"));
    $amount = $_POST["budget"];

    // Prevent setting budget for past months
    $currentYearMonth = date("Y-m");
    if ($month < $currentYearMonth) {
        die("<script>alert('Cannot set budget for past months!'); window.location.href='setbudget.php';</script>");
    }

    if (empty($month) || empty($amount) || $amount <= 0) {
        die("<script>alert('Invalid input. Please enter a valid month and budget amount.');</script>");
    }

    // Check if budget already exists
    $check_sql = "SELECT * FROM Budget WHERE Uid = ? AND Month = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("is", $user_id, $monthName);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Budget for $monthName already exists! Reset it first if you want to change it.'); window.location.href='setbudget.php';</script>";
    } else {
        $insert_sql = "INSERT INTO Budget (Uid, Month, Amount) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_sql);
        $stmt_insert->bind_param("isd", $user_id, $monthName, $amount);
        $stmt_insert->execute();
        echo "<script>alert('Budget set successfully!'); window.location.href='setbudget.php';</script>";
        $stmt_insert->close();
    }

    $stmt_check->close();
}

// Handle budget reset
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["resetBudget"])) {
    $month = $_POST["month"];
    $monthName = date("F", strtotime($month . "-01"));

    $delete_sql = "DELETE FROM Budget WHERE Uid = ? AND Month = ?";
    $stmt_delete = $conn->prepare($delete_sql);
    $stmt_delete->bind_param("is", $user_id, $monthName);
    
    if ($stmt_delete->execute()) {
        echo "<script>alert('Budget reset successfully!'); window.location.href='setbudget.php';</script>";
    } else {
        echo "<script>alert('Error resetting budget.');</script>";
    }

    $stmt_delete->close();
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            <li> <a href="dashboard.php">Dashboard</a></li><br>
            <li> <a href="setbudget.php">Budget</a></li><br>
            <li> <a href="addexpense.php">Add Expense</a></li><br>
            <li class="dropdown">
                <li>
                    <a href="#">Graph Reports ▼</a>
                    <ul class="submenu">
                        <li><a href="linegraph.php">Line Graph Report</a></li>
                        <li><a href="piegraph.php">Pie Graph Report</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#">Tabular Reports ▼</a>
                    <ul class="submenu">
                        <li><a href="tabularreport.php">All Expenses</a></li>
                        <li><a href="categorywisereport.php">Category wise Report</a></li>
                    </ul>
                </li>
            <li> <a href="profile.html">Profile</a></li><br>
            <li> <a href="logout.php">Logout</a></li><br>
        </ul>
    </aside>
    
    <div class="main-content">
        <div class="form-container">
            <h1>Set Budget:</h1>
            <form id="budgetForm" method="POST">
                <label for="month">Select Month:</label>
                <input type="month" id="month" name="month" required>
                <br>
                <label for="budget">Enter Budget Amount:</label>
                <input type="number" id="budget" name="budget" step="0.01" required>
                
                <button type="submit" name="setBudget" id="setBudgetBtn" class="set-budget-btn">Set Budget</button>
                <button type="button" id="resetBtn" class="reset-budget-btn" onclick="resetBudget()">Reset Budget</button>
            </form>
        </div>
    </div>

    <script>
        // Disable past months
        document.addEventListener("DOMContentLoaded", function () {
            let today = new Date();
            let year = today.getFullYear();
            let month = String(today.getMonth() + 1).padStart(2, '0'); 
            let minDate = `${year}-${month}`;
            document.getElementById("month").setAttribute("min", minDate);
        });

        function checkExistingBudget() {
            var selectedMonth = document.getElementById("month").value;
            if (selectedMonth === "") {
                return;
            }

            $.ajax({
                type: "POST",
                url: "check_budget.php",
                data: { month: selectedMonth },
                success: function(response) {
                    if (response.trim() === "exists") {
                        alert("Budget for this month already exists! You must reset it first.");
                        document.getElementById("setBudgetBtn").disabled = true;
                    } else {
                        document.getElementById("setBudgetBtn").disabled = false;
                    }
                }
            });
        }

        function resetBudget() {
            var selectedMonth = document.getElementById("month").value;
            if (selectedMonth === "") {
                alert("Please select a month first.");
                return;
            }

            if (confirm("Are you sure you want to reset the budget for the selected month?")) {
                $.ajax({
                    type: "POST",
                    url: "setbudget.php",
                    data: { resetBudget: true, month: selectedMonth },
                    success: function(response) {
                        alert("Budget reset successfully!");
                        location.reload();
                    },
                    error: function() {
                        alert("Error resetting budget.");
                    }
                });
            }
        }
    </script>
</body>
</html>
