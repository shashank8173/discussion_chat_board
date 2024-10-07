<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location:login.php");
    exit(); 
}
require 'vendor/autoload.php'; 
use PhpOffice\PhpSpreadsheet\IOFactory;
$host = 'localhost';
$dbname = 'bsm_dashboard';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 1: Fetch unique po_numbers from `excel_table` that don't exist in `po_table`
$fetchUniqueQuery = "
    SELECT e.po_number
    FROM excel_table e
    LEFT JOIN po_table p ON e.po_number = p.po_number
    WHERE p.po_number IS NULL
    GROUP BY e.po_number
";
$result = $conn->query($fetchUniqueQuery);

if ($result->num_rows > 0) {
    while ($uniqueRow = $result->fetch_assoc()) {
        $po_number = $uniqueRow['po_number'];

        // Step 2: Fetch all items related to this po_number from `excel_table`
        $fetchItemsQuery = "
            SELECT `Item Code`, `Quantity`, `MRP`, `Margin %`, `Total Amount`
            FROM excel_table
            WHERE po_number = ?
        ";
        $stmtItems = $conn->prepare($fetchItemsQuery);
        $stmtItems->bind_param("s", $po_number);
        $stmtItems->execute();
        $itemsResult = $stmtItems->get_result();

        $fill_rate_stack = 0;
        $totalAmount = 0;
        $all_items_processed = true;

        // Process each item for the current po_number
        while ($itemRow = $itemsResult->fetch_assoc()) {
            $itemcode = $itemRow['Item Code'];
            $ordered_quantity = $itemRow['Quantity'];
            $mrp = $itemRow['MRP'];
            $margin = $itemRow['Margin %'];
            $totalAmount = $itemRow['Total Amount'];

            // Fetch available quantity from `stack_available`
            $availableQuery = "SELECT quantity, mrp, margin FROM stack_available WHERE itemcode = ?";
            $stmtAvailable = $conn->prepare($availableQuery);
            $stmtAvailable->bind_param("s", $itemcode);
            $stmtAvailable->execute();
            $availableResult = $stmtAvailable->get_result();

            if ($availableResult->num_rows > 0) {
                while ($availableRow = $availableResult->fetch_assoc()) {
                    $available_quantity = $availableRow['quantity'];
                    $fixed_mrp = $availableRow['mrp'];
                    $fixed_margin = $availableRow['margin'];

                    if ($available_quantity > 0) {
                        // Calculate the PO value for available quantity
                        $po_value = $available_quantity * $fixed_mrp * (1 - ($fixed_margin / 100));
                        $fill_rate_stack += $po_value;
                    } else {
                        $all_items_processed = false; // Set flag if any item has zero quantity
                    }

                    // Update available quantity in `stack_available`
                    $new_quantity = max(0, $available_quantity - $ordered_quantity);
                    $updateAvailableQuery = "UPDATE stack_available SET quantity = ? WHERE itemcode = ?";
                    $stmtUpdate = $conn->prepare($updateAvailableQuery);
                    $stmtUpdate->bind_param("is", $new_quantity, $itemcode);
                    $stmtUpdate->execute();
                    $stmtUpdate->close();
                }
            } else {
                // No available stock for this itemcode
                $all_items_processed = false;
            }
            $stmtAvailable->close();
        }
        $stmtItems->close();

        // Determine the fc_location based on the first four digits of po_number
        $firstFourDigits = substr($po_number, 0, 4);
        $location = '';
        switch ($firstFourDigits) {
            case '1256':
                $location = 'Farukhnagar';
                break;
            case '1177':
                $location = 'Dasna2';
                break;
            case '1336':
                $location = 'Gurgaon G7';
                break;
            case '2575':
                $location = 'Dasna3';
                break;
            case '2866':
                $location = 'Noida N1';
                break;
            default:
                $location = 'Unknown';
        }

        // Assign variables for insertion
        $updatestack_fill_rate = $fill_rate_stack;
        $updated_totalAmount = $totalAmount;
        $updated_location = $location;

        // Calculate fill rate with safeguard
        if ($updatestack_fill_rate > 0) {
            $fill_rate = ($updated_totalAmount / $updatestack_fill_rate) * 100;
        } else {
            $fill_rate = 0;
            echo "Warning: Fill rate stack is zero for PO Number: $po_number. Fill rate set to 0.<br>";
        }

        // echo "PO Number: " . htmlspecialchars($po_number) . "<br>";
        // echo "Fill Rate: " . number_format($fill_rate, 2) . "%<br>";
        // echo "Total Amount: " . htmlspecialchars($updated_totalAmount) . "<br>";

        $account = (strlen($po_number) == 13) ? 'Blinkit' : '';

        $insertQuery = "
            INSERT INTO po_table 
                (account, po_number, po_date, po_expiry_date, earliest_appt, fc_location, status, po_values, fill_rate, suggested_appt_date, appointment) 
            VALUES 
                (?, ?, CURDATE(), NULL, NULL, ?, NULL, ?, ?, NULL, NULL)
        ";
        $stmtInsert = $conn->prepare($insertQuery);
        $stmtInsert->bind_param("sssdi", $account, $po_number, $updated_location, $updated_totalAmount, $fill_rate);

        if ($stmtInsert->execute()) {
            echo "Data inserted successfully for PO Number: $po_number with fill rate: " . number_format($fill_rate, 2) . "%<br><br>";
        } else {
            echo "Error inserting data for PO Number: $po_number: " . $stmtInsert->error . "<br><br>";
        }
        $stmtInsert->close();
    }
} else {
    echo "No new records found in `excel_table`.<br>";
}

