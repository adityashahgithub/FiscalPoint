<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['Uid'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["Uid"];
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$uid = $_SESSION['Uid'];
$selected_month = isset($_POST['month']) ? $_POST['month'] : date('Y-m');
$month = date("m", strtotime($selected_month));
$year = date("Y", strtotime($selected_month));

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
            cursor: pointer;
        }
        .calendar td.empty {
            background-color: #333;
        }
        form button {
            background-color: #4caf50;
            border: none;
            color: white;
            padding: 10px 20px;
            margin: 10px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        form button:hover {
            background-color: #45a049;
        }
        .month-nav {
            text-align: center;
            margin-top: 20px;
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

            for ($i = 1; $i < $first_day_of_month; $i++) {
                echo '<td class="empty"></td>';
                $cell_count++;
            }

            for ($day = 1; $day <= $total_days; $day++) {
                $classes = "";
                if ($day == $today && $month == $current_month && $year == $current_year) {
                    $classes .= "today ";
                }
                if (isset($daily_expenses[$day])) {
                    $classes .= "has-expense";
                }

                $amount = isset($daily_expenses[$day]) ? number_format($daily_expenses[$day], 2) : '';
                echo "<td class='$classes' data-day='$day' data-amount='$amount'>$day";
                if ($amount !== '') {
                    echo "<br>₹" . $amount;
                }
                echo "</td>";

                if ($cell_count % 7 == 0) {
                    echo "</tr><tr>";
                }

                $cell_count++;
            }

            while (($cell_count - 1) % 7 != 0) {
                echo '<td class="empty"></td>';
                $cell_count++;
            }
            ?>
        </tr>
    </table>
</div>

<div class="month-nav">
    <form method="POST" id="monthForm">
        <input type="hidden" name="month" id="monthInput" value="<?php echo $selected_month; ?>">
        <button type="button" onclick="changeMonth(-1)">← Prev</button>
        <button type="button" onclick="changeMonth(1)">Next →</button>
    </form>
</div>

<script>
    function changeMonth(offset) {
        const currentMonth = new Date(document.getElementById('monthInput').value + '-01');
        currentMonth.setMonth(currentMonth.getMonth() + offset);
        const newMonth = currentMonth.toISOString().slice(0, 7);
        document.getElementById('monthInput').value = newMonth;
        document.getElementById('monthForm').submit();
    }

    // ← → arrow key support
    document.addEventListener('keydown', function(event) {
        if (event.key === 'ArrowLeft') {
            changeMonth(-1);
        } else if (event.key === 'ArrowRight') {
            changeMonth(1);
        }
    });

    // Click day cell to show popup
    document.querySelectorAll(".calendar td.has-expense").forEach(cell => {
        cell.addEventListener("click", () => {
            const day = cell.dataset.day.padStart(2, '0');
            const amount = cell.dataset.amount;
            const date = "<?php echo "$year-$month-"; ?>" + day;
            alert("📅 Date: " + date + "\n💸 Expense: ₹" + amount);
        });
    });
</script>

</body>
</html>
