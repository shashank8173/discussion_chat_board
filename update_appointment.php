<?php
include 'database/database.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $appointment = $_POST['appointment']; 

    // Update the `appointment` column in the `po_table`
    $updateQuery = "UPDATE po_table SET appointment = '$appointment' WHERE id = $id";
    
    if ($conn->query($updateQuery) === TRUE) {
        echo "Appointment updated successfully.";
    } else {
        echo "Error updating appointment: " . $conn->error;
    }
}

$conn->close();
?>
