<?php
include_once('db.php');
@session_start();
if ((isset($_GET['id']))&&(isset($_SESSION['user']['org']))) {
	$tbl=(int)$_GET['id'];
	$name=mysqli_real_escape_string($_GET['name']);
	$res=mysqli_query("SELECT id FROM share_form  WHERE org = {$_SESSION['user']['org']} AND tbl = $tbl AND name ='$name' LIMIT 0,1");
	if (mysqli_num_rows($res)){
		$line=mysqli_fetch_assoc($res);
		echo ($line['id']);
		exit;
	}
	$res=mysqli_query("INSERT INTO share_form (org,tbl,name) VALUES ({$_SESSION['user']['org']},$tbl,'$name')");
	echo (mysqli_insert_id());
}
?>