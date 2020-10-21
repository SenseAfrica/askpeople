<?php
include('head.php');
echo '<html>';
echo '<body>';
$res=mysqli_query($db_conn,"SELECT id,org,form,step,birth,gender,ed,job,kids FROM visitors WHERE phone = $msisdn");
if (mysqli_num_rows($res)){
	$line=mysqli_fetch_assoc($res);
	//Define this function for later use!
	function reserve_credit($org,$form,$undo=false){
		global $line;
		include_once('../../db.php');
		$res=mysqli_query($db_conn,"SELECT fieldset FROM forms_$org WHERE id=$form LIMIT 0,1");
		if(($res)&&($line=mysqli_fetch_assoc($res))){
			$needed=10*substr_count($line['fieldset'],'</li>');
			if($undo) {
				mysqli_query($db_conn,"DELETE FROM reservations WHERE org=$org AND form=$form AND visitor={$line['id']}");
				if(mysqli_affected_rows($db_conn)) mysqli_query($db_conn,"UPDATE end_users SET reserve = reserve-$needed WHERE id=org");
			} else {
				mysqli_query($db_conn,"INSERT INTO reservations VALUES ($org,$form,{$line['id']})");
				if(mysqli_affected_rows($db_conn)) mysqli_query($db_conn,"UPDATE end_users SET reserve = reserve+$needed WHERE id=org AND credits>=(reserve + $needed)");
			}
			return mysqli_affected_rows($db_conn);
		}
		return false;
	}
	if(isset($_GET['response'])){
		$in=explode('.',$_GET['response']);
		$org=(int)$in[0];
		$form=(int)$in[1];
		if (isset($in[2])) $secret=(int)$in[2];
		$res=mysqli_query($db_conn,"SELECT forms_$org.name AS form, end_users.name AS org FROM forms_$org, end_users WHERE forms_$org.id =$form AND ".((isset($secret))?"((UNIX_TIMESTAMP(created) % 1000)+{$line['id']})=$secret":'public=1')." AND end_users.id=$org LIMIT 0,1");
		if($ln=mysqli_fetch_assoc($res)){
			$res=mysqli_query($db_conn,"SELECT id FROM submissions_$org WHERE form=$form AND agent={$line['id']} LIMIT 0,1");
			if(mysqli_num_rows($res)){
				echo 'You have already answred this form.<br/>';
				echo '--<br/>';
			}else{
				if($line['form']) reserve_credit($line['org'],$line['form'],true);
				mysqli_query($db_conn,"UPDATE visitors SET org=$org, form=$form, step=0 WHERE id={$line['id']}");
				echo 'Coming up: "'.$ln['form'].'" by "'.$ln['org'].'"!<br/>';
				echo '<a href="forget.php" accesskey="99">skip</a><br/>';
				echo '<a href="survey.php">start survey</a><br/>';
				echo '</body>';
				echo '</html>';
				exit;
			}
		} else {
			echo 'Form does not exist or is private.<br/>';
			echo '--<br/>';
		}
	}
	if(($line['form']==0)&&($line['step']<5)){
		echo 'Welcome back!<br/>';
		echo 'You were creating your account.<br/>';
		echo '<a href="survey.php">continue</a><br/>';
		echo '<a href="info.php" accesskey="9">more info</a><br/>';
	} else if (($line['form'])&&(!isset($HW_forget))){
		$res=mysqli_query($db_conn,"SELECT forms_{$line['org']}.name AS form, end_users.name AS org FROM forms_{$line['org']}, end_users WHERE forms_{$line['org']}.id = {$line['form']} AND end_users.id = {$line['org']} LIMIT 0,1");
		$line2=mysqli_fetch_assoc($res);
		echo 'Welcome back!<br/>';
		echo 'You were answering the survey "'.$line2['form'].'" by "'.$line2['org'].'".<br/>';
		echo 'Do you wish to continue?<br/>';
		echo '<a href="survey.php">continue</a><br/>';
		echo '<a href="forget.php" accesskey="99">cancel</a><br/>';
	} else {
		if (($line['form'])&&(isset($HW_forget))) {
			mysqli_query($db_conn,"UPDATE visitors SET org = 0, form = 0, step = 5 WHERE id = {$line['id']}");
			reserve_credit($line['org'],$line['form'],true);
		}
		if (file_exists('tmp/'.$line['id'])) unlink('tmp/'.$line['id']);
		$cache_file='cache/'.$line['birth'].$line['ed'].$line['job'].$line['kids'];
		$cache_time=3600;
		if ((!file_exists($cache_file))||(time() - filemtime($cache_file) >= $cache_time)){
			$age=(int)date('Y')-$line['birth'];
			$res=mysqli_query($db_conn,"SELECT id FROM profiles WHERE min_age <= $age AND max_age >= $age AND ((gender = {$line['gender']}) OR (gender IS NULL)) AND ed_{$line['ed']} = 1 AND job_{$line['job']} = 1 AND kids_{$line['kids']} = 1");
			$arr=array();
			while ($line2=mysqli_fetch_assoc($res)) $arr[]=$line2['id'];
			file_put_contents($cache_file,json_encode($arr));
		} else $arr=json_decode(file_get_contents($cache_file));
			
		$banned=array();
		if($line['form']) $banned[]=$line['form'];
		if(!empty($arr)){
			$lim=10;
			for($i=0;$i<$lim;$i++){
				//A-pick a random profile matching our visitor
				$profile=$arr[array_rand($arr)];
					//debug echo "profile:$profile";
				//B-pick a random valid market-node targetting that profile
				$offset_row = mysqli_fetch_object(mysqli_query($db_conn,'SELECT COUNT(*) AS num, FLOOR(RAND() * COUNT(*)) AS `offset` FROM `profiling` WHERE profile = '.$profile));
				$tmp = $offset_row->num;
				if ($tmp==0){
					$i=$lim;
					break;//we havent found any
				}
				$tmp = $offset_row->offset;
				$res = mysqli_query($db_conn,"SELECT org, node FROM `profiling`  WHERE profile= $profile LIMIT $tmp, 1");
				$line2=mysqli_fetch_assoc($res);
					//debug echo "node:{$line2['org']}-{$line2['node']}";
				//C-pick a random survey running in that node
				$res=mysqli_query($db_conn,"SELECT id AS form FROM forms_{$line2['org']} WHERE node={$line2['node']} AND active=1 ".(((empty($banned))||(rand(1,10)<7))?'':'AND id NOT IN ('.implode(',',$banned).') ').'ORDER BY RAND() LIMIT 0,1');
				$line3=mysqli_fetch_assoc($res);
					//debug echo "survey:{$line3['form']}";
				//D-check if current visitor has already answered this form
				if($line3){
					$res = mysqli_query($db_conn,"SELECT id FROM submissions_{$line2['org']} WHERE agent = {$line['id']} AND form = {$line3['form']} LIMIT 0,1");
					if(mysqli_num_rows($res)){
							//debug echo "submitted before";
						$banned[]=$line3['form'];
						if ($lim< 100) $lim++;//so that bans dont cause trouble, but without risking running too long
					} else break;
				}
			}
		}
		if((!empty($arr))&&($i< $lim)&&(reserve_credit($line2['org'],$line3['form']))) {
			mysqli_query($db_conn,"UPDATE visitors SET org = {$line2['org']}, form = {$line3['form']}, step =0 WHERE id = {$line['id']}");
			$res=mysqli_query($db_conn,"SELECT forms_{$line2['org']}.name AS form, end_users.name AS org FROM forms_{$line2['org']}, end_users WHERE forms_{$line2['org']}.id = {$line3['form']} AND end_users.id = {$line2['org']} LIMIT 0,1");
			$line2=mysqli_fetch_assoc($res);
			echo 'Coming up: "'.$line2['form'].'" by "'.$line2['org'].'"!<br/>';
			echo '<a href="forget.php" accesskey="99">skip</a><br/>';
			echo '<a href="survey.php">start survey</a><br/>';
			echo '<a href="code.php">use survey code</a><br/>';
			echo '<a href="info.php" accesskey="9">more info</a><br/>';
		} else {
			if(file_exists($cache_file)) unlink($cache_file);
			echo 'Sorry, we do not have any surveys for you at the moment.<br/>';
			echo 'Please try again later.<br/>';
			echo '<a href="code.php">use survey code</a><br/>';
			echo '<a href="info.php" accesskey="9">more info</a><br/>';
		}
	}
} else {
	mysqli_query($db_conn,"INSERT INTO visitors (phone) VALUES ($msisdn)");
	echo 'Welcome to AskPeople.<br/>';
	echo 'Sign-up in 5 easy steps and start earning rewards today!<br/>';
	echo '<a href="survey.php">sign-up</a><br/>';
	echo '<a href="info.php" accesskey="9">more info</a><br/>';
}
echo '</body>';
echo '</html>';
?>