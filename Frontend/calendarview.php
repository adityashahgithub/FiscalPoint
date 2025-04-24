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

$sql_details = "SELECT DAY(Date) AS day, Category, SUM(Amount) AS category_total, Description 
                FROM Expense 
                WHERE Uid = ? AND MONTH(Date) = ? AND YEAR(Date) = ? 
                GROUP BY DAY(Date), Category";
$stmt = $conn->prepare($sql_details);
$stmt->bind_param("iii", $uid, $month, $year);
$stmt->execute();
$result_details = $stmt->get_result();

$expense_details = [];
while ($row = $result_details->fetch_assoc()) {
    if (!isset($expense_details[$row['day']])) {
        $expense_details[$row['day']] = [];
    }
    $expense_details[$row['day']][] = [
        'category' => $row['Category'],
        'amount' => $row['category_total'],
        'description' => $row['Description']
    ];
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Popup styling */
        .expense-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color:rgb(0, 0, 2);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            z-index: 1000;
            min-width: 300px;
            max-width: 80%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .expense-popup h3 {
            margin-top: 0;
            color: white;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        .expense-popup table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .expense-popup th, .expense-popup td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .expense-popup th {
            background-color:rgb(0, 0, 2);
        }
        
        .close-popup {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 20px;
            color: #999;
        }
        
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 999;
        }
    </style>
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
    <li><a href="insights.php"><i class="fas fa-lightbulb"></i> <strong>Insights</strong></a></li><br>
    <li><a href="predictions.php"><i class="fas fa-chart-line"></i> <strong>Predictions</strong></a></li><br>
    <li><a href="profile.php"><i class="fas fa-user"></i> <strong>Profile</strong></a></li><br>
    <li><a href="query.php"><i class="fas fa-question-circle"></i> <strong>Query</strong></a></li><br>
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

<!-- Add the popup and overlay divs -->
<div class="overlay" id="overlay"></div>
<div class="expense-popup" id="expensePopup">
    <span class="close-popup" onclick="closePopup()">&times;</span>
    <h3 id="popupDate"></h3>
    <div id="popupContent"></div>
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

    // Click day cell to show popup with detailed expense breakdown
    document.querySelectorAll(".calendar td.has-expense").forEach(cell => {
        cell.addEventListener("click", () => {
            const day = cell.dataset.day.padStart(2, '0');
            const date = "<?php echo "$year-$month-"; ?>" + day;
            
            // Get the expense details from PHP
            const expenseDetails = <?php echo json_encode($expense_details); ?>;
            const dayDetails = expenseDetails[parseInt(cell.dataset.day)];
            
            // Format the date for display
            const formattedDate = new Date(date).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            // Set the popup title
            document.getElementById('popupDate').textContent = formattedDate;
            
            // Build the content table
            let content = '<table><thead><tr><th>Category</th><th>Amount</th></tr></thead><tbody>';
            
            if (dayDetails && dayDetails.length > 0) {
                dayDetails.forEach(detail => {
                    content += `<tr>
                        <td>${detail.category}</td>
                        <td>₹${parseFloat(detail.amount).toFixed(2)}</td>
                    </tr>`;
                });
            }
            
            content += '</tbody></table>';
            
            // Add total row
            content += `<p><strong>Total: ₹${parseFloat(cell.dataset.amount).toFixed(2)}</strong></p>`;
            
            // Set the content
            document.getElementById('popupContent').innerHTML = content;
            
            // Show the popup
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('expensePopup').style.display = 'block';
        });
    });
    
    function closePopup() {
        document.getElementById('overlay').style.display = 'none';
        document.getElementById('expensePopup').style.display = 'none';
    }
    
    // Close popup when clicking overlay
    document.getElementById('overlay').addEventListener('click', closePopup);
</script>

</body>
</html>
