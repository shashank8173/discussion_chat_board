<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <link rel="icon" type="image/png" href="./public/fev-icon.png" sizes="16x16">
   <title>EduKrita.smartskill</title>
   <?php include('./client/commonFiles.php'); ?>
</head>

<body>
   <?php
   include('./client/header.php');

   if (isset($_GET['signup']) && !isset($_SESSION['user']['username'])) {
      include('./client/signup.php');
   } elseif (isset($_GET['login']) && !isset($_SESSION['user']['username'])) {
      include('./client/login.php');
   } elseif (isset($_GET['ask'])) {
      include('./client/ask.php');
   } elseif (isset($_GET['q-id'])) {
      $qid = $_GET['q-id'];
      include('./client/question-details.php');
   } elseif (isset($_GET['c-id'])) {
      $cid = $_GET['c-id'];
      include('./client/questions.php');
   } elseif (isset($_GET['u-id'])) {
      $uid = $_GET['u-id'];
      include('./client/questions.php');
   } elseif (isset($_GET['latest'])) {
      include('./client/questions.php');
   } elseif (isset($_GET['search'])) {
      $search = $_GET['search'];
      include('./client/questions.php');
   } else {
      include('./client/questions.php');
   }
   ?>
</body>

</html>

</html>