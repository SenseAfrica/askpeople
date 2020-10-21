<?php
function submit_survey($visitor,$obj,$org,$form,$form_title='',$admin_mail=false){
	//var_dump($obj);
	$keys=array();
	$values=array();
	foreach ($obj as $key=>$value){
		$keys[]="`".mysqli_real_escape_string($db_conn,$key)."`";
		if(is_numeric($value)) $values[]=(int)$value;
		else $values[]="'".mysqli_real_escape_string($db_conn,$value)."'";
	}
	$res=mysqli_query($db_conn,'SELECT tablename,deactivator,alerter,phone,name FROM forms_'.$org.' WHERE id = '.$form);
	//echo ('SELECT tablename,deactivator,alerter,phone,name FROM forms_'.$org.' WHERE id = '.$form.'<br/>');
	$line2=mysqli_fetch_assoc($res);
	$res=mysqli_query($db_conn,'INSERT INTO form_'.$org.'_'.$line2['tablename'].' ('.implode(',',$keys).') VALUES ('.implode(',',$values).')');
	//echo 'INSERT INTO form_'.$org.'_'.$line2['tablename'].' ('.implode(',',$keys).') VALUES ('.implode(',',$values).')'.'<br/>';
	if(($res)&&(mysqli_affected_rows($db_conn))){
		$id=mysqli_insert_id($db_conn);
		mysqli_query($db_conn,"INSERT INTO submissions_$org (agent,form,row_id) VALUES ($visitor,$form,$id)");
		//echo "INSERT INTO submissions_$org (agent,form,row_id) VALUES ($visitor,$form,$id)";
		
		$hr=floor(time()/(60 * 60));
		$nd=1;
		mysqli_query($db_conn,$qry="UPDATE stats_$org SET count = count +1 WHERE form = $form AND node = $nd AND hour = $hr LIMIT 1");
		if (!mysqli_affected_rows($db_conn)){
			mysqli_query($db_conn,"INSERT INTO stats_$org (form,node,hour,count,node_name) VALUES ($form,$nd,$hr,1,'[All submissions]"./*mysqli_real_escape_string($db_conn,$nd_nm).*/"')");
			if (!mysqli_affected_rows($db_conn)) mysqli_query($db_conn,$qry);
		}
		
		//handle deactivating and alerting of submissions
		$res=mysqli_query($db_conn,'SELECT COUNT(*) AS num FROM form_'.$org.'_'.$line2['tablename']);
		$line3=mysqli_fetch_assoc($res);
		$count=$line3['num'];
		if(($line2['alerter'])&&($count%$line2['alerter'])){
			if(!$line2['phone']){
				$res=mysqli_query($db_conn,'SELECT phone FROM end_users WHERE id ='.$org);
				$line3=mysqli_fetch_assoc($res);
				$line2['phone']=$line3['phone'];
			}
			include_once('challenge/utils.php');
			sms($org,$line2['phone'],'Alert on '.$count.' sumbissions. Form "'.$line2['name'].'"');
		}
		if(($line2['deactivator'])&&($count>=$line2['deactivator'])){
			include_once('shownode.php');
			mysqli_query($db_conn,'UPDATE forms_'.$org.' SET active = 0 WHERE id='.$form);
			check_node($form,$org);
			//MAIL ABOUT DEACTIVATION
			if($admin_mail){
				include_once('mail.php');
				HW_send($admin_mail,'Survey deactivated: '.$form_title,"
	Greetings!
	Your form titled \"$form_title\" has been automatically deactivated, because it has reached it preset maximum.
	Thanks!");
			}
		}
	}
}