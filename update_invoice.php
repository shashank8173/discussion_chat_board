<?php
include 'database/database.php';
// Get the ID and new invoice value from the AJAX request
$id = $_POST['id'];
$invoice_value = $_POST['invoice_value'];

// Update the invoice value in the `scheduled` table
$updateQuery = "UPDATE scheduled SET invoice_value='$invoice_value' WHERE id=$id";
if ($conn->query($updateQuery) === TRUE) {
    echo "Invoice value updated successfully.";
} else {
    echo "Error updating invoice value: " . $conn->error;
}
$conn->close();
?>
