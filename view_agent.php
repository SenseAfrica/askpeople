<?php
include_once('db.php');
@session_start();
$_SESSION['last_agent_id']= $in_id= (isset($_GET['id']))?$_GET['id']:((isset($_SESSION['last_agent_id']))?$_SESSION['last_agent_id']:false);

if ((!is_numeric($in_id)) || ($in_id==$_SESSION['user']['uid']) || (!($res=mysqli_query($db_conn,"SELECT admin,email,realname,active,node FROM users WHERE uid = $in_id  AND org = {$_SESSION['user']['org']} LIMIT 0,1"))) || (!($agent=mysqli_fetch_assoc($res)))){
	include ("head.php");
	if($in_id==$_SESSION['user']['uid']) echo ("<br/><h2>You cannot edit your own account.</h2>");
	else echo ("<br/><h2>Sorry, the agent requested does not exist.</h2>");
	include ("foot.php");
	exit;
}


if(isset($_POST['status'])){
	if($_POST['target']!=$agent['node']) {
		$_POST['target']=(int)$_POST['target'];
		mysqli_query($db_conn,'UPDATE users SET node = '.$_POST['target'].' WHERE uid='.$in_id);
		$agent['node']=$_POST['target'];
	}
	$_POST['status']=(int)$_POST['status'];
	if($agent['active']!=$_POST['status']) {
		mysqli_query($db_conn,'UPDATE users SET active = '.$_POST['status'].' WHERE id='.$in_id);
		$agent['active']=$_POST['status'];
	}
	$_POST['admin']=(int)$_POST['admin'];
	if($agent['admin']!=$_POST['admin']) {
		mysqli_query($db_conn,'UPDATE users SET admin = '.$_POST['admin'].' WHERE id='.$in_id);
		$agent['admin']=$_POST['admin'];
	}
	$success="Agent settings were updated.";
}



$title=$agent['realname'];
include ("head.php");
?>
<h1><?php echo $agent['realname'];?></h1>
<legend>Agent settings</legend>
<form method="POST">
	<div class="span4 statbox">
		<label>Status</label>
		<div class="input-control select">
			<select name="status">
				<option value="1">Active</option>
				<option value="0"<?php if (!$agent['active']) echo 'selected="selected"';?>>Inactive (blocked)</option>
			</select>
		</div>
		<label>Is an administrator</label>
		<div class="input-control select">
			<select name="admin">
				<option value="1">Yes</option>
				<option value="0"<?php if (!$agent['admin']) echo 'selected="selected"';?>>No</option>
			</select>
		</div>
		<label>E-mail address</label>
		<div class="input-control text">
			<input name="email"type="email" value="<?php echo $agent['email'];?>" disabled="disabled"/>
			<button class="btn-clear"></button>
		</div>
		<label>Target market</label>
		<div class="input-control select">
			<select name="target">
				<?php
				$res=mysqli_query($db_conn,'SELECT id, name FROM nodes_'.$_SESSION['user']['org']);
				while ($line=mysqli_fetch_assoc($res)) echo '<option '.(($line['id']==$agent['node'])?'selected="selected"':'').'value="'.$line['id'].'">'.$line['name'].'</option>';
				?>
			</select>
		</div>
	</div>
	<br/>
<input class="large inverse"type="submit" value="Update"/>
</form>
<style>
.statbox{border:1px solid gray;background:white;padding:10px}
.input-control{margin-bottom:7px}
tbody tr{cursor:pointer}
.metro .tab-control .frames{border-top:1px dashed black}
</style>				
<?php
include ("foot.php");
?>