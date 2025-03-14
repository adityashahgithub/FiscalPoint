<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION["Uid"])) {
    die("Error: User not logged in. <a href='login.php'>Login here</a>");
}

// Retrieve logged-in user ID
$user_id = $_SESSION["Uid"];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch User Details
$sql_user = "SELECT Uname FROM User WHERE Uid = ?";
$stmt_user = $conn->prepare($sql_user);
if ($stmt_user) {
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user_name = ($result_user->num_rows > 0) ? $result_user->fetch_assoc()["Uname"] : "Unknown User";
    $stmt_user->close();
} else {
    die("Error preparing user query: " . $conn->error);
}

// Fetch categories for dropdown
$sql_categories = "SELECT DISTINCT category FROM Expense WHERE Uid = ?";
$stmt_categories = $conn->prepare($sql_categories);
$categories = [];
if ($stmt_categories) {
    $stmt_categories->bind_param("i", $user_id);
    $stmt_categories->execute();
    $result_categories = $stmt_categories->get_result();
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row["category"];
    }
    $stmt_categories->close();
} else {
    die("Error preparing category query: " . $conn->error);
}

// Default category selection
$selected_category = isset($_POST['category']) ? $_POST['category'] : '';

// Fetch Expenses based on selected category
$sql_expense = "SELECT category, description AS Item, amount AS Cost, date AS Date, Payment_Method FROM Expense WHERE Uid = ?";
if ($selected_category) {
    $sql_expense .= " AND category = ?";
}

$stmt_expense = $conn->prepare($sql_expense);
if ($stmt_expense) {
    if ($selected_category) {
        $stmt_expense->bind_param("is", $user_id, $selected_category);
    } else {
        $stmt_expense->bind_param("i", $user_id);
    }
    $stmt_expense->execute();
    $result_expense = $stmt_expense->get_result();
    $stmt_expense->close();
} else {
    die("Error preparing expense query: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabular Report</title>
    <link rel="stylesheet" href="css/categorywisereport.css">
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
            <li><p> <span style="font-size: 20px;">Name:</span> <?php echo htmlspecialchars($user_name); ?></p></li>
            <li><a href="dashboard.php">Dashboard</a></li><br>
            <li><a href="setbudget.php">Budget</a></li><br>
            <li><a href="addexpense.php">Add Expense</a></li><br>
            <li><a href="linegraph.php">Line Graph Report</a></li><br>
            <li><a href="piegraph.php">Pie Graph Report</a></li><br>
            <li><a href="tabularreport.php">Tabular Category-wise Report</a></li><br>
            <li><a href="profile.html">Profile</a></li><br>
            <li><a href="logout.php">Logout</a></li><br>
        </ul>
    </aside>
    
    <div>
        <h2>View Expenses by Category</h2>
        <form method="POST" action="">
            <label for="category">Select Category:</label>
            <select name="category" id="category">
                <option value="">All Categories</option>
                <?php
                foreach ($categories as $category) {
                    echo "<option value='" . htmlspecialchars($category) . "' " . ($selected_category == $category ? "selected" : "") . ">" . htmlspecialchars($category) . "</option>";
                }
                ?>
            </select>
            <button type="submit">Filter</button>
        </form>

        <h2> All Expenses</h2>
        <table>
            <thead>
                <tr>
                    <th>Sr No.</th>
                    <th>Category</th>
                    <th>Item</th>
                    <th>Cost</th>
                    <th>Date</th>
                    <th>Payment Method</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_expense->num_rows > 0) {
                    $sr_no = 1;
                    while ($row = $result_expense->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $sr_no . "</td>";
                        echo "<td>" . htmlspecialchars($row["category"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["Item"]) . "</td>";
                        echo "<td>₹" . htmlspecialchars($row["Cost"]) . "</td>";
                        echo "<td>" . date("d-m-Y", strtotime($row["Date"])) . "</td>";
                        echo "<td>" . htmlspecialchars($row["Payment_Method"]) . "</td>";
                        echo "</tr>";
                        $sr_no++;
                    }
                } else {
                    echo "<tr><td colspan='6'>No expenses found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
