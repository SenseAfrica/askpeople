<?php
if ((isset($_GET['mail']))&&(filter_var($_GET['mail'],FILTER_VALIDATE_EMAIL))){
	include_once ('db.php');
	$res=mysql_query('SELECT uid FROM users WHERE email = "'.mysql_real_escape_string($_GET['mail']).'" LIMIT 0,1');
	if (mysql_num_rows($res)==0) exit('OK');
}
exit ('KO');
?>