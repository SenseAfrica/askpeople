<?php
@session_start();
include_once ('db.php');
include_once ('mail.php');
if ((isset($_GET['cancel']))&&(empty($_POST))) unset($_SESSION['stored_form'],$_SESSION['page']);
if ((isset($_GET['insert']))&&(isset($_SESSION['stored_form']))&&($_SESSION['page']=='new_agent')) {
	if (filter_var($_SESSION['stored_form']['email'],FILTER_VALIDATE_EMAIL)) {
		//prepare the data
		$_SESSION['stored_form']['email']=mysqli_real_escape_string($mail=$_SESSION['stored_form']['email']);
		$_SESSION['stored_form']['password']= md5(($pass= ($_SESSION['stored_form']['password']=='')? str_pad(((mt_rand(123456,999999)).''),6,'0',STR_PAD_LEFT):$_SESSION['stored_form']['password']).'no-paps');
		$_SESSION['stored_form']['realname']=mysqli_real_escape_string($uname=$_SESSION['stored_form']['realname']);
		//insert data
		$last = end($_SESSION['temp_path']);
		$res = mysqli_query($qry="INSERT INTO users (password,admin,org,email,realname,node) VALUES ('{$_SESSION['stored_form']['password']}',".((isset($_GET['admin']))?1:0).",{$_SESSION['user']['org']},'{$_SESSION['stored_form']['email']}','{$_SESSION['stored_form']['realname']}',{$last['id']})");
		//echo $qry;
		if (($res)&&(mysqli_affected_rows())){
			//send the mail (using: uname mail pass)
			$msg=
"<html>
	<h1>Welcome to <b>AskPeople</b>!</h1>			
	<p>An account has been created for you on the AskPeople platform.</p>
	<h3>Account details:</h3>
	<p>
		<b>Name</b>: $uname
		<br/><b>E-mail</b>: $mail
		<br/><b>Password</b>: $pass
	</p>
</html>";
			$res=HW_send ($mail,'Welcome to AskPeople',$msg);
			
			if ($res) {
				$success="Agent successfuly created! A welcome mail has been sent.";
				include('hierarchy.php');
				exit;
			}
			else $error="Agent created, but e-mail not sent!";
		} else $error="Database Error. Please Try Again Later.";
	} else $error="Invalid e-mail address. Try again later.";
	unset($_SESSION['stored_form'],$_SESSION['page']);
}
$title="New Agent";
if ((!isset($_SESSION['page']))||($_SESSION['page']!='new_agent')){
	$node=(isset($_GET['node']))?$_GET['node']:$_SESSION['user']['node'];
	unset($_SESSION['stored_form']);
	$_SESSION['page']='new_agent';
}
include ("head.php");
?>
<style>
label b{
color: red;
font-size: 20px;
line-height: 15px;
}
.embeds {display:<?php echo (isset($_SESSION['stored_form']))?'block':'none';?>}
#embed_cover {width:100%;height:100%;position:absolute;top:0;left:0;opacity:0.2;z-index:100}
#checking {float:right;display:none}
</style>
<script>
check_mail = function(){
			input=$('#email')[0].value;
			if (/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(input)) {
				with($('#verifying')[0]){
					setAttribute('class','icon-busy fg-black');
					style.display='block';
				}
				$.get('check_mail.php?mail='+encodeURIComponent(input),function(data,status){
					var memory=input;
					if ($('#email')[0].value==memory){
						if (data=='OK') $('#verifying')[0].setAttribute('class','icon-checkmark fg-emerald');
						else if (data=='KO') $('#verifying')[0].setAttribute('class','icon-warning fg-red');
					}
				});
			} else $('#verifying')[0].setAttribute('class','icon-warning fg-red');
		}
var ready=false;
/*
embed_submit=function(){
	ready=true;
	$("#frm")[0].submit();
}
*/
sub = function (){
	if ($("#embed")[0].style.display!='block'){
		if ($('#verifying')[0].getAttribute('class')=='icon-warning fg-red') $.Notify({caption: 'Alert!',content: 'This e-mail is already taken',timeout: 5000,style: {background: 'red', color: 'white'}});
		else if ($('#verifying')[0].getAttribute('class')!='icon-checkmark fg-emerald') $.Notify({caption: 'Wait!',content: 'We are checking availability of the e-mail address',timeout: 5000,style: {background: 'amber', color: 'white'}});
		else {
			var form=$("#frm")[0];
			$.post("temp_form.php", {
				password:form.password.value,
				email:form.email.value,
				realname:form.realname.value
				}, function(){});
			$("#embed_cover")[0].style.display="block";
			$("#embed").fadeIn("slow");
		}
		return false;
	} else return ready;
}
</script>

		<div class="grid">
			<div class="row">
				<div class="span4"style="position:relative;padding:10px">
				<h1>New Agent</h1>
						<fieldset>
									<form id="frm"onsubmit='return sub()'method="POST">
							<label>Real name <b>*</b></label>
							<div class="input-control text" data-role="input-control">
								<input <?php if (isset($_SESSION['stored_form'])) echo "value='{$_SESSION['stored_form']['realname']}'";?> type="text" required="required"placeholder="type real name"name="realname">
								<button class="btn-clear"  type="button"></button>
							</div>
							<label>E-mail <b>*</b> <i id="verifying"style="float:right"></i></label>
							<div class="input-control text" data-role="input-control">
								<input <?php if (isset($_SESSION['stored_form'])) echo "value='{$_SESSION['stored_form']['email']}'";?> type="email" placeholder="type e-mail"id="email"name="email"required="required"onfocus="this.setAttribute('tmp',this.value);$('#verifying')[0].style.display='none'"onchange="check_mail()"onblur="if(this.getAttribute('tmp'))$('#verifying')[0].style.display='block'"/>
								<button class="btn-clear"  type="button"></button>
							</div>
							<label>Password (will auto-genrate if left blank)</label>
							<div class="input-control password" data-role="input-control">
								<input <?php if (isset($_SESSION['stored_form'])) echo "value='{$_SESSION['stored_form']['password']}'";?> type="password" placeholder="type password"name="password">
								<button class="btn-reveal"  type="button"></button>
							</div>
							
							<br/><br/>
							<input type="submit"style="float:right;"class="bg-emerald fg-white"value="Save"/>
										</form>
						</fieldset>
					<div id="embed_cover"class="embeds ribbed-emerald"></div>
				</div>
				<div class="span8 embeds"style="position:relative;padding:10px" id="embed">
				 <?php 
					$embedded='agent';
					include ('hierarchy.php');
				?>
				</div>
			</div>
		</div>
<?php
include ("foot.php");
?>