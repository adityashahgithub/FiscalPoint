<?php
session_start();
if (!isset($_SESSION["Uid"]) || $_SESSION["Role"] !== "admin") {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["uid"])) {
    echo "User ID not specified.";
    exit();
}

$uid = $_GET["uid"];
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";
$conn = new mysqli($servername, $username, $password, $dbname);

// Handle Update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $uname = $_POST["uname"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];

    $stmt = $conn->prepare("UPDATE User SET Uname=?, email=?, phone=? WHERE Uid=?");
    $stmt->bind_param("sssi", $uname, $email, $phone, $uid);
    if ($stmt->execute()) {
        echo "<script>alert('User updated successfully.'); window.location.href='manage_users.php';</script>";
    } else {
        echo "<script>alert('Update failed.');</script>";
    }
    $stmt->close();
}

// Fetch user details
$stmt = $conn->prepare("SELECT Uname, email, phone FROM User WHERE Uid=?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$stmt->bind_result($uname, $email, $phone);
$stmt->fetch();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
</head>
<body>
    <h2>Edit User Details</h2>
    <form method="POST">
        <label>Name:</label><br>
        <input type="text" name="uname" value="<?php echo htmlspecialchars($uname); ?>"><br><br>
        <label>Email:</label><br>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>"><br><br>
        <label>Phone:</label><br>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>"><br><br>
        <button type="submit">Update</button>
    </form>
</body>
</html>
