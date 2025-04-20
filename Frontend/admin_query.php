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


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="css/admin_registered_users.css"> <!-- Use the same CSS -->
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
        <li><a href="admin_profile.php"><i class="fas fa-user"></i> Profile</a></li><br>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li><br>
        <div class="main-content">
    </ul>
    </aside>
    <div>
    <h1> <span style="text-align:center;">User Queries </span></h1>
    <div class="table-container">
    
    <table class="query-table">
        <thead>
            <tr>
                <th>Email</th>
                <th>Query Type</th>
                <th>Description</th>
                <th>Submitted At</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT Email, Query_type, Description, Created_At FROM Query ORDER BY Created_At DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['Email']) . "</td>
                            <td>" . htmlspecialchars($row['Query_type']) . "</td>
                            <td>" . htmlspecialchars($row['Description']) . "</td>
                            <td>" . $row['Created_At'] . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No queries submitted yet.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    </div>
    </div>
</div>

    

</body>