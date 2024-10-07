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
 <title>BSM Dashboard Login</title>
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
//    if ($conn->connect_error) {
//        die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
//    }

   if(isset($_POST["login"])){
   $email=$_POST["email"];
  $password = $_POST["password"];
  // require_once "index.php";
  $sql = "Select * FROM users WHERE email='$email' ";
  $result =mysqli_query($conn,$sql);
  $user= mysqli_fetch_array($result,MYSQLI_ASSOC);
  if($user){
   if(password_verify($password,$user["password"])){
    session_start();
    $_SESSION["user"] = "yes";
    header("Location:index.php");
    die();
   }else{
    echo "<div class='alert alert-danger'>Password does not match</div>";
   }
  }else{
   echo "<div class='alert alert-danger'>Email does not match</div>" ;
  }
 }
  ?>
  <form action="" method="POST">
   <div class="form-group">
    <input type="email" placeholder="Enter Email" name="email" class="form-control">
   </div>
   <div class="form-group">
    <input type="password" placeholder="Enter Password" name="password" class="form-control">
   </div>
   <div class="form-group">
    <input type="submit" value="Login" name="login" class="btn btn-primary">
   </div>
  </form>
  <div><p>Not registered yet ? <a href="registration.php">Register here</a></p></div>
 </div>

</body>
</html>