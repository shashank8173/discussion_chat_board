<?php
if(isset($_GET["id"])){
    $id=$_GET["id"];
    include 'database/database.php';
$sql="DELETE FROM stack_available WHERE id=$id";
$conn->query($sql);
}
header("location: index.php");
exit;
?>