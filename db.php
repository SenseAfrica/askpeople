<?php
include_once('db_params.php');
mysql_connect($sql_details['host'],$sql_details['user'],$sql_details['pass']);
mysql_select_db($sql_details['db']);
?>