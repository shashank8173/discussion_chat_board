<?php
$servername="localhost";
$user="root";
$password="";
$db_name="bsm_dashboard";
$conn= mysqli_connect($servername,$user,$password, $db_name);
if($conn){
    echo("databaase connected");
}
else{
    echo("not connected");
}
$table="SELECT * FROM excel_data";
$check=mysqli_query($conn,$table);
$rowcount=mysqli_num_rows($check);

if($rowcount > 0){
//   print_r($data);

  
  while($data=mysqli_fetch_assoc($check)){
    if($data['HSN_Code']==38089191){
        echo "Noida"."<br>";
    }
    else if($data['HSN_Code']==33049990){
        echo "New Delhi"."<br>";
    }
    else if($data['HSN_Code']==34011190){
        echo "Gurugram"."<br>";
    }
    else{
        echo $data['HSN_Code']."<br>";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>
