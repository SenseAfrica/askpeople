<?php
if (isset($_GET['mail'])){
	//for each of his accounts, give him a time-limited link to reset password
	include_once('../../db.php');
	if ((filter_var($_GET['mail'], FILTER_VALIDATE_EMAIL))&&($res=mysql_query('SELECT uid, realname, name FROM users, end_users WHERE email = "'.mysql_real_escape_string($_GET['mail']).'" AND org = id LIMIT 0,1'))){
		$accts=array();
		while ($line=mysql_fetch_assoc($res)) $accts[]=$line;
		if (!empty($accts)){
			include_once('../../mail.php');
			function make_seed() {
				list($usec, $sec) = explode(' ', microtime());
				return (float) $sec + ((float) $usec * 100000);
			}
			mt_srand(make_seed());
			$code = substr(mt_rand().'',0,8);
			if ($code==floor($code/10)*10) $code+=1;
			mysql_query ("INSERT INTO lost_pass (accounts,code) VALUES ('".json_encode($accts)."',$code)");
			$id=mysql_insert_id();
			$msg=
"<html>Greetings!
We have received a password-reset request for your e-mail ({$_GET['mail']}).<br/>
If you click on the following link (or load it into your browser) within three (3) hours from receiving this mail, your password will be reset.
<a href='https://askpeople.info/reset.php?req=$code&id=$id'>https://askpeople.info/reset.php?req=$code&id=$id</a>
</html>";
			HW_send ($_GET['mail'],"Password reset",$msg);
			exit('OK');
		}
	}
}
exit('KO');
?>