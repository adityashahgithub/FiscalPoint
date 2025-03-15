<?php
session_start();
require 'vendor/autoload.php'; // Load libraries
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FiscalPoint";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$uid = $_SESSION['Uid']; // User ID from session
$user_email = $_SESSION['UserEmail']; // User's Email from session

if (isset($_POST['export_excel'])) {
    exportToExcel($conn, $uid);
} elseif (isset($_POST['export_pdf'])) {
    exportToPDF($conn, $uid);
} elseif (isset($_POST['email_report'])) {
    emailReport($conn, $uid, $user_email);
}

// Function to Export Data to Excel
function exportToExcel($conn, $uid) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Expense Data
    $sheet->setCellValue('A1', 'Date');
    $sheet->setCellValue('B1', 'Category');
    $sheet->setCellValue('C1', 'Amount');
    $sheet->setCellValue('D1', 'Payment Type');

    $sql = "SELECT Date, Category, Amount, PaymentType FROM Expense WHERE Uid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = 2;
    while ($data = $result->fetch_assoc()) {
        $sheet->setCellValue("A$row", $data['Date']);
        $sheet->setCellValue("B$row", $data['Category']);
        $sheet->setCellValue("C$row", $data['Amount']);
        $sheet->setCellValue("D$row", $data['PaymentType']);
        $row++;
    }

    // Budget Data
    $sheet->setCellValue("F1", "Month");
    $sheet->setCellValue("G1", "Budget Amount");

    $sql_budget = "SELECT Month, Amount FROM Budget WHERE Uid = ?";
    $stmt = $conn->prepare($sql_budget);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = 2;
    while ($data = $result->fetch_assoc()) {
        $sheet->setCellValue("F$row", $data['Month']);
        $sheet->setCellValue("G$row", $data['Amount']);
        $row++;
    }

    // Export Excel File
    $writer = new Xlsx($spreadsheet);
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment; filename=User_Report.xlsx");
    $writer->save("php://output");
    exit;
}

// Function to Export Data to PDF
function exportToPDF($conn, $uid) {
    $mpdf = new Mpdf();
    $mpdf->WriteHTML("<h2>User Expense & Budget Report</h2>");

    // Expense Data
    $html = '<h3>Expenses</h3><table border="1" cellpadding="5"><tr><th>Date</th><th>Category</th><th>Amount</th><th>Payment Type</th></tr>';
    $sql = "SELECT Date, Category, Amount, PaymentType FROM Expense WHERE Uid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($data = $result->fetch_assoc()) {
        $html .= "<tr><td>{$data['Date']}</td><td>{$data['Category']}</td><td>{$data['Amount']}</td><td>{$data['PaymentType']}</td></tr>";
    }
    $html .= '</table>';

    // Budget Data
    $html .= '<h3>Budget</h3><table border="1" cellpadding="5"><tr><th>Month</th><th>Budget Amount</th></tr>';
    $sql_budget = "SELECT Month, Amount FROM Budget WHERE Uid = ?";
    $stmt = $conn->prepare($sql_budget);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($data = $result->fetch_assoc()) {
        $html .= "<tr><td>{$data['Month']}</td><td>{$data['Amount']}</td></tr>";
    }
    $html .= '</table>';

    $mpdf->WriteHTML($html);
    $mpdf->Output("User_Report.pdf", "D"); // Download
    exit;
}

// Function to Email Report
function emailReport($conn, $uid, $user_email) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com';
        $mail->Password = 'your-email-password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('your-email@gmail.com', 'FiscalPoint');
        $mail->addAddress($user_email);
        $mail->Subject = 'Your Expense & Budget Report';
        $mail->Body = 'Attached is your expense and budget report from FiscalPoint.';
        $mail->addAttachment('User_Report.pdf');

        $mail->send();
        echo "Email Sent Successfully!";
    } catch (Exception $e) {
        echo "Error: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html>
<body>
    <h2>Export Your Report</h2>
    <form method="post">
        <button type="submit" name="export_excel">Export as Excel</button>
        <button type="submit" name="export_pdf">Export as PDF</button>
        <button type="submit" name="email_report">Email Report</button>
    </form>
</body>
</html>
