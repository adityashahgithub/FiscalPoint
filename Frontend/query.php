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

// Function to sanitize input data and prevent double encoding
function sanitize_input($data) {
    return html_entity_decode(htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8'));
}

// Check if user is logged in
if (!isset($_SESSION["Uid"])) {
    echo "<script>alert('Session expired. Please log in again.'); window.location.href='login.php';</script>";
    exit();
}
$uid = $_SESSION["Uid"];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data
    $email = sanitize_input($_POST["email"]);
    $query_type = sanitize_input($_POST["category"]);
    $description = sanitize_input($_POST["description"]);

    // Prepared statement to insert into Query table
    $stmt = $conn->prepare("INSERT INTO Query (Uid, Email, Query_type, Description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $uid, $email, $query_type, $description);

    if ($stmt->execute()) {
        echo "<script>alert('Query submitted successfully!'); window.location.href='query.php';</script>";
    } else {
        echo "<script>alert('Error submitting query: " . $stmt->error . "');</script>";
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
    <li class="dropdown">
                <a href="#"><i class="fas fa-table"></i> <strong><em>Tabular Reports:</em></strong></a><br>
                <ul>
                    <li><a href="tabularreport.php"><i class="fas fa-list-alt"></i> All Expenses</a></li>
                    <li><a href="categorywisereport.php"><i class="fas fa-layer-group"></i> Category-wise Expense</a></li>
                </ul>
    </li><br>
    <li><a href="insights.php"><i class="fas fa-lightbulb"></i> <strong>Insights</strong></a></li><br>
    <li><a href="predictions.php"><i class="fas fa-chart-line"></i> <strong>Predictions</strong></a></li><br>
    <li><a href="profile.php"><i class="fas fa-user"></i> <strong>Profile</strong></a></li><br>
    <li><a href="query.php"><i class="fas fa-question-circle"></i> <strong>Query</strong></a></li><br>

    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <strong>Logout</strong></a></li><br>
        </ul>
    </aside>
     <!-- MAIN CONTENT -->
    <div class="main-content">
   

        <div class="form-container">
            <h1>Query:</h1>
            <div>
                <h3 class="budget-text">Enter your query :</h3>
               
            </div>
            <br>
            <form action="query.php" method="POST" onsubmit="return validateDate(event)" id="expenseForm">

<label for="email">Enter your registered email:</label>
<input type="email" id="email" name="email" required>

<label for="category">Query Type :</label>
<select id="category" name="category" class="category" required>
    <option value="Dashboard">Dashboard</option>
    <option value="Add Income">Add Income</option>
    <option value="Add Budget">Add Budget</option>
    <option value="Add Expense">Add Expense</option>
    <option value="Graph Report">Graph Report</option>
    <option value="Tabular report">Tabular report</option>
    <option value="Insights">Insights</option>
    <option value="Predictions">Predictions</option>
    <option value="Profile">Profile</option>
</select>

<label for="description">Description:</label>
<input type="text" id="description" name="description" required>

<p class="signup-text">Please keep cheking your mailbox for reponse</p>

<button type="submit" class="add-expense-btn">Submit Query</button>
</form>

    </div>

   
    
     <!-- Linking External JavaScript -->
     <script src="javascript/addexpense.js"></script>
   

</body>
</html>
