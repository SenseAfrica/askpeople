<?php
@session_start();
set_time_limit(0);
include_once ('db.php');
include_once ('challenge/utils.php');
//bug solution
if (isset($_GET['node'])){
	$n=end($_SESSION['temp_path']);
	$n=$n['id'];
	if ($n!=$_GET['node']){
		include_once ('build_path.php');
		build_path($_SESSION['user']['org'],$_GET['node']);
	}
};
//var_dump(end($_SESSION['temp_path']));
//end of bugfix
if ((isset($_GET['cancel']))&&(empty($_POST))) unset($_SESSION['stored_form'],$_SESSION['page']);
if (((isset($_GET['insert']))||(isset($_POST['numbers'])))&&(isset($_SESSION['stored_form']))&&($_SESSION['page']=='new_form')) {
	//create table
	$tbname="form_{$_SESSION['user']['org']}_".($tbtime=time());
	$qry="CREATE TABLE `$tbname` ( `id` int(11) NOT NULL AUTO_INCREMENT  PRIMARY KEY, `submit_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,"/*."  `submit_id` VARCHAR(255) NOT NULL UNIQUE,`submit_location` VARCHAR(255) NOT NULL,"*/;
	$fields = json_decode($_SESSION['stored_form']['elts'],true);
	foreach ($fields as $field) switch ($field['type']){
		case 'tinyint':
			$qry.="`{$field['id']}` tinyint(1) NOT NULL,";
			break;
		case 'int':
		case 'varchar':
		case 'text':
		case 'date':
			$type=array('int'=>'int(11)','varchar'=>'varchar(255)','text'=>'text','date'=>'date');
			$qry.="`{$field['id']}` {$type[$field['type']]} ".(($field['req'])?'NOT':'DEFAULT')." NULL,";
			break;
		case 'enum':
			function query_escape($str){
				return mysqli_real_escape_string($db_conn, $str);
			}
			$qry.="`{$field['id']}` enum('".implode("','",array_map('query_escape',$field['values']))."') DEFAULT NULL,";
			break;
	}
	$qry=rtrim($qry,',').") ENGINE=MyISAM DEFAULT CHARSET=utf8";
	$res=mysqli_query($db_conn,$qry);
	if ($res){
		mysqli_query($db_conn,"UPDATE end_users SET credits = credits-1000 WHERE id = {$_SESSION['user']['org']} AND credits-reserve >999 LIMIT 1");
		if (mysqli_affected_rows($db_conn)){
			$last=end($_SESSION['temp_path']);
			
			$in_mail=false;
			$tag_open="<i id='HW_Mail'style='display:none'>";
			$tmp1=strpos($_SESSION['stored_form']['content'],$tag_open);
			if($tmp1!==false){
				$tmp+=strlen($tag_open);
				$in_mail=filter_var(substr($_SESSION['stored_form']['content'],$tmp,(strpos($_SESSION['stored_form']['content'],'</i>',$tmp)-$tmp)),FILTER_VALIDATE_EMAIL);
			}
			if(!$in_mail) $in_mail=$_SESSION['user']['email'];
			
			mysqli_query($db_conn,$qry="INSERT INTO forms_{$_SESSION['user']['org']} (name,tablename,node,public,fieldset,creator,mail) VALUES ('".
			mysqli_real_escape_string($db_conn,$_SESSION['stored_form']['name'])."','$tbtime',".
			((isset($_POST['numbers']))?'NULL,1':$last['id'].',0').",'".
			mysqli_real_escape_string($db_conn,$_SESSION['stored_form']['content'])."',{$_SESSION['user']['uid']},'".mysqli_real_escape_string($db_conn,$in_mail)."')");
			if ($id=mysqli_insert_id()){
				if (isset($_POST['numbers'])) {
					if ($id=mysqli_insert_id()){
						$tmp=array_filter(array_map('trim',explode(',',$_POST['numbers'])),'good_num');
						if (empty($tmp)) $alert="No valid number!";
						else {
							$okay=true;$free=-1;
							//var_dump($tmp);
							foreach($tmp as $num) {
								$num=preg_replace("/[^0-9]/", '', $num);
								if(!sms(((++$free<50)?0:$_SESSION['user']['org']),$num,"New survey: {$_SESSION['stored_form']['name']} by {$_SESSION['user']['name']}. Use survey-code: {$_SESSION['user']['org']}.{$id} to access it.",true,"include_once('mail_confirm.php');confirm('$in_mail','$num','{$_SESSION['stored_form']['name']}');")) $okay=false;
							}
							if ($okay)	$success="The form <b>{$_SESSION['stored_form']['name']}</b> was added and published.";
							else $warning="The form was added, but not all messages were sent out. Check your credit.";
						}
					}
				} else {
					include_once('shownode.php');
					if (show_node($last['id'],$last)) {
						$success="The form <b>{$_SESSION['stored_form']['name']}</b> was added and published.";
						$crit=json_decode($last['criteria'],true);
						$options=array();
						if(isset($crit['age'])) {
							$tmp=explode('-',$crit['age']);
							$options[]='birth >= '.(((int)date('Y'))-((int)$tmp[0])).' AND birth <= '.(((int)date('Y'))-((int)$tmp[0]));
						}
						if(isset($crit['sex'])) $options[]='gender = '.(($crit['sex']=='M')?1:0);
						$tags=array('ed','job','kids');
						for($i=0;$i<3;$i++) if(isset($crit[$tags[$i]])) for($j=0;$j<3;$j++) if(!in_array($j,$crit[$tags[$i]])) $options[]='NOT('.$tags[$i].' = '.$j.')';						
						$res=mysqli_query($db_conn,'SELECT id,phone FROM visitors WHERE '.((empty($options))?1:implode(' AND ',$options)).' ORDER BY RAND() LIMIT 0,50');
						if($ln=mysqli_fetch_array($res)){
							$res2=mysqli_query($db_conn,"SELECT (UNIX_TIMESTAMP(created) % 1000) AS secret FROM `forms_{$_SESSION['user']['org']}` WHERE id=$id");
							$l=mysqli_fetch_array($res2);
							do {
								sms(0,$ln['phone'],"New survey picked for you: {$_SESSION['stored_form']['name']} by {$_SESSION['user']['name']}. Use survey-code: {$_SESSION['user']['org']}.{$id}.".($l['secret']+$ln['id'])." to access it.");
							} while($ln=mysqli_fetch_array($res));
						}
						
					}
				}
				if(isset($success)){
					$msg=
"<html>
	<h1>New survey on AskForms!</h1>			
	<p>This email has been set as feedback-email for a survey titled \"{$_SESSION['stored_form']['name']}\".<br/>
	You can change that at any time from the platform.</p>
</html>";
					include_once('mail.php');
					HW_send($in_mail,'New Survey on AskPeople',$msg);
					unset($_SESSION['stored_form'],$_SESSION['page']);
					//$new_form=$id;
					include ('hierarchy.php');
					exit;
				}
			} else $error="There was an error in creating the survey table";
		} else {
			mysqli_query($db_conn,"DROP TABLE `$tbname`");
			if (!isset($alert)) $alert="Not enough credit!";
		}
	}else $alert="Unable to create form table. Please contact administrator.";
	unset($_SESSION['stored_form'],$_SESSION['page']);
}
$title="New Form";
if ((!isset($_SESSION['page']))||($_SESSION['page']!='new_form')){
	$node=(isset($_GET['node']))?$_GET['node']:$_SESSION['user']['node'];
	unset($_SESSION['stored_form']);
	$_SESSION['page']='new_form';
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

#pg1,#pg2 {padding:10px}
#formbuilder h3{color:white}
#pg2 button{margin:5px;}
<?php echo (isset($_SESSION['stored_form']))?'#formbuilder {display:none} #pg1':'#pg2';?>,#pg3,#pg3>div {display:none}
#pg3 form{padding:10px}
#pg3 form label i{float:right}
.showcase{padding:5px;padding-left:10px;padding-right:10px;background: #d9d9d9;color: black;}

#save_btn{visibility:hidden}

.showcase b{float:right}
fieldset li{
	padding: 10px;
	cursor:pointer;
    padding-bottom: 0;
}
fieldset li:hover{
	background-color: rgb(255,100,100);
	background-color: rgba(255,100,100,0.5);
    background-image: url(images/x.png);
    background-position: right top;
    background-repeat: no-repeat;
}
fieldset li:hover div{
	opacity:0.5;
	cursor:pointer;
}
</style>
<script>
/*
embed_submit=function(){
	//ready=true;
	$("#frm")[0].submit();
}
*/
sub = function (frm){
	if (typeof(frm.formname)!='undefined') {
		$("#tempform")[0].innerHTML="<legend>"+frm.formname.value.split('<').join('&lt;')+"</legend><fieldset>"+((frm.formemail.value!='<?php echo $_SESSION['user']['email'];?>')?("<i id='HW_Mail'style='display:none'>"+frm.formemail.value+"</i>"):'')+"</fieldset>";
		$("#pg1").fadeOut('slow',function(){$("#pg2").fadeIn('slow')});
		return false;
	}
}
add_elt = function (pos){
	$("#pg3")[0].children[pos].style.display='block';
	$("#pg2").fadeOut('slow',function(){$("#pg3").fadeIn('slow');$("#pg3")[0].children[pos].children[1].label.focus();$("#pg3")[0].scrollIntoView()})
}
cancel_input = function(pos){
	$('#pg3')[0].children[pos].children[1].reset();
	suggest[pos]='';
	$("#pg3").fadeOut('slow',function(){
		$("#pg2").fadeIn('slow');
		$('#pg3')[0].children[pos].style.display='none';
	})
	return false;
}
var suggest=['','','','','',''];
var form_elements=[];
var uniques=['submit_id','submit_date','submit_location'];
var auto_pos=-1;
var auto_anchor=0;
auto_id = function (id){
	auto_pos=id;
	auto_anchor=window.setInterval(function(){
		var pos=auto_pos;
		if(typeof($('#pg3')[0].children[pos])!='undefined'){
			var form=$('#pg3')[0].children[pos].children[1],unique=form.unique,label=form.label;
			//chars left, special case for MCQ
			$('#pg3')[0].children[pos].children[0].children[1].innerHTML=parseInt(form.maxLen.value)-((pos==4)?label.value.length+form.options.value.length+3*form.options.value.split('\n').length:label.value.length);
			if(unique.value==suggest[pos]){
				var allowed='abcdefghijklmnopqrstuvwxyz';
				var tmp=label.value.toLowerCase(),tmp2='',i,c;
				for (i=0;i<tmp.length;i++) {
					c = tmp.charAt(i);
					if (allowed.indexOf(c)>-1) tmp2+=c;
					else if ((c==' ')||(c=='_')){
						tmp2+=' ';
						i++;
						while ((i<tmp.length)&&((' _').indexOf(tmp.charAt(i))>-1)) i++;
						i--
					}
				}
				tmp=tmp2.split(' ').slice(0,3).join('_')
				if (uniques.indexOf(tmp)>-1) {
					i=2;
					while (uniques.indexOf(tmp+'_'+i)>-1) i++;
					tmp+=('_'+i);
				}
				unique.value=suggest[pos]=tmp;
			}
		}
	},200)
}
kill_auto = function (pos){
	if (pos==auto_pos){
		window.clearInterval(auto_anchor);
		auto_pos=-1;
	}
}
sub_new = function (pos){
	var form=$('#pg3')[0].children[pos].children[1],content='',i;
	if (uniques.indexOf(form.unique.value)>-1) {
		i=2;
		while (uniques.indexOf(form.unique.value+'_'+i)>-1) i++;
		form.unique.value+=('_'+i);
	}
	switch (pos){
		case 0://text
		case 3://integer
			content = '<label>'+form.label.value+'</label><div class="input-control text" data-role="input-control"><input type="'+((pos==3)?'number':'text')+'"'+/*((form.required.checked)?'required="required"':'')+'placeholder="'+form.placeholder.value+*/'placeholder="'+((pos==3)?'[number]':'[text]')+
			'"name="'+form.unique.value+'"><button class="btn-clear" tabindex="-1" type="button"></button></div>';
			form_elements.push({type:((pos==3)?'int':'varchar'),id:form.unique.value,req:/*form.required.checked*/false});
			break;
		case 1://textarea
			content = '<label>'+form.label.value+'</label><div class="input-control textarea"readonly="readonly"><textarea placeholder="'+form.placeholder.value+'"name="'+form.unique.value+'"'+((form.required.checked)?'required="required"':'')+'></textarea></div>';
			form_elements.push({type:'text',id:form.unique.value,req:form.required.checked});
			break;
		case 2://checkbox
			content = '<div class="input-control checkbox"><label><input name="'+form.unique.value+'"type="checkbox"'+((form.state.checked)?'checked="checked"':'')+' /><span class="check"></span>'+form.label.value+'</label></div>';
			form_elements.push({type:'tinyint',id:form.unique.value});
			break;
		case 4://checkbox
			content = '<label>'+form.label.value+'</label><div class="input-control select"><select name="'+form.unique.value+'">';
			var opt = form.options.value.split('\n'),opt2=[],letter=97;
			for (i=0;i<opt.length;i++) if(opt[i].length>0) {content += ('<option>'+opt[i]+'</option>');opt2.push(opt[i]);letter++}
			content += '</select></div>';
			form_elements.push({type:'enum',id:form.unique.value,values:opt2});
			break;
		case 5://date
			content ='<label>'+form.label.value+'</label><div class="input-control text" data-role="input-control"><input type="date"name="'+form.unique.value+'"'+((form.required.checked)?'required="required"':'')+'><button class="btn-clear" tabindex="-1" type="button"></button></div>';
			form_elements.push({type:'date',id:form.unique.value,req:form.required.checked});
			break;
	}
	uniques.push(form.unique.value);
	/*
	div=document.createElement('div');
	div.innerHTML=content;
	*/
	li=document.createElement('li');
	li.innerHTML='<div>'+content+'</div>';
	li.setAttribute('onclick','remove_element(this,"'+form.unique.value+'")');
	$("#tempform")[0].children[1].appendChild(li);
	$("#save_btn")[0].style.visibility='visible';
	cancel_input(pos);
	return false;
}
save_form=function(){
if ($("#embed")[0].style.display!='block'){
		if ($("#tempform")[0].children[1].children.length==0) $.Notify({caption: 'Alert!',content: 'Your form is still empty!',timeout: 5000,style: {background: 'red', color: 'white'}});
		else {
			var form=$("#frm")[0], items=$("#tempform li"),i;
			for (i=0;i<items.length;i++) items[i].removeAttribute('onclick');
			$.post("temp_form.php", {
				name:$("#tempform")[0].children[0].innerHTML,
				content:$("#tempform")[0].children[1].innerHTML,
				elts:JSON.stringify(form_elements)
				}, function(){});
			$("#formbuilder")[0].style.display="none";
			$("#embed_cover")[0].style.display="block";
			$("#embed").fadeIn("slow",function(){/*$("#embed")[0].scrollIntoView()*/document.body.scrollTop=0});
		}
		return true;
	} else return false;
}
remove_element=function(li,uniq){
	var pos;
	if (confirm("Delete this question?")){
		if ((pos=uniques.indexOf(uniq))>-1) uniques.splice(pos,1);
		li.parentNode.removeChild(li);
		for (i=0;i<form_elements.length;i++) if (form_elements[i].id==uniq) {form_elements.splice(i,1); break;}
	}
}
</script>

		<div class="grid">
			<div class="row">
				<div class="span4"style="position:relative;padding:10px">
				<h1>New Survey</h1>
					<!--tempform was originally a div-->
					<ol id="tempform"><?php
						if (isset($_SESSION['stored_form'])) echo "<legend>{$_SESSION['stored_form']['name']}</legend><fieldset>{$_SESSION['stored_form']['content']}</fieldset>";
					?></ol>
					<div id="formbuilder"class="ribbed-blue fg-white">
					<div id="pg1">
						<form onsubmit="return sub(this)">
						<label>Survey title</label>
						<div class="input-control text" data-role="input-control">
								<input id="username"type="text" placeholder="Survey title"name="formname"required="required"autofocus=""/>
								<button class="btn-clear"  type="button"></button>
						</div>
						<label>E-mail for alerts</label>
						<div class="input-control text" data-role="input-control">
								<input type="email" placeholder="optional"name="formemail"required=""value="<?php echo $_SESSION['user']['email'];?>"autofocus=""/>
								<button class="btn-clear"  type="button"></button>
						</div>
						<input type="submit"value="Start"/>
						</form>
					</div>
					<div id="pg2">
					<h3>Add a question</h3>
					<button onclick="add_elt(0)">text</button><button onclick="add_elt(3)">integer</button><button onclick="add_elt(4)">multi-choice</button>
					<h3>... or finish editing</h3>
					<a href="?cancel=yes"><button class="fg-white bg-red">Cancel</button></a>
					<button id="save_btn"class="fg-white bg-emerald"onclick="save_form()">Save</button>
					</div>
					<div id="pg3">
						<div>
							<div class="showcase">
							<!--
									<label>Label</label>
									<div class="input-control text" data-role="input-control">
										<input type="text" placeholder="placeholder"readonly="readonly">
										<button class="btn-clear" tabindex="-1" type="button"></button>
									</div>
									<small><i>Example above &middot; Max. length: 255 characters</i></small>
							--><i>Free-form text question</i><b></b>
							</div>
							<form>
								<label><span>Question text</span></label>
								<input type="hidden"name="maxLen"value="150"/>
								<div class="input-control textarea">
										<textarea placeholder="type question"required="required"name="label"></textarea>
								</div>
								<!--
								<div class="input-control switch">
									<label>
										Required&nbsp;
										<input type="checkbox"name="required"/>
										<span class="check"></span>
									</label>
								</div>
								<label>Placeholder [optional]</label>
								<div class="input-control text" data-role="input-control">
									<input name="placeholder"type="text" placeholder="type placeholder">
									<button class="btn-clear" tabindex="2" type="button"></button>
								</div>
								-->
								<label>Database identifier</label>
								<div class="input-control text" data-role="input-control">
									<input name="unique"type="text" placeholder="unique identifier"required="required">
									<button class="btn-clear" tabindex="3" type="button"></button>
								</div>
								<button onclick="cancel_input(0)"class="fg-white bg-red">Cancel</button>
								<input class="fg-white bg-emerald"value="Add"type="submit"/>
							</form>
						</div>
						<div>
							<div class="showcase">
								<label>Label</label>
								<div class="input-control textarea"readonly="readonly">
									<textarea placeholder="placeholder"></textarea>
								</div>
								<small><i>Example above</i></small>
							</div>
							<form>
								<label>Label</label>
								<div class="input-control text" data-role="input-control">
									<input name="label"required="required"type="text" placeholder="type label">
									<button class="btn-clear" tabindex="1" type="button"></button>
								</div>
								<div class="input-control switch">
									<label>
										Required&nbsp;
										<input type="checkbox"name="required"/>
										<span class="check"></span>
									</label>
								</div>
								<label>Placeholder [optional]</label>
								<div class="input-control text" data-role="input-control">
									<input name="placeholder"type="text" placeholder="type placeholder">
									<button class="btn-clear" tabindex="2" type="button"></button>
								</div>
								<label>Identifier</label>
								<div class="input-control text" data-role="input-control">
									<input name="unique"required="required"type="text" placeholder="unique identifier">
									<button class="btn-clear" tabindex="3" type="button"></button>
								</div>
								<button onclick="cancel_input(1)"class="fg-white bg-red">Cancel</button>
								<input class="fg-white bg-emerald"value="Add"type="submit"/>
							</form>
						</div>
						<div>
							<div class="showcase">
								<div class="input-control checkbox">
									<label>
										<input type="checkbox"checked="checked" />
										<span class="check"></span>
										Label
									</label>
								</div>
								<small><i>Example above</i></small>
							</div>
							<form>
								<label>Label</label>
								<div class="input-control text" data-role="input-control">
									<input name="label"required="required"type="text" placeholder="type label">
									<button class="btn-clear" tabindex="1" type="button"></button>
								</div>
								<div class="input-control switch">
									<label>
										Checked by default&nbsp;
										<input type="checkbox"name="state"/>
										<span class="check"></span>
									</label>
								</div>
								<label>Identifier</label>
								<div class="input-control text" data-role="input-control">
									<input name="unique"required="required"type="text" placeholder="unique identifier">
									<button class="btn-clear" tabindex="3" type="button"></button>
								</div>
								<button onclick="cancel_input(2)"class="fg-white bg-red">Cancel</button>
								<input class="fg-white bg-emerald"value="Add"type="submit"/>
							</form>
						</div>
						<div>
							<div class="showcase">
								<!--
								<label>Label</label>
								<div class="input-control text" data-role="input-control">
									<input type="number" placeholder="placeholder"readonly="readonly">
									<button class="btn-clear" tabindex="-1" type="button"></button>
								</div>
								<small><i>Example above &middot; Value range: -2^31 to 2^31</i></small>
								--><i>Numerical (integer) question</i><b></b>
							</div>
							<form>
								<label><span>Question text</span></label>
								<input type="hidden"name="maxLen"value="150"/>
								<div class="input-control textarea">
										<textarea placeholder="type question"required="required"name="label"></textarea>
								</div>
								<!--
								<div class="input-control switch">
									<label>
										Required&nbsp;
										<input type="checkbox"name="required"/>
										<span class="check"></span>
									</label>
								</div>
								<label>Placeholder [optional]</label>
								<div class="input-control text" data-role="input-control">
									<input name="placeholder"type="text" placeholder="type placeholder">
									<button class="btn-clear" tabindex="2" type="button"></button>
								</div>
								-->
								<label>Database identifier</label>
								<div class="input-control text" data-role="input-control">
									<input name="unique"required="required"type="text" placeholder="unique identifier">
									<button class="btn-clear" tabindex="3" type="button"></button>
								</div>
								<button onclick="cancel_input(3)"class="fg-white bg-red">Cancel</button>
								<input class="fg-white bg-emerald"value="Add"type="submit"/>
							</form>
						</div>
						<div>
							<div class="showcase">
								<!--
								<label>Label</label>
								<div class="input-control select">
									<select>
										<option></option>
										<option>Value 1</option>
										<option>Value 2</option>
										<option>Value 3</option>
									</select>
								</div>
								<small><i>Example above</i></small>
								--><i>Multi-choice question</i><b></b>
							</div>
							<form>
								<label><span>Question text</span></label>
								<input type="hidden"name="maxLen"value="150"/>
								<div class="input-control textarea">
										<textarea placeholder="type question"required="required"name="label"></textarea>
								</div>
								<label>Options (type one per line)</label>
								<div class="input-control textarea"readonly="readonly">
									<textarea required="required"name="options"></textarea>
								</div>
								<label>Database identifier</label>
								<div class="input-control text" data-role="input-control">
									<input name="unique"required="required"type="text" placeholder="unique identifier">
									<button class="btn-clear" tabindex="3" type="button"></button>
								</div>
								<button onclick="cancel_input(4)"class="fg-white bg-red">Cancel</button>
								<input class="fg-white bg-emerald"value="Add"type="submit"/>
							</form>
						</div>
						<div>
							<div class="showcase">
								<label>Label</label>
								<div class="input-control text" data-role="input-control">
									<input type="date">
									<button class="btn-clear" tabindex="-1" type="button"></button>
								</div>
								<small><i>Example above</i></small>
							</div>
							<form>
								<label>Label</label>
								<div class="input-control text" data-role="input-control">
									<input name="label"required="required"type="text" placeholder="type label">
									<button class="btn-clear" tabindex="1" type="button"></button>
								</div>
								<div class="input-control switch">
									<label>
										Required&nbsp;
										<input type="checkbox"name="required"/>
										<span class="check"></span>
									</label>
								</div>
								<label>Identifier</label>
								<div class="input-control text" data-role="input-control">
									<input name="unique"required="required"type="text" placeholder="unique identifier">
									<button class="btn-clear" tabindex="3" type="button"></button>
								</div>
								<button onclick="cancel_input(5)"class="fg-white bg-red">Cancel</button>
								<input class="fg-white bg-emerald"value="Add"type="submit"/>
							</form>
						</div>
					</div>
					</div>
					<div id="embed_cover"class="embeds ribbed-emerald"></div>
				</div>
				<div class="span8 embeds"style="position:relative;padding:10px" id="embed">
				 <?php 
					$embedded='form';
					include ('hierarchy.php');
				?>
				</div>
			</div>
		</div>
<script>
var i=0,frm;
for (;i<$('#pg3')[0].children.length;i++){
	frm=$('#pg3')[0].children[i].children[1];
	frm.setAttribute("onsubmit","return sub_new("+i+")");
	frm.label.setAttribute("onfocus","auto_id("+i+")");
	frm.label.setAttribute("onblur","kill_auto("+i+")");
	if(typeof(frm.options)!='undefined'){
		frm.options.setAttribute("onfocus","auto_id("+i+")");
		frm.options.setAttribute("onblur","kill_auto("+i+")");
	}
}
</script>
<?php
include ("foot.php");
?>