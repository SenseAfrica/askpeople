<?php 
@session_start();
include_once ('db.php');
include_once ('build_path.php');

function go_away(){
	//include ('index.php');
	echo "some error occured in the code";
	exit();
}


if(!isset($node)){
	if (isset($_GET['node'])) $node=$_GET['node'];
	else if (isset($_SESSION['temp_path'])) {$node=end($_SESSION['temp_path']);$node=$node['id'];}
	else if (isset($_SESSION['user'])) $node=$_SESSION['user']['node'];
	else go_away();
}

if ((!isset($_SESSION['user']))||(is_null($_SESSION['user']['org']))||(!is_array($path=build_path($_SESSION['user']['org'],$node)))) go_away();

$last=end($_SESSION['temp_path']);
if (isset($_POST['new_node'])) {
	$new_crit=array();
	$criteria=json_decode($last['criteria'],true);
	$tmp=($_POST['age'])?$_POST['age']:((isset($criteria['age']))?$criteria['age']:false);
	if ($tmp){
		function okay ($test){return in_array($test, array('13','20','30','50','150'));}
		$tmp2=array_filter(explode('-',$tmp), "okay");
		if ((count($tmp2)==2)&&($tmp2[0]<$tmp2[1])) $new_crit['age']=$tmp;
	}
	if (isset($criteria['sex'])) $new_crit['sex']=$criteria['sex'];
	else if ((isset($_POST['sex']))&&(in_array($_POST['sex'],array('M','F')))) $new_crit['sex']=$_POST['sex'];
	foreach(array('ed','job','kids') as $item){
		global $new_crit, $criteria;
		$arr=array();
		for($i=0;$i<3;$i++) if ((isset($_POST[$item.'_'.$i]))&&($_POST[$item.'_'.$i]=='on')&&((!isset($criteria[$item]))||(in_array($i,$criteria[$item])))) $arr[]=$i;
		if(!empty($arr)) $new_crit[$item]=$arr;
		else if (isset($criteria[$item])) $new_crit[$item]=$criteria[$item];
	}
	$res=mysqli_query($db_conn,"INSERT INTO nodes_{$_SESSION['user']['org']} (parent,name,criteria) VALUES ($node,'".mysqli_real_escape_string($db_conn,ucwords(strtolower($_POST['new_node'])))."','".json_encode($new_crit)."')");
	if (($res)&&(mysqli_affected_rows($db_conn))) {
		$success = "\"{$_POST['new_node']}\" sub-unit was created";
	}
	else $alert="Database Error. Please Try Again Later.";
} else if (isset($_GET['delete'])) {
	$del = (int) $_GET['delete'];
	if (is_numeric($last['id'])) {
		//check valid
		$res = mysqli_query($db_conn,"SELECT id FROM nodes_{$_SESSION['user']['org']} WHERE id = $del AND parent = {$last['id']} LIMIT 0,1");
		if (mysqli_num_rows($res)){
			$to_del=array();
			$parents=array($del);
			$children=array();
			while (!empty($parents)) {
				$res=mysqli_query($db_conn,"SELECT id FROM nodes_{$_SESSION['user']['org']} WHERE parent IN (".implode(',',$parents).')');
				while ($line=mysqli_fetch_assoc($res)) $children[]=$line['id'];
				$to_del=array_merge($to_del,$parents);
				$parents=$children;
				$children=array();
			}
			$to_del=implode(',',$to_del);
			mysqli_query($db_conn,"DELETE FROM nodes_{$_SESSION['user']['org']} WHERE id IN ($to_del)");
			mysqli_query($db_conn,"UPDATE users SET node = {$last['id']} WHERE node IN ($to_del)");
			mysqli_query($db_conn,"UPDATE forms SET node = {$last['id']} WHERE node IN ($to_del)");
		} else $error = "Please check your input.";
	} else $error = "Please check your input.";
}

