<?php
// Database connection
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

// Fetch expenses from database
$sql = "SELECT id, category, item, amount, Date FROM Expense";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabular Report</title>
    <link rel="stylesheet" href="css/tabularreport.css">
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
            <li> <a href="addexpense.php">Add Expense </a></li><br>
            <li> <a href="linegraph.php">Line Graph Report </a></li><br>
            <li> <a href="linegraph.php">Pie Graph Report </a></li><br>
            <li> <a href="profile.html">Profile</a></li><br>
            <li> <a href="logout.php">Logout</a></li><br>
        </ul>
    </aside>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Sr no.</th>
                    <th>Category</th>
                    <th>Item</th>
                    <th>Cost</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $sr_no = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$sr_no}</td>
                                <td>{$row['category']}</td>
                                <td>{$row['item']}</td>
                                <td>{$row['amount']}</td>
                                <td>{$row['Date']}</td>
                              </tr>";
                        $sr_no++;
                    }
                } else {
                    echo "<tr><td colspan='5'>No expenses found</td></tr>";
                }
                ?>
            </tbody>
        </table>
</body>
</html>