<?php
ob_start();

include('head.php');
echo '<html>';
echo '<body>';

$res=mysql_query("SELECT id,org,form,step FROM visitors WHERE phone = $msisdn");
$line=mysql_fetch_assoc($res);
if ($line['form']==0){
	include('../../account_options.php');
	$questions=array(
		array(
			'type'=>'int',
			'text'=>'In what year were you born?',
			'column'=>'birth'
		),
		array(
			'type'=>'mcq',
			'text'=>'Of what sex are you?',
			'options'=>array('Female','Male'),
			'column'=>'gender'
		),
		array(
			'type'=>'mcq',
			'text'=>'Which option best describes your education?',
			'options'=>$options['ed'],
			'column'=>'ed'
		),
		array(
			'type'=>'mcq',
			'text'=>'Which option best describes your professional situation?',
			'options'=>$options['job'],
			'column'=>'job'
		),
		array(
			'type'=>'mcq',
			'text'=>'Which option best describes your family situation?',
			'options'=>$options['kids'],
			'column'=>'kids'
		)
	);
	$curr_q=$questions[$line['step']];
} else {
	function cut_out($string,$start,$end){
		$str_two = substr(substr($string, strpos($string, $start)), strlen($start));
		return trim(substr($str_two, 0, strpos($str_two, $end)));
	}
	function clean_q ($q){
		$out=array(
			'text'=>cut_out($q,'<label>','</label>'),
			'column'=>cut_out($q,'name="','">')
		);
		if (strpos($q,'<select name="')){
			$arr=explode('<option>',str_replace('</option>','',cut_out($q,'<select name="','</select>')));
			array_shift($arr);
			$out['type']='mcq';
			$out['options']=$arr;
		}
		else if(strpos($q,'<input type="number"')) $out['type']='int';
		else if(strpos($q,'<input type="text"')) $out['type']='str';
		return $out;
	}
	function add_to_temp($key,$val){
		global $line;
		$obj= (file_exists('tmp/'.$line['id']))? json_decode(file_get_contents('tmp/'.$line['id']),true) : array();
		$obj[$key]= $val;
		file_put_contents('tmp/'.$line['id'],json_encode($obj));
	}
	$res=mysql_query("SELECT fieldset,name,mail FROM forms_{$line['org']} WHERE id = {$line['form']}");
	$line2=mysql_fetch_assoc($res);
	$questions=explode('</li><li',$line2['fieldset']);
	$admin=array($line2['name'],$line2['mail']);
	$curr_q=clean_q($questions[$line['step']]);	
}

if (isset($_GET["response"])) {
	switch($curr_q['type']){
		case 'mcq':
			if ((is_numeric($_GET["response"]))&&($_GET["response"]>0)&&(count($curr_q['options'])>=$_GET["response"])) $val=($line['form']==0)?($_GET["response"]-1):$curr_q['options'][$_GET["response"]-1];
			break;
		case 'str':
			if (!empty($_GET["response"])) $val=$_GET["response"];
			break;
		case 'int':
			if (is_numeric($_GET["response"])) $val=(int)$_GET["response"];
			$yr=date('Y');
			if (($line['form']==0) && (($val>$yr-13)||($val<$yr-150))) unset($val);
			break;
	}
	if (isset($val)){
		if($line['form']==0) {
			mysql_query("UPDATE visitors SET {$curr_q['column']} = $val, step = step +1 WHERE id = {$line['id']}");
			if($line['step']==4) {
				$curr_q=false;
				echo 'All is set!<br/>';
				echo 'You are now a member of AskPeople.<br/>';
				echo '<a href="forget.php" accesskey="99">home</a><br/>';
				echo '<a href="info.php" accesskey="9">more info</a><br/>';
			}
			else $curr_q=$questions[$line['step']+1];
		} else {
			add_to_temp($curr_q['column'],$val);
			if (count($questions)<=$line['step']+1) {
				$curr_q=false;
				$obj =json_decode(file_get_contents('tmp/'.$line['id']),true);
				unlink('tmp/'.$line['id']);
				$num=count($obj);
				$needed=10*$num;
				mysql_query("DELETE FROM reservations WHERE org={$line['org']} AND form={$line['form']} AND visitor={$line['id']}");
				if(mysql_affected_rows()) mysql_query("UPDATE end_users SET reserve = reserve-$needed, credits=credits-$needed WHERE id={$line['org']} AND credits>=$needed");
				file_put_contents('debug.txt',"UPDATE end_users SET reserve = reserve-$needed, credits=credits-$needed WHERE id={$line['org']} AND credits>=$needed");
				
				include_once('../../submission.php');
				if(isset($admin)) submit_survey($line['id'],$obj,$line['org'],$line['form'],$admin[0],$admin[1]);
				else submit_survey($line['id'],$obj,$line['org'],$line['form']);
					
				if(mysql_affected_rows()) {
					mysql_query("UPDATE visitors SET org = 0, form = 0, step = 5, credits = credits + ($num*5) WHERE id = {$line['id']}");
					//include_once('../../submission.php');
					//if(isset($admin)) submit_survey($line['id'],$obj,$line['org'],$line['form'],$admin[0],$admin[1]);
					//else submit_survey($line['id'],$obj,$line['org'],$line['form']);
				} else {
					mysql_query("UPDATE end_users SET reserve = reserve-$needed WHERE id={$line['org']}");
					//MAIL ABOUT LOW CREDIT
					if(isset($admin)){
						include_once('../../mail.php');
						HW_send($admin[1],'Credit is Low',"
Greetings!
Your account credit is too low to accept new submissions!
This happened as a respondent attempted to answer your form titled \"$admin[0]\".
Thanks!");
					}
				}
				echo 'Thanks for your time.<br/>';
				echo 'You answered '.$num.' questions! Check info for your rewards.<br/>';
				echo '<a href="info.php" accesskey="9">more info</a><br/>';
			} else {
				mysql_query("UPDATE visitors SET step = step +1 WHERE id = {$line['id']}");
				$curr_q=clean_q($questions[$line['step']+1]);
			}
		}
	} else{
		echo 'Invalid answer. Try again<br/>';
		echo '--<br/>';
	}
}
if($curr_q){
	echo $curr_q['text'].'<br/>';
	if($curr_q['type']=='mcq')for($i=0;$i<count($curr_q['options']);$i++) echo ($i+1).':'.$curr_q['options'][$i].'<br/>';
	echo '  <form action="survey.php">';
	echo '    <input type="text" name="response"/>';
	echo '  </form>';
}

echo '</body>';
echo '</html>';

file_put_contents('debug.txt',ob_get_contents());
ob_end_flush();
?>