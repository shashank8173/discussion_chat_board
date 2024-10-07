<?php
require 'vendor/autoload.php'; // Include PHPSpreadsheet via Composer autoload or manual inclusion

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bsm_dashboard";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from po_summary table
$sql = "SELECT id, po_date, po_expiry_date, po_number, pc, location, status, invoice_value, delivery_tat, fill_rate, appointment FROM po_summary";
$result = $conn->query($sql);

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set the headers of the columns
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'PO Date');
$sheet->setCellValue('C1', 'PO Expiry Date');
$sheet->setCellValue('D1', 'PO Number');
$sheet->setCellValue('E1', 'PC');
$sheet->setCellValue('F1', 'Location');
$sheet->setCellValue('G1', 'Status');
$sheet->setCellValue('H1', 'Invoice Value');
$sheet->setCellValue('I1', 'Delivery TAT');
$sheet->setCellValue('J1', 'Fill Rate');
$sheet->setCellValue('K1', 'Appointment');

// Populate the rows with data from the table
if ($result->num_rows > 0) {
    $rowNumber = 2; // Start from the second row
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $rowNumber, $row['id']);
        $sheet->setCellValue('B' . $rowNumber, $row['po_date']);
        $sheet->setCellValue('C' . $rowNumber, $row['po_expiry_date']);
        $sheet->setCellValue('D' . $rowNumber, $row['po_number']);
        $sheet->setCellValue('E' . $rowNumber, $row['pc']);
        $sheet->setCellValue('F' . $rowNumber, $row['location']);
        $sheet->setCellValue('G' . $rowNumber, $row['status']);
        $sheet->setCellValue('H' . $rowNumber, $row['invoice_value']);
        $sheet->setCellValue('I' . $rowNumber, $row['delivery_tat']);
        $sheet->setCellValue('J' . $rowNumber, $row['fill_rate']);
        $sheet->setCellValue('K' . $rowNumber, $row['appointment']);
        $rowNumber++;
    }
}

// Close the connection
$conn->close();

// Set the headers for the Excel file download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="po_table.xlsx"');
header('Cache-Control: max-age=0');

// Write the file to output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
