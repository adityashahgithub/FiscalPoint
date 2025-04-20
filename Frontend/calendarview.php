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
   <link rel="stylesheet" href="css/calendarview.css">
</head>
<body>
<aside class="sidebar">
        <div class="profile">
            <img src="css/profile.png" alt="Profile Image" class="avatar">
        </div>
        <ul class="menu">
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <strong>Dashboard</strong></a></li><br>
    <li><a href="addincome.php"><i class="fas fa-wallet"></i> <span style="font-weight: bold;">Income</span></a></li><br>
    <li><a href="setbudget.php"><i class="fas fa-coins"></i> <strong>Budget</strong></a></li><br>
    <li><a href="addexpense.php"><i class="fas fa-plus-circle"></i> <strong>Add Expense</strong></a></li><br>
    
    <li class="dropdown">
                <a href="#"><i class="fas fa-chart-bar"></i> <strong><em>Graph Reports:</em></strong></a>
                <ul>
                    <li><a href="linegraph.php"><i class="fas fa-chart-line"></i> Line Graph Report</a></li>
                    <li><a href="piegraph.php"><i class="fas fa-chart-pie"></i> Pie Graph Report</a></li>
                </ul>
    </li><br>
    <li class="dropdown">
                <a href="#"><i class="fas fa-table"></i> <strong><em>Tabular Reports:</em></strong></a><br>
                <ul>
                    <li><a href="tabularreport.php"><i class="fas fa-list-alt"></i> All Expenses</a></li>
                    <li><a href="categorywisereport.php"><i class="fas fa-layer-group"></i> Category-wise Expense</a></li>
                </ul>
    </li><br>
    <li><a href="insights.php"><i class="fas fa-robot"></i> <strong>Insights</strong></a></li><br>
    <li><a href="predictions.php"><i class="fas fa-robot"></i> <strong>Predictions</strong></a></li><br>
    <li><a href="profile.php"><i class="fas fa-user"></i> <strong>Profile</strong></a></li><br>
    <li><a href="query.php"><i class="fas fa-user"></i> <strong>Query</strong></a></li><br>
    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <strong>Logout</strong></a></li><br>
        </ul>
    </aside>
    
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
