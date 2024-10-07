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
       include 'database/database.php';
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
                        // Registration successful, redirect to index.php
                        header("Location: index.php");
                        exit();
                    } else {
                        echo "<div class='alert alert-danger'>Error: " . mysqli_stmt_error($stmt) . "</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>SQL preparation failed: " . mysqli_error($conn) . "</div>";
                }
            }
        }
        ?>
        
        <!-- Registration form -->
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
