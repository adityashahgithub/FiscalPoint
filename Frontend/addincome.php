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

// Handle form submission for adding income
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["addincome"])) {
    $month = $_POST["month"];  // Stores in YYYY-MM format
    $income = $_POST["budget"]; // Decimal value

    // Convert month from YYYY-MM to "April" format
    $month_name = date("F", strtotime($month . "-01"));

    // Check if income for this month already exists for the user
    $check_sql = "SELECT * FROM Income WHERE Uid = ? AND Month = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("is", $user_id, $month_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Income for this month already exists! Reset it before adding a new entry.');</script>";
    } else {
        // Insert new income record
        $insert_sql = "INSERT INTO Income (Uid, Month, Income) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("isd", $user_id, $month_name, $income);

        if ($stmt->execute()) {
            echo "<script>alert('Income added successfully!'); window.location.href = 'addincome.php';</script>";
        } else {
            echo "<script>alert('Error adding income.');</script>";
        }
    }

    $stmt->close();
}

// Handle budget reset via AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["resetIncome"]) && $_POST["resetIncome"] === "true") {
    $month = $_POST["month"];
    $monthName = date("F", strtotime($month . "-01"));

    $delete_sql = "DELETE FROM Income WHERE Uid = ? AND Month = ?";
    $stmt_delete = $conn->prepare($delete_sql);
    $stmt_delete->bind_param("is", $user_id, $monthName);

    if ($stmt_delete->execute()) {
        echo "success";  // Fix: Return success response
    } else {
        echo "error";
    }

    $stmt_delete->close();
    exit(); // Fix: Prevent further HTML output
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Income</title>
    <link rel="stylesheet" href="css/addincome.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Fix: Ensure jQuery is included -->
</head>
<body>
    <!-- Header Section -->
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
            <li><a href="insights.php"><i class="fas fa-robot"></i> <strong>Insights</strong></a></li><br>
            <li><a href="predictions.php"><i class="fas fa-robot"></i> <strong>Predictions</strong></a></li><br>
            <li><a href="profile.php"><i class="fas fa-user"></i> <strong>Profile</strong></a></li><br>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <strong>Logout</strong></a></li><br>
        </ul>
    </aside>

    <div class="main-content">
        <div class="form-container">
            <h1>Add Income:</h1>
            <form id="IncomeForm" method="POST">
                <label for="month">Select Month:</label>
                <input type="month" id="month" name="month" required>
                <br>
                <label for="budget">Enter Income for the month:</label>
                <input type="number" id="budget" name="budget" step="0.01" required>
                
                <button type="submit" name="addincome" id="add-income-Btn" class="addincome-btn">Add Income</button>
                <button type="button" id="resetBtn" class="reset-income-btn" onclick="resetIncome()">Reset Income</button>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        let today = new Date();
        let year = today.getFullYear();
        let month = String(today.getMonth() + 1).padStart(2, '0'); 
        let minDate = `${year}-${month}`;
        document.getElementById("month").setAttribute("min", minDate);
    });

    function resetIncome() {
        var selectedMonth = document.getElementById("month").value;
        if (selectedMonth === "") {
            alert("Please select a month first.");
            return;
        }

        if (confirm("Are you sure you want to reset the Income for the selected month?")) {
            $.ajax({
                type: "POST",
                url: "addincome.php",
                data: { resetIncome: "true", month: selectedMonth },
                success: function(response) {
                    console.log("Server Response:", response.trim()); // Debugging
                    if (response.trim() === "success") {
                        alert("Income reset successfully!");
                        location.reload();
                    } else {
                        alert("Error resetting income.");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", xhr.responseText);
                    alert("Error: " + xhr.responseText);
                }
            });
        }
    }
    </script>
</body>
</html>
