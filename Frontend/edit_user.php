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
$stmt = $conn->prepare("SELECT Uid, Uname, email, Phone_no FROM User WHERE Uid = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: admin_registered_users.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $uname = $_POST['uname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    // Get the referring page if it was set
    $redirect_page = isset($_POST['redirect_page']) ? $_POST['redirect_page'] : 'admin_registered_users.php';

    $update_stmt = $conn->prepare("UPDATE User SET Uname = ?, email = ?, Phone_no = ? WHERE Uid = ?");
    $update_stmt->bind_param("sssi", $uname, $email, $phone, $uid);

    if ($update_stmt->execute()) {
        echo "<script>alert('User updated successfully.'); window.location.href='" . $redirect_page . "';</script>";
    } else {
        echo "<script>alert('Failed to update user.');</script>";
    }
    $update_stmt->close();
}

// Determine the referring page
$redirect_page = 'admin_registered_users.php'; // Default
if (isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    if (strpos($referer, 'manage_admin.php') !== false) {
        $redirect_page = 'manage_admin.php';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
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

<div class="edit-form-container">
    <h2>Edit User Details</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label for="uname">Username:</label>
            <input type="text" id="uname" name="uname" value="<?php echo htmlspecialchars($user['Uname']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['Phone_no']); ?>" required>
        </div>
        
        <div class="form-actions">
            <input type="hidden" name="redirect_page" value="<?php echo $redirect_page; ?>">
            <button type="submit" class="save-btn"><i class="fas fa-save"></i> Save Changes</button>
            <a href="<?php echo $redirect_page; ?>" class="cancel-btn"><i class="fas fa-times"></i> Cancel</a>
        </div>
    </form>
</div>

<style>
.edit-form-container {
    margin-left: 20%;
    padding: 20px;
    background-color: #86a69c;
    border-radius: 20px;
    width: 60%;
}

.edit-form-container h2 {
    text-align: center;
    color: white;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: white;
    font-weight: bold;
}

.form-group input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
}

.form-actions {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
}

.save-btn, .cancel-btn {
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    transition: background-color 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-weight: 500;
}

.save-btn {
    background-color: #4CAF50;
}

.save-btn:hover {
    background-color: #45a049;
}

.cancel-btn {
    background-color: #f44336;
}

.cancel-btn:hover {
    background-color: #d32f2f;
}
</style>

</body>
</html>
<?php $conn->close(); ?>
