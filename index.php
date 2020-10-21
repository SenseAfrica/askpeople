<?php 
@session_start();
include_once ('db.php');
if(isset($_GET['go'])){
	include('webform.php');
	exit;
}
$title="Home";

if (isset($_GET['alert'])) $alert=urldecode($_GET['alert']);


if (isset($_POST['email'])){
	$res=mysqli_query($db_conn,$qry='SELECT * FROM `users` LEFT JOIN `end_users` ON org = id WHERE email = "'.mysqli_real_escape_string($db_conn,$_POST['email']).'" AND password = "'.md5($_POST['password'].'no-paps').'" LIMIT 0,1');
	//echo $qry;
	if ($res){
		if (mysqli_num_rows($res)){
			$_SESSION['user']=mysqli_fetch_array($res, mysqli_ASSOC);
			mysqli_query($db_conn,'UPDATE users SET last_active = CURRENT_TIMESTAMP WHERE uid = '.$_SESSION['user']['uid']);
		}
		else $alert="Incorrect E-mail/Password Combination";
	} else $alert="Database Error 3. Please Try Again Later.";
}

if (isset($_SESSION['user'])){
	if (isset($_POST['new_pwd'])){
		if ($_POST['new_pwd']==$_POST['new_pwd_2']){
			if(md5($_POST['old_pwd'].'no-paps')==$_SESSION['user']['password']){
				$res=mysqli_query($db_conn,'UPDATE users SET password = "'.md5($_POST['new_pwd'].'no-paps').'" WHERE uid = '.$_SESSION['user']['uid']);
				if (($res)&&(mysqli_affected_rows($db_conn))){
					$_SESSION['user']['password']=md5($_POST['new_pwd'].'no-paps');
					$success="Password Successfuly Updated";
				}
				else $alert="Database Error 4. Please Try Again Later.";
			} else $alert="Incorrect Password.";
		} else $alert="Please Re-enter the New Password.";
	}
	if (isset($_POST['bill_phone'])){
		include_once('challenge/utils.php');
		$amt=(int)$_POST['bill_amount'];
		if(charge($_POST['bill_phone'],$amt)){
			$res=mysqli_query($db_conn,"UPDATE end_users SET credits=credits+$amt WHERE id=".$_SESSION['user']['org']);
			if($res) $success="You have recharged your accound with $amt XOF";
		}else $error="Please check your number, and your balance.";
	}
	include ("head.php");
	include('hierarchy.php');
	exit();
} else {
	include ("head.php");
?>
		<br/><br/>
		<div class="grid">
			<div class="row">
				<div class="padding10"align="center" style="margin:0">
					<h1><i class="icon-files"></i> <small><i class="icon-arrow-right-4"></i></small> <i class="icon-mobile"></i> <small><i class="icon-arrow-right-4"></i></small> <i class="icon-bars"></i></h1>
					<h1 style="">Ask People !</h1><br/>
					<button class="bg-green fg-white"style="width:140px"onclick="login()"><h2>login</h2></button>
					<a href="signup.php"><button class="bg-orange fg-white"style="width:140px"><h2>sign-up!</h2></button></a>
				</div>
			</div>
			<br/>
			<div class="row no-phone">
				<style>
					#steps{margin-left:112px;margin-right:112px;border-top:2px solid;position:relative}
					#steps button{width:40px;height:40px;font-weight:bold;border-radius:20px;background:black;color:white;position:absolute;top:-20px}
					.notice{background-color:#d9d9d9!important}
				</style>
				<div id="steps"align="center">
					<button style="left:-20px">1</button>
					<button style="position:relative">2</button>
					<button style="right:-20px">3</button>
				</div>
				<div style="position:relative"align="center">
					<div class="notice marker-on-top"style="width:220px;position:absolute;left:0px;top:0">
						Easily build survey forms
					</div>
					<div class="notice marker-on-top"style="width:220px">
						Send them around or let us broadcast for you
					</div>
					<div class="notice marker-on-top"style="width:220px;position:absolute;right:0px;top:0">
						View detailed results
					</div>
				</div>
			</div>
		</div>
		<script>
			login=function(){
				txt='<form method="POST" action="index.php">'+
					'<fieldset>'+
						'<legend>Welcome!</legend>'+
						'<label>E-mail</label>'+
						'<div class="input-control text" data-role="input-control">'+
							'<input type="email" placeholder="type email"name="email"required="required"autofocus="">'+
							'<button class="btn-clear"  type="button"></button>'+
						'</div>'+
						'<label>Password</label>'+
						'<div class="input-control password" data-role="input-control">'+
							'<input type="password" placeholder="type password"name="password"required="required">'+
							'<button class="btn-reveal"  type="button"></button>'+
						'</div>'+
						'<a href="javascript:forgot_pwd()"><small>Forgot password?</small></a>'+
						'<br/><br/>'+
						'<input style="float:right"type="submit" value="Submit">'+
					'</fieldset>'+
				'</form>';
				$.Dialog({
					overlay: true,
					shadow: true,
					flat: true,
					padding:30,
					title: 'Login',
					content: txt
				});
			}
			forgot_pwd=function(){
				mail=prompt("Enter the e-mail you used to sign-up. We will send you a password-reset link.");
				$.get('API/recover/?mail='+encodeURIComponent(mail),function(data,status){
					if (data=='OK') $.Notify({caption: 'Success!',content: 'Password reset processed. Please check your mail (including spam).',timeout: 5000,style: {background: 'green', color: 'white'}});
					else $.Notify({caption: 'Alert!',content: 'Unknown error (probably address unknown).',timeout: 5000,style: {background: 'red', color: 'white'}});
				});
				$.Notify({caption: 'Sent',content: 'Processing...',timeout: 3000,style: {background: 'black', color: 'white'}});
			}
		</script>
<?php
}
include ("foot.php");
?>