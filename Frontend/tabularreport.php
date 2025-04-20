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

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle Expense Deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $delete_id = intval($_POST["delete_id"]);
    $sql_delete = "DELETE FROM Expense WHERE Uid = ? AND Eid = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    if ($stmt_delete) {
        $stmt_delete->bind_param("ii", $user_id, $delete_id);
        if ($stmt_delete->execute()) {
            echo "<script>alert('Expense deleted successfully.'); window.location.href='tabularreport.php';</script>";
        } else {
            echo "<script>alert('Failed to delete expense.');</script>";
        }
        $stmt_delete->close();
    } else {
        die("Error preparing delete query: " . $conn->error);
    }
}

// Get the selected month (default: current month)
$selected_month = isset($_POST['month']) ? $_POST['month'] : date('Y-m');

// Sorting Logic
$order_by_clauses = [];

if (!empty($_POST['date_order'])) {
    $order_by_clauses[] = "date " . $_POST['date_order'];
}

if (!empty($_POST['amount_order'])) {
    $order_by_clauses[] = "amount " . $_POST['amount_order'];
}

$order_by = "";
if (!empty($order_by_clauses)) {
    $order_by = " ORDER BY " . implode(", ", $order_by_clauses);
} else {
    $order_by = " ORDER BY date ASC"; // default
}

// Payment type filter
$payment_type_filter = ""; // default empty
$payment_param = "";

if (!empty($_POST['payment_type'])) {
    $payment_type_filter = " AND Payment_Method = ?";
    $payment_param = $_POST['payment_type'];
}

// Final SQL query
$sql_expense = "SELECT Eid, category, description AS Item, amount AS Cost, date AS Date, Payment_Method 
                FROM Expense 
                WHERE Uid = ? AND DATE_FORMAT(date, '%Y-%m') = ?" . $payment_type_filter . 
                $order_by;

// Uncomment for debugging final SQL
// echo "<!-- SQL: $sql_expense -->";

// Prepare the statement again with updated SQL
$stmt_expense = $conn->prepare($sql_expense);

if ($stmt_expense) {
    if (!empty($payment_param)) {
        $stmt_expense->bind_param("iss", $user_id, $selected_month, $payment_param);
    } else {
        $stmt_expense->bind_param("is", $user_id, $selected_month);
    }

    $stmt_expense->execute();
    $result_expense = $stmt_expense->get_result();
} else {
    die("Error preparing filtered expense query: " . $conn->error);
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabular Report</title>
    <link rel="stylesheet" href="css/tabularreport.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            <li><a href="predictions.php"><i class="fas fa-robot"></i> <strong>Predictions</strong></a></li><br>            <li><a href="profile.php"><i class="fas fa-user"></i> <strong>Profile</strong></a></li><br>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <strong>Logout</strong></a></li><br>
        </ul>
    </aside>

    <div>
        <h2>All Expenses</h2>
        <form method="POST" action="">
            <input type="hidden" name="submit" value="1">
            <label for="month">Select Month:</label>
            <input type="month" id="month" name="month" value="<?php echo $selected_month; ?>">

            <select name="date_order">
                <option value="">Date</option>
                <option value="ASC" <?php if (($_POST['date_order'] ?? '') === 'ASC') echo 'selected'; ?>>Oldest First</option>
                <option value="DESC" <?php if (($_POST['date_order'] ?? '') === 'DESC') echo 'selected'; ?>>Newest First</option>
            </select>

            <select name="amount_order">
                <option value="">Amount</option>
                <option value="ASC" <?php if (($_POST['amount_order'] ?? '') === 'ASC') echo 'selected'; ?>>Low to High</option>
                <option value="DESC" <?php if (($_POST['amount_order'] ?? '') === 'DESC') echo 'selected'; ?>>High to Low</option>
            </select>

            <select name="payment_type">
                <option value="">Payment Type</option>
                <option value="Cash" <?php if (($_POST['payment_type'] ?? '') === 'Cash') echo 'selected'; ?>>Cash</option>
                <option value="UPI Payment" <?php if (($_POST['payment_type'] ?? '') === 'UPI Payment') echo 'selected'; ?>>UPI</option>
                <option value="Credit / Debit Card" <?php if (($_POST['payment_type'] ?? '') === 'Credit / Debit Card') echo 'selected'; ?>>Credit/Debit Card</option>
                <option value="Other" <?php if (($_POST['payment_type'] ?? '') === 'Other') echo 'selected'; ?>>Other</option>
            </select>

            <button type="submit">Filter</button>
        </form>

         <!-- Download Excel Form (placed separately) -->
        <form method="POST" action="download_excel.php" target="_blank" style="margin-top: 10px;">
            <input type="hidden" name="month" value="<?php echo htmlspecialchars($selected_month); ?>">
            <input type="hidden" name="date_order" value="<?php echo isset($_POST['date_order']) ? $_POST['date_order'] : ''; ?>">
            <input type="hidden" name="amount_order" value="<?php echo isset($_POST['amount_order']) ? $_POST['amount_order'] : ''; ?>">
            <input type="hidden" name="payment_type" value="<?php echo isset($_POST['payment_type']) ? $_POST['payment_type'] : ''; ?>">
            <button type="submit" style="background-color:#4CAF50; color:white; border:none; padding:8px 16px; border-radius:5px; cursor:pointer;">
                Download Excel
                <button type="submit" onclick="location.href='calendarview.php'" style="background-color:#4CAF50; color:white; border:none; padding:8px 16px; border-radius:5px; cursor:pointer;">
                Calendar wise expense
            </button>
            </form>


        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Sr No.</th>
                        <th>Category</th>
                        <th>Item</th>
                        <th>Cost</th>
                        <th>Date</th>
                        <th>Payment Method</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_cost = 0;
                    if ($result_expense->num_rows > 0) {
                        $sr_no = 1;
                        while ($row = $result_expense->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $sr_no . "</td>";
                            echo "<td>" . htmlspecialchars($row["category"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["Item"]) . "</td>";
                            echo "<td>₹" . number_format($row["Cost"], 2) . "</td>";
                            echo "<td>" . date("d-m-Y", strtotime($row["Date"])) . "</td>";
                            echo "<td>" . htmlspecialchars($row["Payment_Method"]) . "</td>";
                            echo "<td>
                                    <form method='POST' onsubmit='return confirm(\"Are you sure you want to delete this expense?\")' style='display:inline;'>
                                        <input type='hidden' name='delete_id' value='" . $row["Eid"] . "'>
                                        <button type='submit' style='background-color:red; color:white; padding:5px 10px; border:none; border-radius:5px; cursor:pointer;'>Delete</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                            $total_cost += floatval($row["Cost"]);
                            $sr_no++;
                        }
                    } else {
                        echo "<tr><td colspan='7'>No expenses found.</td></tr>";
                    }
                    echo "<tr style='background-color:#86a69c; font-weight:bold;'>";
                    echo "<td><strong>Total:</strong></td>";
                    echo "<td colspan='2'></td>";
                    echo "<td><strong>₹" . number_format($total_cost, 2) . "</strong></td>";
                    echo "<td colspan='3'></td>";
                    echo "</tr>";
                    ?>
                </tbody>
            </table>
        </div>
    </div>
   
</body>
</html>
