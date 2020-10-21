<?php
if (isset($_GET['name'])){
	include_once ('db.php');
	$res=mysqli_query('SELECT id FROM end_users WHERE name = "'.mysqli_real_escape_string($_GET['name']).'" LIMIT 0,1');
	if (mysqli_num_rows($res)==0) exit('OK');
}
echo 'KO';
?>