<style>
label b{
color: red;
font-size: 20px;
line-height: 15px;
}
</style>
<?php
set_time_limit(0);
include_once ('db.php');
if (isset($_POST['orgname'])){
	if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
		//$res=mysqli_query($db_conn,'INSERT INTO nodes (name) VALUES ("'.mysqli_real_escape_string($db_conn,$_POST['orgname']).'")');
		//if (($res)&&(mysqli_affected_rows($db_conn))&&($rid=mysqli_insert_id($db_conn))) {
			if(true) { $rid=1;
			//$res2=mysqli_query($db_conn,'INSERT INTO end_users (name) VALUES ("'.mysqli_real_escape_string($db_conn,$_POST['orgname']).'", '.$rid.')');
			$res2=mysqli_query($db_conn,'INSERT INTO end_users (name,phone) VALUES ("'.mysqli_real_escape_string($db_conn,$_POST['orgname']).'",'.(int)$_POST['phone'].')');
		}
		else $res2=false;
		if (($res2)&&(mysqli_affected_rows($db_conn))&&($id=mysqli_insert_id($db_conn))){
			
			/*
			if ($_FILES['logo']['size']>=3145728) $error="Upload error. File too bog.";
			else if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadfile)) {
				echo "File is valid, and was successfully uploaded.\n";
			} else $error="Error during logo upload.";
			*/
			include ('upload_pic.php');
			$_POST['realname']=(empty($_POST['realname']))?'NULL':"'".mysqli_real_escape_string($db_conn,$_POST['realname'])."'";
			$res=mysqli_query($db_conn,'INSERT INTO users (password, admin, org, email, realname, node) VALUES ("'.md5($_POST['password'].'no-paps').'", 1, '.$id.', "'.mysqli_real_escape_string($db_conn,$_POST['email']).'", '.$_POST['realname'].', '.$rid.')');
			$big_query ="
CREATE TABLE `forms_$id` (
`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `tablename` int(11) NOT NULL,
  `node` int(11),
  `fieldset` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `creator` int(11) NOT NULL,
  `deactivator` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
  `alerter` SMALLINT UNSIGNED NOT NULL DEFAULT '3',
  `phone` BIGINT UNSIGNED NULL DEFAULT NULL,
  `public` tinyint(1) NOT NULL DEFAULT '0',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mail` varchar(255) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `nodes_$id` (
`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `criteria` varchar(255) NOT NULL DEFAULT '{}',
  `parent` int(11) DEFAULT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO nodes_{$id} (name) VALUES ('"./*mysqli_real_escape_string($db_conn,$_POST['orgname']).*/"[ALL]');

CREATE TABLE `extra_nodes_{$id}` (
`id` int(11) NOT NULL AUTO_INCREMENT  PRIMARY KEY,
  `node` int(11) NOT NULL,
  `user` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `extra_nodes_{$id}`
 ADD KEY `node` (`node`), ADD KEY `user` (`user`);

CREATE TABLE `submissions_$id` (
`id` int(11) NOT NULL AUTO_INCREMENT  PRIMARY KEY,
  `agent` int(11) NOT NULL,
  `form` int(11) NOT NULL,
  `row_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


ALTER TABLE `submissions_$id`
 ADD KEY `agent` (`agent`), ADD KEY `form` (`form`);
 
CREATE TABLE `stats_$id` (
`id` int(11) NOT NULL AUTO_INCREMENT  PRIMARY KEY,
  `form` int(11) NOT NULL,
  `node` int(11) NOT NULL,
  `hour` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  `node_name` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `stats_$id` ADD UNIQUE( `form`, `node`, `hour`);

CREATE VIEW agent_view_{$id} AS SELECT uid, realname, users.org, admin, last_active, email, name AS node, COUNT(submissions_{$id}.id) AS submissions FROM users LEFT JOIN submissions_{$id} ON agent = uid, nodes_{$id} WHERE users.org = {$id} AND nodes_{$id}.id = users.node AND active = 1 "./*"AND admin = 0 ".*/"GROUP BY uid;

CREATE VIEW form_view_{$id} AS SELECT active, created, forms_{$id}.id, forms_{$id}.name, nodes_{$id}.name AS node, COUNT(submissions_{$id}.id) AS submissions FROM forms_{$id} LEFT JOIN submissions_{$id} ON form = forms_{$id}.id, nodes_{$id} WHERE nodes_{$id}.id = forms_{$id}.node GROUP BY forms_{$id}.id
";

			$small_query="DROP VIEW form_view_{$id}; DROP VIEW agent_view_{$id}; [X]; DROP table `stats_{$id}`; [X]; DROP table `submissions_{$id}`; [X]; DROP table `extra_nodes_{$id}`; [X]; DROP table `nodes_{$id}`; DROP table `forms_{$id}`; DELETE FROM end_users WHERE id = {$id} LIMIT 1";
			
			if (($res)&&(mysqli_affected_rows($db_conn))){
				$user=mysqli_insert_id($db_conn);
				//all is well
				$i=0;
				$queries=explode(';',$big_query);
				foreach ($queries as $query) if (mysqli_query($db_conn,$debug=trim($query))) {$debug="";$i++;} else {$debug.=' -> '.mysqli_error();break;}
				if($i==11){
					$success="Organization account successfully created";
					include_once('mail.php');
					HW_send($_POST['email'],'Welcome to AskPeople',"<b>An account has been created on the AskPeople platform for {$_POST['orgname']}.</b><br/><p>You have also received 5000 credits to test the system.</p>");
					include_once('challenge/utils.php');
					register_shutdown_function('sms',0,$_POST['phone'],'Welcome to AskPeople');
					$_POST=array();
					include ('index.php');
					exit();
				} else {
					//echo $i.' '.$debug;
					$queries=explode(';',$small_query);
					for ($j=11-$i;$j<12;$j++) if (($query=trim($queries[$j]))!='[X]') mysqli_query($db_conn,$query);
					$alert ="Database Error 1. Please Try Again Later.";
					mysqli_query($db_conn,'DELETE FROM users WHERE id = '.$user.' LIMIT 1');
					mysqli_query($db_conn,'DELETE FROM end_users WHERE id = '.$id.' LIMIT 1');
				}
			} else {
				mysqli_query($db_conn,'DELETE FROM end_users WHERE id = '.$id.' LIMIT 1');
				//mysqli_query($db_conn,'DELETE FROM nodes WHERE id = '.$rid.' LIMIT 1');
				$alert="Account creation error. Probably email is already taken.";
			}
		} else {
			$alert="Database Error 2. Please Try Again Later.";
			//if (isset($rid)) mysqli_query($db_conn,'DELETE FROM nodes WHERE id = '.$rid.' LIMIT 1');
		}
	} else $alert="Invalid e-mail. Please Try Again.";
}

$title="Home";
include ("head.php");
//if (isset($_GET['alert'])) $alert=urldecode($_GET['alert']);
 ?>

		<br/><br/>
		<div class="grid">
			<div class="row">
			<form enctype="multipart/form-data"method="POST">
				<div class="span4">
						<fieldset>
							<legend>Your Admin Account</legend>
							<label>Real name (as on ID) <b>*</b></label>
							<div class="input-control text" data-role="input-control">
								<input type="text" placeholder="type your name"name="realname"required="required"autofocus=""/>
								<button class="btn-clear"  type="button"></button>
							</div>
							<label>E-mail <b>*</b> <i id="verifying"style="float:right"></i></label>
							<div class="input-control text" data-role="input-control">
								<input type="email" placeholder="type e-mail"id="email"name="email"required="required"onfocus="$('#verifying')[0].style.display='none'"onchange="check_mail()"onblur="if(this.value)$('#verifying')[0].style.display='block'"/>
								<button class="btn-clear"  type="button"></button>
							</div>
							<label>Password <b>*</b></label>
							<div class="input-control password" data-role="input-control">
								<input type="password" placeholder="type password"id="password"name="password"required="required">
								<button class="btn-reveal"  type="button"></button>
							</div>
							<div class="input-control password" data-role="input-control">
								<input type="password" placeholder="type password again"id="password2"name="password2"required="required">
								<button class="btn-reveal"  type="button"></button>
							</div>
						</fieldset>
				</div>
				<div class="span1"></div>
				<div class="span4">			
						<fieldset>
							<legend>Organization</legend>
							<label>Organization name (15 char. max.)<b>*</b> <i id="checking"style="float:right"></i></label>
							<div class="input-control text" data-role="input-control">
								<input type="text" placeholder="type organization name"name="orgname"required="required"id="orgname"onfocus="$('#checking')[0].style.display='none'"onchange="check_name()"onblur="if(this.value)$('#checking')[0].style.display='block'"/>
								<button class="btn-clear"  type="button"></button>
							<small style="float:right;position:absolute;right:0;bottom:-15px"><a href="trademark_issues.php">someone reserved my trademark!</a></small>
							</div>
							<label style="margin-top:8px">Phone number (for SMS alerts)<b>*</b></label>
							<div class="input-control text" data-role="input-control">
								<input type="text" placeholder="type phone number"name="phone"required="required"/>
								<button class="btn-clear"  type="button"></button>
							</div>
							<label>Logo (square image)</label>
							<div class="input-control file">
								<input type="hidden" name="MAX_FILE_SIZE" value="3145728" />
								<input type="file" name="logo"/>
								<button class="btn-file"></button>
							</div>
							<div class="input-control checkbox"style="margin-top:10px">
								<label>
									<input type="checkbox"required="required"/>
									<span class="check"></span>
									We agree to the <a href="terms.php">Terms and Conditions</a>
								</label>
							</div>
							<br/><br/>
							<input style="float:right"type="submit" class="bg-emerald fg-white" value="Submit">
						</fieldset>
					
				</div>
			</form>
			</div>
		</div>
		<script>
		check_name = function(){
			var input=$('#orgname')[0].value=$('#orgname')[0].value.substring(0,15).toUpperCase();
			if (input!='') {
				with($('#checking')[0]){
					setAttribute('class','icon-busy fg-black');
					style.display='block';
				}
				$.get('check_name.php?name='+encodeURIComponent(input),function(data,status){
					var memory=input;
					if ($('#orgname')[0].value==memory){
						if (data=='OK') $('#checking')[0].setAttribute('class','icon-checkmark fg-emerald');
						else if (data=='KO') $('#checking')[0].setAttribute('class','icon-warning fg-red');
					}
				});
			} else $('#checking')[0].style.display='block';
		}
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
		</script>
<?php
include ("foot.php");
?>