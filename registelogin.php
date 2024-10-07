<?php
session_start();
if(isset($_SESSION["user"])){
    header("Location:index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BSM Dashboard Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            padding: 50px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 50px;
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 9px;
        }
        .form-group {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        $host = 'localhost';
        $dbname = 'bsm_dashboard';
        $username = 'root';
        $password = '';
        $conn = new mysqli($host, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
        }

        if (isset($_POST["submit"])) {
            $fullname = $_POST["fullname"];
            $email = $_POST["email"];
            $password = $_POST["password"];
            $repeat_password = $_POST["repeat_password"];
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $errors = array();
            if (empty($fullname) || empty($email) || empty($password) || empty($repeat_password)) {
                array_push($errors, "All fields are required.");
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                array_push($errors, "Email is not valid.");
            }
            if (strlen($password) < 8) {
                array_push($errors, "Password must be at least 8 characters long.");
            }
            if ($password !== $repeat_password) {
                array_push($errors, "Passwords do not match.");
            }
             $sql="SELECT * FROM users WHERE email='$email'";
             $result=mysqli_query($conn,$sql) ;
             $rowCount=mysqli_num_rows($result);
             if($rowCount>0){
              array_push($errors, "Email already exists.");
             }
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    echo "<div class='alert alert-danger'>$error</div>";
                }
            } else {
                
                $sql = "INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)";
                $stmt = mysqli_stmt_init($conn);

               
                if (mysqli_stmt_prepare($stmt, $sql)) {
                    mysqli_stmt_bind_param($stmt, "sss", $fullname, $email, $passwordHash);
                    if (mysqli_stmt_execute($stmt)) {
                        echo "<div class='alert alert-success'>You are registered successfully.</div>";
                    } else {
                        echo "<div class='alert alert-danger'>Error: " . mysqli_stmt_error($stmt) . "</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>SQL preparation failed: " . mysqli_error($conn) . "</div>";
                }
            }
        }
        ?>
        
       
        <form action="" method="POST">
            <div class="form-group">
                <input type="text" class="form-control" name="fullname" placeholder="Full Name:">
            </div>
            <div class="form-group">
                <input type="email" class="form-control" name="email" placeholder="Email:">
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="password" placeholder="Password">
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="repeat_password" placeholder="Repeat Password">
            </div>
            <div class="form-btn">
                <input type="submit" class="btn btn-primary" value="Register" name="submit">
            </div>
        </form>
        <div><p>Already Registered<a href="login.php">Login here</a></p></div>
    </div>
</body>
</html>


<!-- <h1>
                <?php
       
        if ($result_username->num_rows > 0) {
            echo "<ul class='list-group'>";
            while($row = $result_username->fetch_assoc()) {
                echo "<li class='list-group-item'>" . htmlspecialchars($row['fullname']) . "</li>";
            }
            
            echo "</ul>";
        } else {
            echo "<p>No users found.</p>";
        }
        ?>
                </h1> -->






                <?php 
session_start();
if (!isset($_SESSION["user"])) {
    header("Location:login.php");
}

require 'vendor/autoload.php'; // Load PhpSpreadsheet using Composer
use PhpOffice\PhpSpreadsheet\IOFactory;

// Database connection settings
$host = 'localhost';
$dbname = 'bsm_dashboard';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 1: Fetch unique data from `excel_table` that doesn't exist in `po_table`
$fetchUniqueQuery = "
    SELECT e.*
    FROM excel_table e
    LEFT JOIN po_table p ON e.po_number = p.po_number
    WHERE p.po_number IS NULL
";
$result = $conn->query($fetchUniqueQuery);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $po_number = $row['po_number']; 
        $itemcode = $row['itemcode'];  // Fetch itemcode from `excel_table`
        $ordered_quantity = $row['Quantity'];  // Fetch ordered quantity
        $totalAmount = $row['Total Amount']; 

        // Step 2: Check available quantity in `stack_available`
        $availableQuery = "SELECT quantity FROM stack_available WHERE itemcode = '$itemcode'";
        $availableResult = $conn->query($availableQuery);
        $fill_rate = 0;  // Default fill rate

        if ($availableResult->num_rows > 0) {
            $availableRow = $availableResult->fetch_assoc();
            $available_quantity = $availableRow['quantity'];

            // Step 3: Calculate fill rate
            if ($available_quantity >= $ordered_quantity) {
                $fill_rate = 100;
            } else {
                $fill_rate = ($available_quantity / $ordered_quantity) * 100;
            }

            // Update available quantity in `stack_available`
            $new_quantity = max(0, $available_quantity - $ordered_quantity);
            $updateAvailableQuery = "UPDATE stack_available SET quantity = $new_quantity WHERE itemcode = '$itemcode'";
            $conn->query($updateAvailableQuery);
        }

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
        }

        // Insert data into `po_table` with fill rate
        $account = (strlen($po_number) == 13) ? 'Blinkit' : '';
        $insertQuery = "
            INSERT INTO po_table (account, po_number, po_date, po_expiry_date, earliest_appt, fc_location, status, po_values, fill_rate, suggested_appt_date, appointment)
            VALUES ('$account', '$po_number', CURDATE(), NULL, NULL, '$location', NULL, '$totalAmount', $fill_rate, NULL, NULL)
        ";

        if ($conn->query($insertQuery) === FALSE) {
            echo "Error inserting data: " . $conn->error;
        }
    }
} else {
    // echo "No new records found in `excel_table`.";
}

// Fetch data from `po_table` for display
$sql_updated = "SELECT id, account, po_number, po_date, po_expiry_date, earliest_appt, fc_location, status, po_values, fill_rate, suggested_appt_date, appointment FROM po_table";
$result_updated = $conn->query($sql_updated);

// (Other queries for scheduled and delivered)

$conn->close();
?>
