<?php
function show_node($node,$last=false){
global $db_conn;
//This function ensures that this node can be seen by uses through profiling
	if((!$last)||($last['id']==$node)){
		$res=mysqli_query($db_conn,"SELECT * FROM nodes_{$_SESSION['user']['org']} WHERE id = $node");
		if($res)$last=mysqli_fetch_assoc($res);
	}
	if(!$last) return false;
	$res=mysqli_query($db_conn,"SELECT * FROM profiling WHERE org = {$_SESSION['user']['org']} AND node = $node LIMIT 0,1");
	if(mysqli_num_rows($res)) return true;
	else {
		//set up the db wrt the preferences
		$crit=$last['criteria'];
		//var_dump($crit);
		$qry='SELECT id FROM profiles WHERE ';
		$qry.='min_age='.((isset($crit['age']))?str_replace('-',' AND max_age=',$crit['age']):'13 AND max_age=150');
		$qry.=' AND gender '.((isset($crit['sex']))?('='.(($crit['sex']=='M')?'1':'0')):'IS NULL');
		$nulls=[];					
		$items=array('ed','job','kids');
		for($j=0;$j<3;$j++) {
			if(isset($crit[$items[$j]])) for($i=0;$i<3;$i++){
				if(!in_array($i,$crit[$items[$j]])) {
					$nulls[]=$items[$j].'_'.$i;
					$qry.=' AND '.$items[$j].'_'.$i.'=0';
				} else $qry.=' AND '.$items[$j].'_'.$i.'=1';
			}
			else for($i=0;$i<3;$i++) $qry.=' AND '.$items[$j].'_'.$i.'=1';
		}
		$res=mysqli_query($db_conn,$qry);
		if(mysqli_num_rows($res)){
			$line=mysqli_fetch_assoc($res);
			$profile=$line['id'];
		} else {
			$qry="INSERT INTO profiles (min_age,max_age".((isset($crit['sex']))?',gender':'').((!empty($nulls))?','.implode(',',$nulls):'').") VALUES (".((isset($crit['age']))?str_replace('-',',',$crit['age']):'13,150').((isset($crit['sex']))?','.(($crit['sex']=='M')?'1':'0'):'');
			for($i=0;$i<count($nulls);$i++) $qry.=',0';
			$qry.=")";
			mysqli_query($db_conn,$qry);
			$profile=mysqli_insert_id($db_conn);
		}
		if ($profile){
			$res=mysqli_query($db_conn,"INSERT INTO profiling (org,node,profile) VALUES ({$_SESSION['user']['org']},$node,$profile)");
			if (!$res) $error ="There was an error when setting the target profile of your unit";
			return true;
		}
	}
	return false;
}
function check_node($node,$org=false){
//this function checks if the given node still deserves profiling. it removes inactive branches
	if($org){
		//we cannot count on session, and $ode is in fact the form
		$res=mysqli_query($db_conn,"SELECT node FROM forms_$org WHERE id=$node");
		$ln=mysqli_fetch_assoc($res);
		$node=$ln['node'];
	} else if(isset($_SESSION['user']['org'])) $org=$_SESSION['user']['org'];
	else return;
	$res=mysqli_query($db_conn,"SELECT id FROM forms_$org WHERE node=$node AND active = 1 LIMIT 0,1");
	if(!(mysqli_num_rows($res))){
		$res=mysqli_query($db_conn,"SELECT profile FROM profiling WHERE org=$org AND node =$node");
		$line=mysqli_fetch_assoc($res);
		mysqli_query($db_conn,"DELETE FROM profiling WHERE org=$org AND node =$node");
		$res=mysqli_query($db_conn,"SELECT org FROM profiling WHERE profile={$line['profile']} LIMIT 0,1");
		if(!(mysqli_num_rows($res))) mysqli_query($db_conn,"DELETE FROM profiles WHERE id ={$line['profile']}");
	}
}
?>