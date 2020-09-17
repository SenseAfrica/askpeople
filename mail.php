<?php
function HW_send($to,$subj,$msg){
	$headers = 'MIME-Version: 1.0'."\r\n".'Content-type: text/html; charset=iso-8859-1'."\r\n".'From: AskPeople <noreply@askpeople.humanwireless.cm>' . "\r\n" .
		'X-Mailer: PHP/' . phpversion();
	return mail($to,$subj,$msg,$headers);
}
?>