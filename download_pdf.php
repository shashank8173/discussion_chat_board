<?php
require('fpdf/fpdf.php'); // Load FPDF

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bsm_dashboard";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get the data (including Un_PO_No)
$sql = "SELECT `Un_PO_No`, `Item_Code`, `Product_UPC`, `Product_Description`, `Quantity`, `MRP`, `Total_Amount` FROM excel_table";
$result = $conn->query($sql);

// Check if there is data
if ($result->num_rows > 0) {
    // Initialize FPDF
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Set title
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(190, 10, 'Product Data Report', 0, 1, 'C');
    
    // Set table headers (including Un_PO_No)
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(30, 10, 'Un PO No', 1);
    $pdf->Cell(30, 10, 'Item Code', 1);
    $pdf->Cell(40, 10, 'Product UPC', 1);
    $pdf->Cell(60, 10, 'Description', 1);
    $pdf->Cell(20, 10, 'Qty', 1);
    $pdf->Cell(20, 10, 'MRP', 1);
    $pdf->Cell(20, 10, 'Total', 1);
    $pdf->Ln();

    // Set table rows
    $pdf->SetFont('Arial', '', 12);
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(30, 10, $row['Un_PO_No'], 1);
        $pdf->Cell(30, 10, $row['Item_Code'], 1);
        $pdf->Cell(40, 10, $row['Product_UPC'], 1);
        $pdf->Cell(60, 10, $row['Product_Description'], 1);
        $pdf->Cell(20, 10, $row['Quantity'], 1);
        $pdf->Cell(20, 10, $row['MRP'], 1);
        $pdf->Cell(20, 10, $row['Total_Amount'], 1);
        $pdf->Ln();
    }

    // Output the PDF to the browser for download
    $pdf->Output('D', 'product_data.pdf');
} else {
    echo "No data found.";
}

$conn->close();
?>
