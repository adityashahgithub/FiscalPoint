<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION["Uid"]) || $_SESSION["Role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user ID from URL
$uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;

if ($uid <= 0) {
    header("Location: admin_registered_users.php");
    exit();
}

// Fetch user details
$user_stmt = $conn->prepare("SELECT Uname FROM User WHERE Uid = ?");
$user_stmt->bind_param("i", $uid);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    header("Location: admin_registered_users.php");
    exit();
}

// Fetch expense data
$expense_query = "SELECT * FROM Expense WHERE Uid = ? ORDER BY date DESC";
$expense_stmt = $conn->prepare($expense_query);
$expense_stmt->bind_param("i", $uid);
$expense_stmt->execute();
$expense_result = $expense_stmt->get_result();

if (!$expense_result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expense Details</title>
    <link rel="stylesheet" href="css/admin_registered_users.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<header>
    <img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'">
</header>

<aside class="sidebar">
    <div class="profile">
        <img src="css/profile.png" alt="Admin Profile" class="avatar">
    </div>
    <ul class="menu">
        <li><a href="admin_category.php"><i class="fas fa-layer-group"></i> Category</a></li><br>
        <li><a href="admin_registered_users.php"><i class="fas fa-users-cog"></i> Reg Users</a></li><br>
        <li><a href="admin_query.php"><i class="fas fa-user"></i> <strong>Query</strong></a></li><br>
        <li><a href="add_admin.php"><i class="fas fa-user"></i> <strong>Add Admin</strong></a></li><br>
        <li><a href="manage_admin.php"><i class="fas fa-user"></i> <strong>Manage Admin</strong></a></li><br>
        <li><a href="profile.php"><i class="fas fa-user"></i> <strong>Profile</strong></a></li><br>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li><br>
    </ul>
</aside>

<div class="expense-container">
    <h2>Expense Details for <?php echo htmlspecialchars($user['Uname']); ?></h2>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Payment Method</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($expense_result->num_rows > 0) {
                    while ($expense = $expense_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . date("d-m-Y", strtotime($expense['date'])) . "</td>";
                        echo "<td>" . htmlspecialchars($expense['category']) . "</td>";
                        echo "<td>₹" . number_format($expense['amount'], 2) . "</td>";
                        echo "<td>" . htmlspecialchars($expense['description']) . "</td>";
                        echo "<td>" . htmlspecialchars($expense['Payment_Method']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No expenses found for this user.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <div class="back-button">
        <a href="admin_registered_users.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Users</a>
    </div>
</div>

<style>
.expense-container {
    margin: 20px 0 20px 20%;
    padding: 20px;
    background-color: #86a69c;
    border-radius: 20px;
    width: 75%;
    max-width: 1200px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.expense-container h2 {
    text-align: center;
    color: white;
    margin-bottom: 20px;
    font-size: 24px;
}

.table-container {
    margin-top: 20px;
    width: 100%;
    overflow-x: auto;
    padding: 0 10px;
}

table {
    width: 100%;
    min-width: 800px;
    max-width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin-right: 10rem;

}

th, td {
    padding: 12px 8px;
    text-align: left;
    color: white;
    word-wrap: break-word;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-right: 10rem;
}

/* Fixed column widths */
th:nth-child(1), td:nth-child(1) { /* Date */
    width: 12%;
}
th:nth-child(2), td:nth-child(2) { /* Category */
    width: 18%;
}
th:nth-child(3), td:nth-child(3) { /* Amount */
    width: 15%;
    text-align: right;
}
th:nth-child(4), td:nth-child(4) { /* Description */
    width: 35%;
}
th:nth-child(5), td:nth-child(5) { /* Payment Method */
    width: 20%;
}

tr:hover td {
    background-color: #779589;
}

/* Make the container scrollable on smaller screens */
@media screen and (max-width: 1024px) {
    .expense-container {
        margin: 20px 5%;
        width: 90%;
    }
    
    .table-container {
        margin: 20px 0;
        padding: 0;
    }
}

.action-buttons {
    display: flex;
    gap: 10px;
    align-items: center;
}

.action-buttons .edit-btn,
.action-buttons .delete-btn,
.back-btn {
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    transition: background-color 0.3s;
    display: inline-block;
    font-weight: 500;
}

.action-buttons .edit-btn,
.back-btn {
    background-color: #4CAF50;
}

.action-buttons .edit-btn:hover,
.back-btn:hover {
    background-color: #45a049;
}

.action-buttons .delete-btn {
    background-color: #f44336;
}

.action-buttons .delete-btn:hover {
    background-color: #d32f2f;
}

.action-buttons form {
    display: inline;
    margin: 0;
}

.back-button {
    margin-top: 20px;
    text-align: center;
}
</style>

</body>
</html>
