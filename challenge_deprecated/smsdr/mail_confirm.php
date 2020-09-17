<?php
include_once('../../mail.php');
function confirm($mail,$num,$title){
	HW_send($mail,'SMS delivery receipt: '.$num,"
Greetings!
Your form titled \"$title\" was successfully presented to the number $num via SMS.");
}
?>