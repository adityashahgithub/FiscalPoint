<?php
// Start session
session_start();

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
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
<img src="css/logo.png" alt="Logo" class="logo" onclick="location.href='landing.html'">


<aside class="sidebar">
        <div class="profile">
            <img src="css/profile.png" alt="Profile Image" class="avatar">
        </div>
        <ul class="menu">
            <li><a href="dashboard.php"><span style="font-weight: bold;">Dashboard</span></a></li><br>
            <li><a href="setbudget.php"><span style="font-weight: bold";>Budget</span></a></li><br>
            <li><a href="addexpense.php"><span style="font-weight:bold";>Add Expense</span></a></li><br>
            <li>
            <li class="dropdown">
            <a href="#"><span style="font-style: italic; font-weight: bold;">Graph Reports:</span></a>
            <ul>
            <li><a href="linegraph.php">Line Graph Report</a></li>
            <li><a href="piegraph.php">Pie Graph Report</a></li>
        </ul>
            </li>
            <br>
    <li>
        <a href="#"> <span style="font-style: italic; font-weight: bold;">Tabular Reports:</span></a><br>
        <ul>
            <li><a href="tabularreport.php">All Expenses</a></li>
            <li><a href="categorywisereport.php">Category wise Expense</a></li>
        </ul>
    </li><br>
            <li><a href="profile.php"><span style="font-weight:bold;">Profile</span></a></li><br>
            <li><a href="logout.php"><span style="font-weight:bold";>Logout</span></a></li><br>
        </ul>
    </aside>

    <div class="profile-container">
        <div class="profile-card">
            <h1>User Details</h1>
            <h2>Name:</h2>
            <div class="input-field"><?php echo htmlspecialchars($user['Uname']); ?></div>

            <h2>Email:</h2>
            <div class="input-field"><?php echo htmlspecialchars($user['email']); ?></div>

            <h2>Phone Number:</h2>
            <div class="input-field"><?php echo htmlspecialchars($user['Phone_no']); ?></div>
          <br>
            <div class="button-group">
                <button class="btn reset-btn" onclick="openModal()">Reset Password</button>
                <button class="btn forgot-btn" onclick="openModal()">Forgot Password</button>
                <button class="btn delete-btn" onclick="confirmDelete()">Delete Account</button>
            </div>
        </div>
    </div>

<!-- Password Reset Modal -->
<div id="resetPasswordModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Reset Password</h2>
        <input type="password" id="newPassword" placeholder="Enter new password">
        <input type="password" id="confirmPassword" placeholder="Confirm new password">
        <button onclick="resetPassword()">Submit</button>
    </div>
</div>
<!-- JavaScript for Modal & Password Reset -->
<script>
function openModal() {
    document.getElementById("resetPasswordModal").style.display = "block";
}

function closeModal() {
    document.getElementById("resetPasswordModal").style.display = "none";
}

function resetPassword() {
    let newPassword = document.getElementById("newPassword").value;
    let confirmPassword = document.getElementById("confirmPassword").value;

    if (newPassword !== confirmPassword) {
        alert("Passwords do not match!");
        return;
    }

    fetch('reset_password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ password: newPassword })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Password reset successful!");
            closeModal();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

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
