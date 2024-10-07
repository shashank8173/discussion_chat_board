<?php
// Database connection settings
include 'database/database.php';
// Get the ID and status from the AJAX request
$id = $_POST['id'];
$status = $_POST['status'];
//Fetch the row data from scheduled table
$queryscheduled= "SELECT * FROM scheduled WHERE id = $id";
$resultscheduled = $conn->query($queryscheduled);
$rowscheduled = $resultscheduled->fetch_assoc();
if ($rowscheduled) {
    $po_number = $rowscheduled['po_number'];
    $account = $rowscheduled['account'];
    $po_date = $rowscheduled['po_date'];
    $fc_location = $rowscheduled['fc_location'];
    $po_expiry_date = $rowscheduled['po_expiry_date']; 
    $earliest_appt = $rowscheduled['earliest_appt'];    
    $po_values = $rowscheduled['po_values'];             
    $invoice_value = $rowscheduled['invoice_value'];   
    $turn_around_time = $rowscheduled['turn_around_time']; 
    $appointment = $rowscheduled['appointment'];   

    // Decide the table based on the status
    $target_table = '';
    if ($status == 'Delivered') {
        $target_table = 'delivered';
    }
    if ($target_table) {
        // Insert the row into the target table
        $insertQuery = "INSERT INTO $target_table (account, po_number, po_date, po_expiry_date, earliest_appt, fc_location, status, po_values, invoice_value, turn_around_time, appointment)
                        VALUES ('$account', '$po_number', '$po_date', '$po_expiry_date', '$earliest_appt', '$fc_location', '$status', '$po_values', '$invoice_value', '$turn_around_time', '$appointment')";
        if ($conn->query($insertQuery) === TRUE) {
            // Delete the row from `po_table`
            $deleteQuery = "DELETE FROM scheduled WHERE id = $id";
            if ($conn->query($deleteQuery) === TRUE) {
                echo "Status updated successfully, and row moved to $target_table.";
            } else {
                echo "Error deleting the row: " . $conn->error;
            }
        } else {
            echo "Error inserting into $target_table: " . $conn->error;
        }
    } else {
        echo "Invalid status selected.";
    }
} else {
    echo "No row found with the given ID.";
}
// Fetch the row data from `po_table`
$query = "SELECT * FROM po_table WHERE id = $id";
$result = $conn->query($query);
$row = $result->fetch_assoc();

if ($row) {
    $po_number = $row['po_number'];
    $account = $row['account'];
    $po_date = $row['po_date'];
    $fc_location = $row['fc_location'];
    $po_expiry_date = $row['po_expiry_date']; 
    $earliest_appt = $row['earliest_appt'];  
    $po_values = $row['po_values'];        
    $turn_around_time	= $row['turn_around_time']; 
    // $invoice_value = $row['invoice_value'];   
    $appointment = $row['appointment'];    

    // Decide the table based on the status
    $target_table = '';
    if ($status == 'Scheduled') {
        $target_table = 'scheduled';
    } elseif ($status == 'Delivered') {
        $target_table = 'delivered';
    }

    if ($target_table) {
        // Insert the row into the target table
        $insertQuery = "INSERT INTO $target_table (account, po_number, po_date, po_expiry_date, earliest_appt, fc_location, status, po_values,turn_around_time,appointment)
                        VALUES ('$account', '$po_number', '$po_date', '$po_expiry_date', '$earliest_appt', '$fc_location', '$status', '$po_values', '$turn_around_time','$appointment')";
        if ($conn->query($insertQuery) === TRUE) {
            // Delete the row from `po_table`
            $deleteQuery = "DELETE FROM po_table WHERE id = $id";
            if ($conn->query($deleteQuery) === TRUE) {
                echo "Status updated successfully, and row moved to $target_table.";
            } else {
                echo "Error deleting the row: " . $conn->error;
            }
        } else {
            echo "Error inserting into $target_table: " . $conn->error;
        }
    } else {
        echo "Invalid status selected.";
    }
} else {
    echo "No row found with the given ID.";
}

$conn->close();
?>