$sql_items = "SELECT * FROM stack_available";
$result_items = $conn->query($sql_items);

$sql_updated = "SELECT id, account, po_number, po_date, po_expiry_date, earliest_appt, fc_location, status, po_values, fill_rate, suggested_appt_date, appointment FROM po_table";
$result_updated = $conn->query($sql_updated);

$sql_scheduled = "SELECT id, account, po_number, po_date, po_expiry_date, earliest_appt, fc_location, status, po_values, invoice_value, turn_around_time, appointment FROM scheduled";
$result_scheduled = $conn->query($sql_scheduled);

$sql_delivered = "SELECT id, account, po_number, po_date, po_expiry_date, earliest_appt, fc_location, status, po_values, invoice_value, turn_around_time, appointment FROM delivered";
$result_delivered = $conn->query($sql_delivered);

$sql_username = "SELECT fullname FROM users";
$result_username = $conn->query($sql_username);

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>BMS Dashboard</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body>
    <div class="dashboard">
        <div class="sidebar">
            <div class="logo">
                <img src="assets/images/image.png" alt="log">
            </div>
            <div class="left-items">

                <ul>
                    <li data-content="home" class="active">PO Summary</li>
                    <li data-content="profile">Inventory</li>
                    <li data-content="settings">Reports</li>
                    <li data-content="logout">Description</li>
                </ul>
                <a href="logout.php" class="logoutbutton">Logout</a>
            </div>
        </div>
        <div class="content">
            <div class="containers">
                <div class="box">
                    <h5>Total PO Received (MTD)</h5>
                </div>
                <div class="box">
                    <h5>Total PO Delivered (MTD)</h5>
                </div>
                <div class="box">
                    <h5>Total PO Pending (MTD)</h5>
                </div>
                <div class="box">
                    <h5>Fill Rate %</h5>
                </div>
            </div>
            <div id="home" class="content-page active">
                <div class="items-row">
                    <div class="itembsm active" id="itembsm1">Pending <br>
                        <?php

                        if ($result_updated->num_rows > 0) {
                            $row_count = $result_updated->num_rows;
                            echo $row_count;
                        } else {
                            echo "NA";
                        }
                        ?>
                    </div>
                    <div class="itembsm" id="itembsm2">Scheduled <br>
                        <?php

                        if ($result_scheduled->num_rows > 0) {
                            $row_count = $result_scheduled->num_rows;
                            echo $row_count;
                        } else {
                            echo "NA";
                        }
                        ?>
                    </div>
                    <div class="itembsm" id="itembsm3">Delivered <br>
                        <?php

                        if ($result_delivered->num_rows > 0) {
                            $row_count = $result_delivered->num_rows;
                            echo $row_count;
                        } else {
                            echo "NA";
                        }
                        ?>
                    </div>
                    <div class="uploades">
                        <form action="upload.php" method="POST" enctype="multipart/form-data">
                            <input type="file" name="excelFile" accept=".xls, .xlsx" required>
                            <button type="submit" class="custom-upload">+ Upload New</button>
                        </form>
                        <br><br>
                        <form action="download_excel.php" method="POST">
                            <button class="btn" type="submit">Download</button>
                        </form>
                    </div>
                </div>
                <div class="content-area" id="content-area">
                    <div class="contentbsm" id="contentbsm1">
                        <div class="tables-home">
                            <div class="table-container">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Account</th>
                                            <th>PO Number</th>
                                            <th>PO Date</th>
                                            <th>PO Expiry Date</th>
                                            <th>Earliest Appt</th>
                                            <th>FC Location</th>
                                            <th>Status</th>
                                            <th>PO Values</th>
                                            <th>Fill Rate(%)</th>
                                            <th>Suggested Appt Date</th>
                                            <th>Appointment</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        
                                        if ($result_updated->num_rows > 0) {
                                            while ($row = $result_updated->fetch_assoc()) {
                                                echo "<tr>
                                                        <td>" . htmlspecialchars($row["id"]) . "</td>
                                                        <td>" . htmlspecialchars($row["account"]) . "</td>
                                                        <td>" . htmlspecialchars($row["po_number"]) . "</td>
                                                        <td>" . htmlspecialchars($row["po_date"]) . "</td>
                                                        <td>" . htmlspecialchars($row["po_expiry_date"]) . "</td>
                                                        <td>" . htmlspecialchars($row["earliest_appt"]) . "</td>
                                                        <td>" . htmlspecialchars($row["fc_location"]) . "</td>
                                                         <td>
                        <select onchange='updateStatus(" . $row["id"] . ", this.value)'>
                            <option value='Pending'" . ($row['status'] == 'Pending' ? ' selected' : '') . ">Pending</option>
                            <option value='Scheduled'" . ($row['status'] == 'Scheduled' ? ' selected' : '') . ">Scheduled</option>
                            <option value='Delivered'" . ($row['status'] == 'Delivered' ? ' selected' : '') . ">Delivered</option>
                        </select>
                    </td>
                                                        <td>₹" . htmlspecialchars($row["po_values"]) . "</td>
                                                        <td>" . htmlspecialchars($row["fill_rate"]) . "</td>
                                                        <td>" . htmlspecialchars($row["suggested_appt_date"]) . "</td>
                                                       <td>
                        <input type='date' value='" . htmlspecialchars($row["appointment"]) . "' 
                               onchange='updateAppointment(" . $row["id"] . ", this.value)' />
                    </td>
                                                      </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='12'>No records found</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="gapbtn2vw"></div>
                        <div class="lowerbutton">
                            <button class="btn">Save</button>
                            <button class="btn">Run Appointment System</button>
                        </div>
                    </div>
                    <div class="contentbsm" id="contentbsm2">
                        <div class="tables-home">
                            <div class="table-container">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Account</th>
                                            <th>PO Number</th>
                                            <th>PO Date</th>
                                            <th>PO Expiry Date</th>
                                            <th>Earliest Appointment</th>
                                            <th>FC Location</th>
                                            <th>Status</th>
                                            <th>PO Value</th>
                                            <th>Invoice Value</th>
                                            <th>Turn Around Time(IN DAYS)</th>
                                            <th>Appointment</th>
                                        </tr>
                                    </thead>
                                    <?php
                                    
                                    if ($result_scheduled->num_rows > 0) {
                                        while ($row = $result_scheduled->fetch_assoc()) {
                                            echo "<tr>
                        <td>" . $row["id"] . "</td>
                        <td>" . $row["account"] . "</td>
                        <td>" . $row["po_number"] . "</td>
                        <td>" . $row["po_date"] . "</td>
                        <td>" . $row["po_expiry_date"] . "</td>
                        <td>" . $row["earliest_appt"] . "</td>
                        <td>" . $row["fc_location"] . "</td>
                         <td>
                        <select onchange='updateStatus(" . $row["id"] . ", this.value)'>
                            
                            <option value='Scheduled'" . ($row['status'] == 'Scheduled' ? ' selected' : '') . ">Scheduled</option>
                            <option value='Delivered'" . ($row['status'] == 'Delivered' ? ' selected' : '') . ">Delivered</option>
                        </select>
                    </td>
                        <td>₹" . $row["po_values"] . "</td>
                         <td>
                                <input type='text' value='" . $row["invoice_value"] . "' onkeydown='updateInvoiceValue(event, " . $row["id"] . ", this.value)' />
                            </td>
                        <td>" . $row["turn_around_time"] . "</td>
                        <td>" . $row["appointment"] . "</td>
                      </tr>";
                                        }
                                    } else {
                                        // echo "<tr><td colspan='11'>No records found</td></tr>";
                                    }
                                    ?>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="contentbsm" id="contentbsm3">
                        <div class="tables-home">
                            <div class="table-container">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Account</th>
                                            <th>PO Number</th>
                                            <th>PO Date</th>
                                            <th>PO Expiry Date</th>
                                            <th>Earliest Appointment</th>
                                            <th>FC Location</th>
                                            <th>Status</th>
                                            <th>PO Value</th>
                                            <th>Invoice Value</th>
                                            <th>Turn Around Time(IN DAYS)</th>
                                            <th>Appointment</th>
                                        </tr>
                                    </thead>
                                    <?php
                                    
                                    if ($result_delivered->num_rows > 0) {
                                        while ($row = $result_delivered->fetch_assoc()) {
                                            echo "<tr>
                        <td>" . $row["id"] . "</td>
                        <td>" . $row["account"] . "</td>
                        <td>" . $row["po_number"] . "</td>
                        <td>" . $row["po_date"] . "</td>
                        <td>" . $row["po_expiry_date"] . "</td>
                        <td>" . $row["earliest_appt"] . "</td>
                        <td>" . $row["fc_location"] . "</td>
                         <td>" . $row["status"] . "</td>
                        <td>₹" . $row["po_values"] . "</td>
                        <td>₹" . $row["invoice_value"] . "</td>
                        <td>" . $row["turn_around_time"] . "</td>
                        <td>" . $row["appointment"] . "</td>
                      </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='11'>No records found</td></tr>";
                                    }
                                    ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="profile" class="content-page">
                <div class="container my-5">
                    <h2>Available Product In Inventory</h2>
                   

                    <div class="uploaditesms">
                     <div><a class="btn btn-primary" href="create.php" role="button">Add New Product</a></div> 
                     <div><form action="uploaditems.php" method="post" enctype="multipart/form-data">
                            <input type="file" name="excel_file" accept=".xlsx, .xls">
                            <input type="submit" value="Upload New Item Sheet">
                        </form></div>
                    </div>
                    <br>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Item Code</th>
                                <th>Product Description</th>
                                <th>Quantity</th>
                                <th>MRP</th>
                                <th>Margin(%)</th>
                                <th>Operations</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result_items->num_rows > 0) {
                                while ($row = $result_items->fetch_assoc()) {
                                    echo "
                         <tr>
                            <td>{$row['id']}</td>
                            <td>{$row['itemcode']}</td>
                            <td>{$row['product_description']}</td>
                            <td>{$row['quantity']}</td>
                            <td>{$row['mrp']}</td>
                            <td>{$row['margin']}</td>

                            <td>
                                <a class='btn btn-primary btn-sm' href='edit.php?id={$row['id']}'>Update</a>
                                <a class='btn btn-danger btn-sm' href='delete.php?id={$row['id']}' onclick=\"return confirm('Are you sure you want to delete this item?');\">Delete</a>
                            </td>
                         </tr>
                         ";
                                }
                            }
                            ?>

                        </tbody>
                    </table>
                </div>
            </div>
            <div id="settings" class="content-page">
                <h1>Reports</h1>
            </div>
            <div id="logout" class="content-page">
                <h1>Logout</h1>
                <p>You have been logged out.</p>
            </div>
        </div>
    </div>
    <script src="assets/js/scripts.js"></script>
    <script>
        function updateInvoiceValue(event, id, invoiceValue) {
            if (event.keyCode === 13) { 
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "update_invoice.php", true);  
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        var response = xhr.responseText;
                       
                        document.getElementById("invoice-value-" + id).innerText = invoiceValue; 
                    }
                };
                xhr.send("id=" + id + "&invoice_value=" + invoiceValue);  
            }
        }


        function updateStatus(id, newStatus) {
            $.ajax({
                url: 'update_status.php',  
                type: 'POST',
                data: {
                    id: id,
                    status: newStatus
                },
                success: function (response) {
                    alert(response);  
                    location.reload(); 
                },
                error: function () {
                    alert("An error occurred while updating the status.");
                }
            });
        }
    </script>
</body>

</html>