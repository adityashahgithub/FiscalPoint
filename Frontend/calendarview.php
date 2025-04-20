<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['Uid'])) {
    header("Location: login.php");
    exit();
}
// Retrieve logged-in user ID
$user_id = $_SESSION["Uid"];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$uid = $_SESSION['Uid'];
$selected_month = isset($_POST['month']) ? $_POST['month'] : date('Y-m');
$month = date("m", strtotime($selected_month));
$year = date("Y", strtotime($selected_month));

// Day-wise expenses for calendar view
$sql_calendar = "SELECT DAY(Date) AS day, SUM(amount) AS total FROM Expense WHERE Uid = ? AND MONTH(Date) = ? AND YEAR(Date) = ? GROUP BY DAY(Date)";
$stmt = $conn->prepare($sql_calendar);
$stmt->bind_param("iii", $uid, $month, $year);
$stmt->execute();
$result_calendar = $stmt->get_result();

$daily_expenses = [];
while ($row = $result_calendar->fetch_assoc()) {
    $daily_expenses[$row['day']] = $row['total'];
}
$stmt->close();

// Get first day of the month
$first_day_of_month = date('N', strtotime("$year-$month-01")); // 1 (Mon) to 7 (Sun)
$total_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$today = date("j");
$current_month = date("n");
$current_year = date("Y");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Calendar Day-wise Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: white;
            margin: 0;
            padding: 20px;
        }
        .calendar {
            margin-top: 30px;
            background-color: #222;
            border-radius: 10px;
            padding: 20px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        .calendar h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .calendar table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }
        .calendar th, .calendar td {
            padding: 10px;
            border: 1px solid #444;
            width: 14.2%;
        }
        .calendar td.today {
            background-color: #4caf50;
            color: white;
            font-weight: bold;
        }
        .calendar td.has-expense {
            background-color: #ff9800;
            color: white;
        }
        .calendar td.empty {
            background-color: #333;
        }
    </style>
</head>
<body>

<div class="calendar">
    <h2><?php echo date("F Y", strtotime("$year-$month-01")); ?></h2>
    <table>
        <tr>
            <th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th>
            <th>Fri</th><th>Sat</th><th>Sun</th>
        </tr>
        <tr>
            <?php
            $day_counter = 1;
            $cell_count = 1;

            // Print empty cells before first day
            for ($i = 1; $i < $first_day_of_month; $i++) {
                echo '<td class="empty"></td>';
                $cell_count++;
            }

            // Print actual days
            for ($day = 1; $day <= $total_days; $day++) {
                $classes = "";
                if ($day == $today && $month == $current_month && $year == $current_year) {
                    $classes .= "today ";
                }
                if (isset($daily_expenses[$day])) {
                    $classes .= "has-expense";
                }

                echo "<td class='$classes'>$day";
                if (isset($daily_expenses[$day])) {
                    echo "<br>₹" . number_format($daily_expenses[$day], 2);
                }
                echo "</td>";

                // Break row after Sunday
                if ($cell_count % 7 == 0) {
                    echo "</tr><tr>";
                }

                $cell_count++;
            }

            // Fill remaining cells of the last row
            while (($cell_count - 1) % 7 != 0) {
                echo '<td class="empty"></td>';
                $cell_count++;
            }
            ?>
        </tr>
    </table>
</div>

</body>
</html>
