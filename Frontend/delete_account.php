<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed."]));
}

// Ensure user is logged in
if (!isset($_SESSION['Uid'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit();
}

$uid = $_SESSION['Uid'];

// Delete the user account
$query = "DELETE FROM User WHERE Uid = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $uid);

if ($stmt->execute()) {
    session_destroy(); // Destroy session after deleting the account
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to delete account."]);
}

$stmt->close();
$conn->close();
?>