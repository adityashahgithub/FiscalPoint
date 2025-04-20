<?php
session_start();
if (!isset($_SESSION["Uid"]) || $_SESSION["Role"] !== "admin") {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["uid"])) {
    echo "User ID not provided.";
    exit();
}

$uid = $_GET["uid"];
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";
$conn = new mysqli($servername, $username, $password, $dbname);

$sql = "SELECT Amount, Category, Date, Note FROM Expense WHERE Uid=? ORDER BY Date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Expense Details</title>
</head>
<body>
    <h2>User Expense Details</h2>
    <table border="1">
        <tr><th>Amount</th><th>Category</th><th>Date</th><th>Note</th></tr>
        <?php
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . $row["Amount"] . "</td>
                    <td>" . $row["Category"] . "</td>
                    <td>" . $row["Date"] . "</td>
                    <td>" . $row["Note"] . "</td>
                  </tr>";
        }
        ?>
    </table>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
