<?php
if(!isset($_SESSION['answered'])) $_SESSION['answered']=array();
$title="Web survey";
if(isset($_POST['HWINPUTS-submit'])){
	//Handle the data
	if(!isset($_SESSION['submitter_id'])){
		//check captcha
		$fh = fopen('http://www.easycaptchas.com/check.aspx?sessionid='.session_id().'.'.$_SESSION['captchas'].'.askpeople.info&input='.$_POST['captcha'], 'r');
		$result = trim(fread($fh,8192));
		if ($result == 'TRUE') {
			mysqli_query($db_conn,'INSERT INTO visitors (step,birth,gender,ed,job,kids) VALUES (5,'.(int)$_POST['HWINPUTS-birth'].','.(int)$_POST['HWINPUTS-gender'].','.(int)$_POST['HWINPUTS-ed'].','.(int)$_POST['HWINPUTS-job'].','.(int)$_POST['HWINPUTS-kids'].')');
			if ($id=mysqli_insert_id()) $_SESSION['submitter_id']=(int)$id;
			else $alert="Something went wrong (database error). Try again later.";
		}
		else $alert="You entered the wrong captcha code!";
	}
	else {
		foreach ($_POST as $key=>$val) if (substr($key,0,9)=='HWINPUTS-') unset($_POST[$key]);
		if(!empty($_POST)){
			include_once('submission.php');
			submit_survey($_SESSION['submitter_id'],$_POST,$_SESSION['tmp'][1],$_SESSION['tmp'][2],$_SESSION['tmp'][3],$_SESSION['tmp'][4]);
			$_SESSION['answered'][]=$_SESSION['tmp'][0];
			$success="Your submission has been saved and computed.";
			include ("head.php");
			echo ("<br/><h2>Thanks for your contribution!</h2>");
			include ("foot.php");
			unset ($_SESSION['tmp']);
			exit;
		} else $warning="Something went wrong (survey error). Try again later";
	}
}
if ((isset($_SESSION['answered']))&&(in_array($_GET['go'],$_SESSION['answered']))){
	include ("head.php");
	echo ("<br/><h2>It seems you have already taken this survey.</h2>");
	include ("foot.php");
	exit;
}
if ((!isset($_GET['go'])) || (count($tmp=explode('.',$_GET['go']))<2) || (!is_numeric($tmp[0]))|| (!is_numeric($tmp[1])) || (!($res=mysqli_query($db_conn,"SELECT fieldset,name,mail FROM forms_{$tmp[0]} WHERE id = {$tmp[1]} AND public =1 AND active = 1"))) || (!($form=mysqli_fetch_assoc($res)))){
	include ("head.php");
	echo ("<br/><h2>Sorry, the form requested does not exist, or is restricted or inactive.</h2>");
	include ("foot.php");
	exit;
}
$_SESSION['tmp']=array($_GET['go'],$tmp[0],$tmp[1],$form['name'],$form['mail']);


$title="Home";
include ("head.php");
?>
<h1><?php echo $form['name']; $submit_text="Take survey >"; ?></h1>
<div class="grid">
<div class="row"style="">
	<form method="POST">
