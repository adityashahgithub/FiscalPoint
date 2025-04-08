<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';
session_start();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION["Uid"])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION["Uid"];
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$selected_month = $_POST['month'] ?? date('Y-m');
$date_order = $_POST['date_order'] ?? '';
$amount_order = $_POST['amount_order'] ?? '';
$payment_type = $_POST['payment_type'] ?? '';

$order_by_clauses = [];
if (!empty($date_order)) $order_by_clauses[] = "date $date_order";
if (!empty($amount_order)) $order_by_clauses[] = "amount $amount_order";
$order_by = !empty($order_by_clauses) ? " ORDER BY " . implode(", ", $order_by_clauses) : " ORDER BY date ASC";

$payment_type_filter = "";
if (!empty($payment_type)) {
    $payment_type_filter = " AND Payment_Method = ?";
}

$sql = "SELECT category, description AS Item, amount AS Cost, date AS Date, Payment_Method 
        FROM Expense 
        WHERE Uid = ? AND DATE_FORMAT(date, '%Y-%m') = ?" . $payment_type_filter . $order_by;

$stmt = $conn->prepare($sql);
if (!empty($payment_type)) {
    $stmt->bind_param("iss", $user_id, $selected_month, $payment_type);
} else {
    $stmt->bind_param("is", $user_id, $selected_month);
}
$stmt->execute();
$result = $stmt->get_result();

// Excel File Creation
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Headers
$headers = ['Category', 'Item', 'Cost', 'Date', 'Payment Method'];
$sheet->fromArray($headers, NULL, 'A1');

// Data Rows
$rowNum = 2;
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue("A{$rowNum}", $row['category']);
    $sheet->setCellValue("B{$rowNum}", $row['Item']);
    $sheet->setCellValue("C{$rowNum}", number_format($row['Cost'], 2));
    $sheet->setCellValue("D{$rowNum}", date("d-m-Y", strtotime($row['Date'])));
    $sheet->setCellValue("E{$rowNum}", $row['Payment_Method']);
    $rowNum++;
}

// Download Headers
$filename = "expenses_{$selected_month}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
