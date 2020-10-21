<?php
if (isset($_GET['name'])){
	include_once ('db.php');
	$res=mysqli_query($db_conn,'SELECT id FROM end_users WHERE name = "'.mysqli_real_escape_string($db_conn,$_GET['name']).'" LIMIT 0,1');
	if (mysqli_num_rows($res)==0) exit('OK');
}
echo 'KO';
?>