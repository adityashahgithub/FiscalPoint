<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['Uid'])) {
    die("User not logged in");
}
$uid = $_SESSION['Uid'];
$month = isset($_POST['month']) ? $_POST['month'] : date('Y-m');

// Fetch budget data for the selected month
$sql = "SELECT Bid, Amount FROM Budget WHERE Uid = ? AND Month = ? ORDER BY Bid";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL error: " . $conn->error);
}

$stmt->bind_param("is", $uid, $month);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[$row['Bid']] = $row['Amount'];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Line Graph</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <form method="post">
        <label for="month">Select Month:</label>
        <input type="month" id="month" name="month" value="<?php echo htmlspecialchars($month); ?>">
        <button type="submit">Generate Graph</button>
    </form>

    <canvas id="budgetChart"></canvas>

    <script>
        const ctx = document.getElementById('budgetChart').getContext('2d');
        const data = <?php echo json_encode($data); ?>;
        
        const labels = Object.keys(data).map(id => `Entry ${id}`);
        const amounts = Object.values(data);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Amount Spent',
                    data: amounts,
                    borderColor: 'blue',
                    fill: false
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { title: { display: true, text: 'Entry ID' } },
                    y: { title: { display: true, text: 'Amount Spent' } }
                }
            }
        });
    </script>
</body>
</html>
