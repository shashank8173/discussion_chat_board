<?php
require 'vendor/autoload.php'; 
use PhpOffice\PhpSpreadsheet\IOFactory;

$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "bsm_dashboard"; 


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_FILES['excel_file']['name']) {
    $fileName = $_FILES['excel_file']['tmp_name'];

    
    $spreadsheet = IOFactory::load($fileName);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray();

    // Get the header row (first row) to identify 'Item Code' and 'Quantity' columns
    $headers = $data[0]; // Assuming the first row contains the headers
    $itemCodeIndex = null;
    $quantityIndex = null;

    // Search for 'Item Code' and 'Quantity' columns
    foreach ($headers as $key => $header) {
        if (stripos($header, 'Item Code') !== false) {
            $itemCodeIndex = $key;
        }
        if (stripos($header, 'Quantity') !== false) {
            $quantityIndex = $key;
        }
    }

    if ($itemCodeIndex !== null && $quantityIndex !== null) {
        
        for ($i = 1; $i < count($data); $i++) {
            $row = $data[$i];
            $item_code = isset($row[$itemCodeIndex]) ? $row[$itemCodeIndex] : null;
            $quantity = isset($row[$quantityIndex]) ? $row[$quantityIndex] : null;

            // Skip rows where either Item Code or Quantity is empty
            if (!empty($item_code) && !empty($quantity)) {
                // Query to check if the item_code exists in stack_available table
                $checkQuery = "SELECT quantity FROM stack_available WHERE itemcode = ?";
                $stmt = $conn->prepare($checkQuery);
                $stmt->bind_param("s", $item_code);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    // Fetch the current quantity
                    $stmt->bind_result($current_quantity);
                    $stmt->fetch();

                    // Calculate the updated quantity
                    $new_quantity = $current_quantity + $quantity;

                    // Update query to set new quantity
                    $updateQuery = "UPDATE stack_available SET quantity = ? WHERE itemcode = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("is", $new_quantity, $item_code);
                    $updateStmt->execute();
                }

                $stmt->close();
            }
        }

        echo "Quantity updated successfully.";
    } else {
        echo "'Item Code' or 'Quantity' column not found in the Excel sheet.";
    }
} else {
    echo "Please upload an Excel file.";
}

$conn->close();
?>
