<?php
$token='c8e21b624e2f4bb54a2ac9f12c7547f';
include_once('libs_orangeapis.php');
function good_num($number){
	return ((substr($number,0,1)=='+')&&(((int)substr($number,1))>0));
}
function sms($org,$phone,$msg,$dr=0,$todo=false){
	global $db_conn;
	file_put_contents('debug.txt',json_encode(array($org,$phone,$msg,$dr,$todo)));
	global $token;
	if (is_string($phone)) $phone=(int)preg_replace("/[^0-9]/", '', $phone);
	global $token;
	include_once('../db.php');
	if($dr){
		mysqli_query($db_conn,"INSERT INTO pendingsms (org,phone,msg".(($todo)?',todo':'').") VALUES ($org,$phone,'".mysqli_real_escape_string($db_conn,$msg)."'".(($todo)?(",'".mysqli_real_escape_string($db_conn,$todo)."'"):'').")");
		$dr=mysqli_insert_id($db_conn);
	}
	//$org=0 means SEND FOR FREE
	if ($org) mysqli_query($db_conn,"UPDATE end_users SET credits = credits - 10 WHERE id=$org AND (credits-reserve)>9");
	if ((!$org)||(mysqli_affected_rows($db_conn))){
		$res = sendSMS($phone, $msg, 'AskPeople', $dr, $token);
		return (floor((int)$res[0]/100)==2);	
	}
	return false;
}
function charge($phone,$price){
	global $token;
	if (is_string($phone)) $phone=preg_replace("/[^0-9]/", '', $phone);
	global $token;
	$res=chargeAmountUser($phone,$price,'XOF',$token);
	//echo (floor((int)$res[0]/100)==2)?'yes':$res[0].':'.$phone.$res[1];
	return (floor((int)$res[0]/100)==2);
}
?>