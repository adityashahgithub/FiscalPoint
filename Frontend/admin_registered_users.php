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

// Handle Delete
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $delete_uid = $_POST["delete_id"];
    $stmt = $conn->prepare("DELETE FROM User WHERE Uid = ?");
    $stmt->bind_param("i", $delete_uid);
    if ($stmt->execute()) {
        echo "<script>
            alert('User deleted successfully.');
            window.location.href='admin_registered_users.php';
        </script>";
    } else {
        echo "<script>alert('Failed to delete user.');</script>";
    }
    $stmt->close();
}

// Fetch all users with correct column names
$sql = "SELECT Uid, Uname, email, Phone_no, Created_At FROM User ORDER BY Created_At DESC";
$result_users = $conn->query($sql);
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
        <li><a href="admin_category.php"><i class="fas fa-tags"></i> Category</a></li><br>
        <li><a href="admin_registered_users.php"><i class="fas fa-user-friends"></i> Reg Users</a></li><br>
        <li><a href="admin_query.php"><i class="fas fa-question-circle"></i> <strong>Query</strong></a></li><br>
        <li><a href="add_admin.php"><i class="fas fa-user-plus"></i> <strong>Add Admin</strong></a></li><br>
        <li><a href="manage_admin.php"><i class="fas fa-user-cog"></i> <strong>Manage Admin</strong></a></li><br>
        <li><a href="admin_profile.php"><i class="fas fa-id-card"></i> Profile</a></li><br>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li><br>
    </ul>
</aside>

<div>
 
    <h2 style="text-align:center;">Registered Users</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Sr No.</th>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Register Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
<?php
if ($result_users->num_rows > 0) {
    $sr_no = 1;
    while ($row = $result_users->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$sr_no}</td>";
        echo "<td>" . htmlspecialchars($row["Uname"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["Phone_no"]) . "</td>";
        echo "<td>" . date("d-m-Y", strtotime($row["Created_At"])) . "</td>";
        echo "<td class='action-buttons'>
                <a href='edit_user.php?uid={$row["Uid"]}' class='edit-btn'>Edit</a>
                <form method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this user?\");'>
                    <input type='hidden' name='delete_id' value='" . $row["Uid"] . "'>
                    <button type='submit' class='delete-btn'>Delete</button>
                </form>
                <a href='expense_details.php?uid={$row["Uid"]}' class='edit-btn'>Expense Details</a>
              </td>";
        echo "</tr>";
        $sr_no++;
    }
} else {
    echo "<tr><td colspan='6'>No registered users found.</td></tr>";
}
?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

<?php $conn->close(); ?>
