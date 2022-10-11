<?php
require('../config/settings.inc.php');
$conn = mysqli_connect(_DB_SERVER_,_DB_USER_,_DB_PASSWD_,_DB_NAME_) or die("Connection failed: ");
echo _DB_SERVER_."|"._DB_USER_."|"._DB_PASSWD_."|"._DB_NAME_."<br/>\n"; 
$sql = "SELECT * FROM "._DB_PREFIX_."orders where date_add  > STR_TO_DATE('2019-08-01','%Y-%m-%d')";
$result = $conn->query($sql) or die("Not query");
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
	$id_order = $row["id_order"];
	$payment = $row["payment"];
	$modu = $row["module"];
	$total_paid = $row["total_paid"];
	$date_add = $row["date_add"];
echo $id_order." | ".$total_paid." | ".$payment." | ".$modu." | ".$date_add."<br>";

    }    
} 
else 
{
    echo "0 results";
}
$conn->close();
?>