<?php if(!isset($_SESSION['submitter_id'])){ ?>	
	<div class="span4 statbox">
		<legend>Captcha</legend>
		<img id="captchaimg" style="visibility:hidden;position:relative;top: -10px;left: 85px"src="API/captcha.php" border="0"onload="this.style.visibility='visible'"/>
		<a href="#" onclick="with(document.getElementById('captchaimg')){style.visibility='hidden';src+='?rand='+Math.random();};return false;" style="position:relative;left:-130px;top:-15px;"><i class="icon-cycle"></i> reload</a>
		<div class="input-control text">
			<input type="text" placeholder="enter the text above"name="captcha"required="required"/>
			<button class="btn-cycle"  type="button"></button>
		</div>
	</div>
	<div class="span4 statbox">
		<legend>Anonymized statistics</legend>
		<label>Year of birth (a number)</label>
		<div class="input-control text">
			<input name="HWINPUTS-birth"required="required"type="number"<?php if (isset($_POST['HWINPUTS-birth'])) echo 'value="'.$_POST['HWINPUTS-birth'].'"'; ?>/>
			<button class="btn-clear"></button>
		</div>
		<label>Gender</label>
		<div class="input-control radio default-style">
			<label style="float:left">
				<input required="required"type="radio"name="HWINPUTS-gender"value="1"<?php if ((isset($_POST['HWINPUTS-gender']))&&($_POST['HWINPUTS-gender']==1)) echo 'checked=checked'; ?>/>
				<span class="check"></span>
				Male&emsp;
			</label>
			&emsp;
			<label style="float:left">
				<input type="radio"name="HWINPUTS-gender"value="0"<?php if ((isset($_POST['HWINPUTS-gender']))&&($_POST['HWINPUTS-gender']==0)) echo 'checked=checked'; ?>/>
				<span class="check"></span>
				Female
			</label>
		</div>
		<label>Describe your education</label>
		<div class="input-control select">
			<select name="HWINPUTS-ed">
				<?php
				include_once('account_options.php');
				for ($i=0;$i<3;$i++) echo "<option value='$i'".(((isset($_POST['HWINPUTS-ed']))&&($_POST['HWINPUTS-ed']==$i))? 'selected=selected':'').">{$options['ed'][$i]}</option>";
				?>
			</select>
		</div>
		<label>Describe your work</label>
		<div class="input-control select">
			<select name="HWINPUTS-job">
				<?php
				include_once('account_options.php');
				for ($i=0;$i<3;$i++) echo "<option value='$i'".(((isset($_POST['HWINPUTS-job']))&&($_POST['HWINPUTS-job']==$i))? 'selected=selected':'').">{$options['job'][$i]}</option>";
				?>
			</select>
		</div>
		<label>Describe your status</label>
		<div class="input-control select">
			<select name="HWINPUTS-kids">
				<?php
				include_once('account_options.php');
				for ($i=0;$i<3;$i++) echo "<option value='$i'".(((isset($_POST['HWINPUTS-kids']))&&($_POST['HWINPUTS-kids']==$i))? 'selected=selected':'').">{$options['kids'][$i]}</option>";
				?>
			</select>
		</div>
	</div>
<?php } else { 
	$submit_text="Submit !";
?>
	
	<!--<div class="span4 statbox">
		<legend>Survey questions</legend>-->
		<ol id="survey"><?php echo $form['fieldset'];?></ol>
	<!--</div>-->
	<script>
		var l=$('#survey input'),i;
		for (i=0;i<l.length;i++) l[i].setAttribute('required','required');
		l=$('#survey select'),i;
		for (i=0;i<l.length;i++) l[i].setAttribute('required','required');
		
		var l=$('#survey li'),i;
		l[0].style.marginLeft='20px';
		for (i=0;i<l.length;i++) l[i].setAttribute('class','span4 statbox');
		
		keepSessionAlive=function(){
			$.get('keep_session_alive.php?rand='+Math.random(),function(data){setTimeout(5000,keepSessionAlive)});
		}
		keepSessionAlive();
	</script>
<?php } ?>	
	</div>
	<div class="row">
		<input class="large inverse"name="HWINPUTS-submit"type="submit" value="<?php echo $submit_text; ?>"/>
	</form>
</div>
</div>

	
<style>
.statbox{border:1px solid gray;background:white;padding:10px}
.input-control{margin-bottom:7px}
tbody tr{cursor:pointer}
.metro .tab-control .frames{border-top:1px dashed black}

ol {
    counter-reset:li; /* Initiate a counter */
    margin-left:0; /* Remove the default left margin */
    padding-left:0; /* Remove the default left padding */
}
ol > li {
    position:relative; /* Create a positioning context */
    list-style:none; /* Disable the normal item numbering */
    
    margin-bottom:30px;
    
    /*margin:0 0 6px 2em; /* Give each list item a left margin to make room for the numbers */
    /*padding:4px 8px; /* Add some spacing around the content */
    /*border-top:2px solid #666;*/
    /*background:#f6f6f6;*/
}
ol > li:before {
    content:counter(li); /* Use the counter as content */
    counter-increment:li; /* Increment the counter by 1 */
    /* Position and style the number */
    position:absolute;
    top:-14px;
    left:-1em;
    -moz-box-sizing:border-box;
    -webkit-box-sizing:border-box;
    box-sizing:border-box;
    width:2em;
    /* Some space between the number and the content in browsers that support
       generated content but not positioning it (Camino 2 is one example) */
    margin-right:8px;
    padding:4px;
    border-top:2px solid #666;
    color:#fff;
    background:#666;
    font-weight:bold;
    font-family:"Helvetica Neue", Arial, sans-serif;
    text-align:center;
}
li ol,
li ul {margin-top:6px;}
ol ol li:last-child {margin-bottom:0;}
</style>				
<?php
include ("foot.php");
?>