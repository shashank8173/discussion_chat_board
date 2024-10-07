<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bsm_dashboard";

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_FILES['excelFile'])) {
    $fileName = $_FILES['excelFile']['name'];
    $tempFilePath = $_FILES['excelFile']['tmp_name'];

    // Extract PO number from the filename (before the first underscore)
    $poNumber = explode('_', $fileName)[0];

    // Load the spreadsheet
    $spreadsheet = IOFactory::load($tempFilePath);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    // Assuming the first row contains column names
    $columns = $rows[0];
    array_shift($columns); // Remove the first column

    // Filter out empty column names and ensure they are valid
    $columns = array_filter($columns, fn($col) => !empty($col));
    if (empty($columns)) {
        die("No valid columns found in the Excel sheet.");
    }

    // Table name (fixed as 'excel_table')
    $tableName = "excel_table";

    // Check if the table already exists
    $tableExistsQuery = $conn->query("SHOW TABLES LIKE '$tableName'");
    if ($tableExistsQuery->num_rows == 0) {
        // Create table only if it does not exist
        $createTableSQL = "CREATE TABLE `$tableName` (id INT AUTO_INCREMENT PRIMARY KEY, ";

        foreach ($columns as $col) {
            // Escape column names to avoid SQL injection and ensure validity
            $createTableSQL .= "`" . $conn->real_escape_string($col) . "` VARCHAR(255), ";
        }

        // Add the 'po_number' column to the table
        $createTableSQL .= "`po_number` VARCHAR(255));";

        // Execute table creation
        if ($conn->query($createTableSQL) === TRUE) {
            echo "Table $tableName created successfully.<br>";
        } else {
            die("Error creating table: " . $conn->error);
        }
    } else {
        // Check if the 'po_number' column exists, add it if not
        $columnExistsQuery = $conn->query("SHOW COLUMNS FROM `$tableName` LIKE 'po_number'");
        if ($columnExistsQuery->num_rows == 0) {
            $alterTableSQL = "ALTER TABLE `$tableName` ADD `po_number` VARCHAR(255)";
            if ($conn->query($alterTableSQL) !== TRUE) {
                die("Error adding 'po_number' column: " . $conn->error);
            }
        }
    }

    // Initialize variable to sum Total Amount for this specific PO number
    $totalAmountSum = 0;
    $totalAmountColumnIndex = array_search('Total Amount', $columns);

    // Insert data into the existing table, skipping the header row
    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        array_shift($row); // Remove the first column

        // Filter out any extra data if the number of columns in the row exceeds the column names
        $row = array_slice($row, 0, count($columns));

        // Skip empty rows (rows where all values are empty)
        $nonEmptyValues = array_filter($row, fn($value) => !empty($value));
        if (empty($nonEmptyValues)) {
            continue; // Skip this row if it contains only empty values
        }

        // Sum the Total Amount column for the current PO number
        if (isset($row[$totalAmountColumnIndex])) {
            $totalAmountSum += (float)$row[$totalAmountColumnIndex];
        }

        // Add the PO number to the row data
        $row[] = $poNumber;

        // Insert the data into the table
        $placeholders = rtrim(str_repeat('?,', count($row)), ',');
        $insertSQL = "INSERT INTO `$tableName` (" . implode(",", array_map(fn($col) => "`$col`", $columns)) . ", `po_number`) VALUES ($placeholders)";

        $stmt = $conn->prepare($insertSQL);
        $stmt->bind_param(str_repeat('s', count($row)), ...$row);

        if ($stmt->execute()) {
            // echo "Row $i inserted successfully.<br>";
        } else {
            echo "Error inserting row $i: " . $stmt->error . "<br>";
        }
    }

    // After inserting all rows, update rows with the same PO number
    $updateTotalSQL = "UPDATE `$tableName` SET `Total Amount` = ? WHERE `po_number` = ?";
    $stmt = $conn->prepare($updateTotalSQL);
    $stmt->bind_param("ds", $totalAmountSum, $poNumber); // Bind the total amount and PO number

    if ($stmt->execute()) {
        // echo "Total Amount updated for PO number: $poNumber with value: $totalAmountSum<br>";
        
session_start();

    header("Location:index.php");


    } else {
        echo "Error updating Total Amount: " . $stmt->error . "<br>";
    }

    $stmt->close();
}

$conn->close();
?>
