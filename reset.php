<?php
@session_start();
if ((isset($_POST['password']))&&(isset($_SESSION['accounts_to_reset']))){
	include_once ('db.php');
	mysqli_query($db_conn,"UPDATE users SET password ='".md5($_POST['password'].'no-paps')."' WHERE uid IN (".implode(',',$_SESSION['accounts_to_reset']).")");
	mysqli_query($db_conn,"DELETE FROM lost_pass WHERE id = {$_SESSION['request_to_reset']} LIMIT 1");
	$success="Passwords updated!";
	unset($_SESSION['accounts_to_reset'],$_SESSION['request_to_reset']);
	include('index.php');
	exit();
}else if ((!isset($_GET['req']))||(!isset($_GET['id']))) {
	include('index.php');
	exit();
}
$title="Password Recovery";
include ("head.php");
$res=mysqli_query($db_conn,"SELECT accounts from lost_pass WHERE id = ".(int)$_GET['id']." AND code = ".(int)$_GET['req']." AND submitted > DATE_SUB(NOW(), INTERVAL 3 HOUR) LIMIT 0,1");
if (($res)&&($data=mysqli_fetch_array($res))){
?>
	<h1>Password Recovery</h1>
	<p class="bg-grayLighter padding20">
		Use the form at the bottom to set a new password for your AskPeople account.
	</p><br/>
	<div class="grid"><div class="row"><div class="span4">
	<table class="table striped bordered">
		<thead><tr><th>Username</th><th>Organization</th></tr></thead>
		<tbody>
			<?php
			$accounts=json_decode($data['accounts'],true);
			$_SESSION['accounts_to_reset']=array();
			$_SESSION['request_to_reset']=(int)$_GET['id'];
			foreach ($accounts as $account) {
				echo "<tr><td>{$account['realname']}</td><td>{$account['name']}</td></tr>";
				$_SESSION['accounts_to_reset'][]=$account['uid'];
			}
			?>
		</tbody>
	</table>
	<form method="POST">
		<label>New Password</label>
		<div class="input-control password" data-role="input-control">
			<input type="password" placeholder="type password"name="password"required="required">
			<button class="btn-reveal"  type="button"></button>
		</div>
		<input type="submit">
	</form>
	</div></div></div>
<?php
} else exit ('<h3>The password-recovery request you entered does not exist, or has expired.</h3>');
include_once ('foot.php');
?>