if(!isset($embedded)){
$title="Hierarchy";
include_once ("head.php");
}
?>
<style>
.subnodes h2 button {height:30px;font-size:20px;width:30px;float:right;margin:0px;position:relative;font-size: 20px;padding: 0;}
.subnodes button {height:50px;font-size:20px;width:100%;margin-bottom:10px;position:relative}
.subnodes button b{float:right}
.subnodes button div{height:40px;top:5px;left:5px;font-size:18px;width:40px;float:left;background:transparent;line-height: 40px;position:absolute;color:white!important}
.subnodes button div:hover{background:#555}
.subnodes button i:nth-last-child(2){position: absolute;top: 2px;left: 2px;font-size: 12px;}
#set_criteria div {width:100%;font-family: monospace;/*background: rgb(230,230,230);padding-left: 4px;*/}
#set_criteria div label{float:left;position:relative;width:100px;margin:0;padding:0}
#set_criteria label{margin:0;}
#age_range label{width:50px!important}
#age_range label div{width:30px;border-top:2px solid transparent;position:absolute;top:12px;left:20px}
.hide {display:none}
</style>
		<h1><?php
			if (isset($embedded)) {
				if ($embedded=='agent') echo 'Assign to Market unit';
				else if ($embedded=='viewform') echo 'Focus on a Segment [beta]';
				else if ($embedded=='form') echo 'Send to known numbers</h1><div class="bg-grayLighter padding20"style="padding-right:148px;padding-top:10px"><form method="POST"style="margin:0"><small style="position:relative;bottom:5px">Use this for internal surveys or pre-broadcast tests</small><input type="text" placeholder="Enter phone numbers (ex +99... ), separated by commas"required="required"name="numbers"style="position:relative;width:100%;height:26px;font-family: monospace;font-size: 14px;"><input type="submit"style="position:absolute;width: 55px;right: 99px;height: 26px;"class="bg-emerald fg-white"value="Send"/><button onclick="window.location=\'?cancel=yes\'"style="position:absolute;width:65px;right: 30px;height: 26px;"class="bg-red fg-white">Cancel</button></form></div><h1>...or Let us broadcast it';
			}else echo 'Main dashboard';
		?></h1><br/>
		<p class="bg-grayLighter padding20"><b>
			<?php
				$out=array(); $i=0;
				foreach ($_SESSION['temp_path'] as $step) {
					$out[]="<a href='?node={$step['id']}'>{$step['name']}</a>";
					$i++;
				}
				echo implode(' &gt; ',$out);
				if ((isset($embedded))&&($embedded!='viewform')) echo '&nbsp;<a href="?insert=yes"><button onclick="embed_submit()"class="bg-emerald fg-white">Assign '.$embedded.'</button></a>&nbsp;'.((($embedded=='agent')&&($i==1))?'<a href="?insert=yes&admin=yes"><button class="bg-amber fg-white">Add as admin</button></a>&nbsp;':'').'<a href="?cancel=yes"><button class="bg-red fg-white">Cancel</button></a>';
			?>
		</b></p>
			<div class="grid"<?php if((isset($embedded))&&($embedded=='viewform'))echo"style='display:none'"; ?>>
				<br/>
				<legend style="line-height:12pt">In the <b><?php echo addslashes($last['name']); ?></b> market-unit:<br/><br/><small><?php
					$criteria=json_decode($last['criteria'],true);
					include('account_options.php');
					function verbify ($item){
						global $criteria, $options;
						$arr=array();
						foreach ($criteria[$item] as $pos) $arr[]=$options[$item][$pos];
						return join(', ',$arr);
					}
					echo '<b>age:</b> '.((isset($criteria['age']))?$criteria['age']:'any').
					' <b>&middot; gender:</b> '.((isset($criteria['sex']))?$criteria['sex']:'any').
					' <b>&middot; education:</b> '.((isset($criteria['ed']))?verbify('ed'):'any').
					' <b>&middot; occupation:</b> '.((isset($criteria['job']))?verbify('job'):'any').
					' <b>&middot; family:</b> '.((isset($criteria['kids']))?verbify('kids'):'any');
				?></small></legend>
                <div class="row"align="center">
                    <div class="span4 subnodes">
					<h2>Market sub-units <button title='add sub-unit'onclick='javascript:add_node("<?php echo addslashes($last['name']); ?>")'class="bg-gray fg-white"><i class="icon-plus"></i></button></h2>
						<?php
							$res=mysqli_query($db_conn,"SELECT id, name FROM nodes_{$_SESSION['user']['org']} WHERE parent = ".$last['id'].' ORDER BY id DESC');
							while($line=mysqli_fetch_assoc($res)){
							 echo 
							 "<a href='?node={$line['id']}'><button class='bg-lightBlue fg-white'>".
								((isset($embedded))?'':"<div title='delete this unit'onclick='return remove_node({$line['id']},\"".addslashes($line['name'])."\",\"".addslashes($last['name'])."\")'><i class='icon-remove'></i></div>").
								"{$line['name']} <b>&hellip;</b>
                            </button></a>";
							}
						?>							
                    </div>
					<?php if(!isset($embedded)){ ?>
					<div class="span4 subnodes">
					<h2>Assigned agents <a href='new_agent.php?node=<?php echo addslashes($last['id']); ?>'><button title='add agent to "<?php echo addslashes($last['name']); ?>"'class="bg-gray fg-white"><i class="icon-plus"></i></button></a></h2>
						<?php
							$res=mysqli_query($db_conn,'SELECT uid, realname, admin FROM users WHERE org = '.$_SESSION['user']['org'].' AND node = '.$last['id'].' AND active = 1 ORDER BY admin DESC, uid DESC');
							while($line=mysqli_fetch_assoc($res)){
								$name=$line['realname'];
							 echo 
							 "<a href='view_agent.php?id={$line['uid']}'><button class='bg-".(($line['admin'])?'amber':'magenta')." fg-white'>$name<b>&hellip;</b></button></a>";
							}
						?>							
                    </div>
					<div class="span4 subnodes">
					<h2>Surveys <a href='new_form.php?node=<?php echo addslashes($last['id']); ?>'><button title='send form to "<?php echo addslashes($last['name']); ?>"'class="bg-gray fg-white"><i class="icon-plus"></i></button></a></h2>
						<?php
							$res=mysqli_query($db_conn,"SELECT forms_{$_SESSION['user']['org']}.id, active, name, COUNT(submissions_{$_SESSION['user']['org']}.id) AS submissions FROM forms_{$_SESSION['user']['org']} LEFT JOIN submissions_{$_SESSION['user']['org']} ON forms_{$_SESSION['user']['org']}.id=form WHERE node = {$last['id']} GROUP BY forms_{$_SESSION['user']['org']}.id ORDER BY active DESC, id DESC");
							while($line=mysqli_fetch_assoc($res)){
							 echo 
							 "<a href='view_form.php?id={$line['id']}'><button class='bg-".(($line['active'])?'emerald':'gray')." fg-white'>{$line['name']}<i class='icon-copy'> {$line['submissions']}</i><b>&hellip;</b>
                            </button></a>";
							}
						?>							
                    </div>
					<?php } ?>
                </div>
			</div>
<script>
range_sel=function(radio,pos){
	if(!radio.disabled){
		var HA=$('#hidden_age')[0];
		if(!radio.checked){
			$('#age_range div').attr('style','border-color:transparent');
			$('#age_range span').attr('style','background-color:transparent');
			HA.value='';
		} else {
			num=([13,20,30,50,150])[pos];
			if (HA.value){
				var divs=$('#age_range div');
				var dots=$('#age_range input');
				var spans=$('#age_range span');
				var dark=false;
				if (HA.value.indexOf('-')>0){
					bounds=HA.value.split('-').map(function(x){return ([13,20,30,50,150]).indexOf(parseInt(x));});
					$('#age_range div').attr('style','border-color:transparent');
					$('#age_range span').attr('style','background-color:transparent');
					dots[bounds[(pos==bounds[0])?0:1]].checked=false;
					HA.value=([13,20,30,50,150])[bounds[(pos==bounds[0])?1:0]];
					if (pos!=bounds[(pos==bounds[0])?0:1]) $('#hidden_age')[0].value=([parseInt(HA.value),num]).sort().join('-');
				} else $('#hidden_age')[0].value=([parseInt(HA.value),num]).sort().join('-');
				if (HA.value.indexOf('-')>0) for (var i=0;i<5;i++){
					if (dots[i].checked) dark=!dark;
					if (dark) {
						divs[i].style.borderColor='rgb(216,216,216)';
						if (!dots[i+1].checked) spans[i+1].style.backgroundColor='rgb(216,216,216)';
					}
				}
			}
			else $('#hidden_age')[0].value=num;
		}
	}
}
add_node=function(parent_name){
    $.Dialog({
                                overlay: true,
                                shadow: true,
                                flat: true,
                                draggable: true,
                                padding: 10,
                                onShow: function(_dialog){
                                    var content = '<form method="POST"id="set_criteria">'+
'<div class="input-control text">'+
    '<input type="text" name="new_node"value=""required="required" placeholder="Name of sub-unit"/>'+
    '<button class="btn-clear"></button>'+
'</div>'+
<?php
$border=(isset($criteria['age']))?explode('-',$criteria['age']):array(0,151); 
?>
'<label>Age range<br/><small>pick boundary values or leave blank to accept all</small></label>'+
'<input id="hidden_age"name="age"type="hidden"/>'+
'<div class="input-control radio default-style"id="age_range">'+
	'<label>'+
		'<div></div>'+
		'<input type="radio" onclick="range_sel(this,0)" <?php if ($border[0]>13) echo 'class="hide"disabled';?>/>'+
		'<span class="check"></span><br/>&lt;20'+
	'</label>'+
	'<label>'+
		'<div></div>'+
		'<input type="radio" onclick="range_sel(this,1)" <?php if ($border[0]>20) echo 'class="hide"disabled';?>/>'+
		'<span class="check"></span><br/>20'+
	'</label>'+
	'<label>'+
		'<div></div>'+
		'<input type="radio" onclick="range_sel(this,2)" <?php if (($border[0]>30)||($border[1]<30)) echo 'class="hide"disabled';?>/>'+
		'<span class="check"></span><br/>30'+
	'</label>'+
	'<label>'+
		'<div></div>'+
		'<input type="radio" onclick="range_sel(this,3)" <?php if ($border[1]<50) echo 'class="hide"disabled';?>/>'+
		'<span class="check"></span><br/>50'+
	'</label>'+
	'<label>'+
		'<input type="radio" onclick="range_sel(this,4)" <?php if ($border[1]<150) echo 'class="hide"disabled';?>/>'+
		'<span class="check"></span><br/>&gt;50'+
	'</label>'+
'</div>'+
<?php if (!isset($criteria['sex'])){ ?>
'<label>Gender<br/><small>pick one or leave blank to accept all</small></label>'+
'<div class="input-control radio default-style">'+
	'<label>'+
		'<input type="radio" name="sex"value="M"/>'+
		'<span class="check"></span> Man'+
	'</label>'+
	'<label>'+
		'<input type="radio" name="sex"value="F"/>'+
		'<span class="check"></span> Woman'+
	'</label>'+
'</div>'+
<?php
}
$names=array('Education','Occupation','Family situation');
$items=array('ed','job','kids');
for($j=0;$j<3;$j++){
$tmp=(isset($criteria[$items[$j]]))?$criteria[$items[$j]]:array(0,1,2); 
?>
'<label><?php echo $names[$j];?><br/><small>pick any number of values</small></label>'+
'<div class="input-control checkbox">'+
	<?php
	for ($i=0;$i<3;$i++){
	?>
	'<label>'+
		'<input name="<?php echo $items[$j].'_'.$i;?>"type="checkbox"<?php echo (in_array($i, $tmp))?'checked':'class="hide"disabled';?>/>'+
		'<span class="check"></span><br/><?php echo $options[$items[$j]][$i]?>'+
	'</label>'+
	<?php
	}
	?>
'</div>'+
<?php
}
?>
'<input type="submit"class="bg-emerald fg-white"/>&nbsp;&nbsp;'+
							'<button class="button" type="button" onclick="$.Dialog.close()">Cancel</button>'+
							'</form>';
                                    $.Dialog.title('New sub-unit ( within <b>'+parent_name+'</b> )');
                                    $.Dialog.content(content);
                                }
                            });					
}
remove_node=function(id,node_name,parent_name){
    $.Dialog({
                                overlay: true,
                                shadow: true,
                                flat: true,
                                draggable: true,
                                padding: 10,
                                onShow: function(_dialog){
                                    var content = 
							'<p>This will delete the "'+node_name+'" sub-unit</p>'+
'<p><b>All Forms and Users attached to this unit and its sub-units shall be transferred to "'+parent_name+'"</b></p>'+
'<a href="?delete='+id+'"><button class="bg-red fg-white">Delete</button></a>&nbsp;&nbsp;'+
							'<button class="button" type="button" onclick="$.Dialog.close()">Cancel</button>';
                                    $.Dialog.title('Delete sub-unit');
                                    $.Dialog.content(content);
                                }
                            });			
return false;
}
</script>
<?php
if(!isset($embedded)) include ("foot.php");
?>