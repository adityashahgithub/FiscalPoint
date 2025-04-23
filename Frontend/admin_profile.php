<?php
// Start session
session_start();
if (!isset($_SESSION["Uid"]) || $_SESSION["Role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "FiscalPoint"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['Uid'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['Uid'];

// Fetch user details
$query = "SELECT Uname, email, Phone_no FROM User WHERE Uid = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found!";
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="css/admin_userprofile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'">

    <aside class="sidebar">
        <div class="profile">
            <img src="css/profile.png" alt="Profile Image" class="avatar">
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

    <div class="profile-container">
        <div class="profile-card">
            <h1>Admin Details</h1>
            <h2>Name:</h2>
            <div class="input-field"><?php echo htmlspecialchars($user['Uname']); ?></div>

            <h2>Email:</h2>
            <div class="input-field"><?php echo htmlspecialchars($user['email']); ?></div>

            <h2>Phone Number:</h2>
            <div class="input-field"><?php echo htmlspecialchars($user['Phone_no']); ?></div>
            <br>
            <div class="button-group">
                <!-- Update reset password button to link to reset_password.php -->
                <a href="reset_password.php" class="btn reset-btn">Reset Password</a>
                <a href="forgot_password.php" class="btn forgot-btn">Forgot Password</a>
                <button class="btn delete-btn" onclick="confirmDelete()">Delete Account</button>
            </div>
        </div>
    </div>

<style>
/* Add consistent styling for buttons */
.button-group {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
}

.btn {
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    transition: background-color 0.3s ease;
    display: inline-block;
    font-weight: 500;
}

.reset-btn {
    background-color: #4CAF50;
}

.reset-btn:hover {
    background-color: #45a049;
}

.forgot-btn {
    background-color: #2196F3;
}

.forgot-btn:hover {
    background-color: #0b7dda;
}

.delete-btn {
    background-color: #f44336;
}

.delete-btn:hover {
    background-color: #d32f2f;
}
</style>

<!-- JavaScript for Delete Account Confirmation -->
<script>
function confirmDelete() {
    let confirmation = confirm("Are you sure you want to delete your account?");
    if (confirmation) {
        fetch('delete_account.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ delete: true })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Account deleted successfully!");
                window.location.href = "landing.html"; // Redirect to landing page
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
</script>

</body>
</html>
