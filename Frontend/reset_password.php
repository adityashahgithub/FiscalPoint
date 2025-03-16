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
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['password'])) {
    $newPassword = password_hash($data['password'], PASSWORD_DEFAULT); // Hash password

    $query = "UPDATE User SET password = ? WHERE Uid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $newPassword, $uid);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update password."]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}

$conn->close();
?>
