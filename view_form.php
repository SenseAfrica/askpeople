<?php
include_once('db.php');
@session_start();

$_SESSION['last_form_id']= $in_id= (isset($_GET['id']))?$_GET['id']:((isset($_SESSION['last_form_id']))?$_SESSION['last_form_id']:false);

if ((is_numeric($in_id)) && ($res=mysqli_query($db_conn,"SELECT deactivator, alerter, phone,public, forms_{$_SESSION['user']['org']}.name, tablename,mail, forms_{$_SESSION['user']['org']}.node, forms_{$_SESSION['user']['org']}.active, fieldset, forms_{$_SESSION['user']['org']}.created, realname, submissions FROM forms_{$_SESSION['user']['org']}, users, form_view_{$_SESSION['user']['org']} WHERE forms_{$_SESSION['user']['org']}.id = {$in_id} AND creator = uid AND forms_{$_SESSION['user']['org']}.id=form_view_{$_SESSION['user']['org']}.id LIMIT 0,1")) && ($form=mysqli_fetch_assoc($res))){
	$tbname='form_'.$_SESSION['user']['org'].'_'.$form['tablename'];
	if ($res=mysqli_list_fields ($sql_details['db'],$tbname)){
		$_SESSION['formFields']=array();
		$i=0;
		$_SESSION['formId']=$form['tablename'];
		while (@$field=mysqli_field_name($res,$i)){
			$i++;
			if ($field=='submit_node') $_SESSION['formFields'][]='sub-unit';
			else if (($field!='submit_id')&&($field!='submit_location')) $_SESSION['formFields'][]=$field;
		}
	} else {
		include ("head.php");
		echo ("<br/><h2>Sorry, a database error occured.</h2>");
		include ("foot.php");
		exit;
	}
} else {
	include ("head.php");
	echo ("<br/><h2>Sorry, the form requested does not exist.</h2>");
	include ("foot.php");
	exit;
}



if(isset($_POST['status'])){
	$_POST['public']=((isset($_POST['public']))&&($_POST['public']=='on'))?1:0;
	if($form['public']!=$_POST['public']) {
		mysqli_query($db_conn,'UPDATE forms_'.$_SESSION['user']['org'].' SET public = '.$_POST['public'].' WHERE id='.$in_id);
		$form['public']=$_POST['public'];
	}
	if($_POST['target']!=$form['node']) {
		$_POST['target']=(int)$_POST['target'];
		include_once('shownode.php');
		if(show_node($_POST['target'])){
			mysqli_query($db_conn,'UPDATE forms_'.$_SESSION['user']['org'].' SET node = '.$_POST['target'].' WHERE id='.$in_id);			
			check_node($form['node']);
			$form['node']=$_POST['target'];
		}
	}
	if($_POST['status']=='0'){
		if ($form['active']) {
			include_once('shownode.php');
			mysqli_query($db_conn,'UPDATE forms_'.$_SESSION['user']['org'].' SET active = 0 WHERE id='.$in_id);
			check_node($form['node']);
			$form['active']=0;
		}
	} else if (!$form['active']) {
		include_once('shownode.php');
		mysqli_query($db_conn,'UPDATE forms_'.$_SESSION['user']['org'].' SET active = 1 WHERE id='.$in_id);
		show_node($form['node']);
		$form['active']=1;
	}
	
	if($_POST['deactivate']=='0') {
		if ($form['deactivator']) {
			mysqli_query($db_conn,'UPDATE forms_'.$_SESSION['user']['org'].' SET deactivator = 0 WHERE id='.$in_id);
			$form['deactivator']=0;
		}
	} else {
		$form['deactivator']=max(0,min((int)$_POST['deactivator'],50000));
		mysqli_query($db_conn,'UPDATE forms_'.$_SESSION['user']['org'].' SET deactivator = '.$form['deactivator'].' WHERE id='.$in_id);
	}
	if($_POST['alert']=='0') {
		if ($form['alerter']) {
			mysqli_query($db_conn,'UPDATE forms_'.$_SESSION['user']['org'].' SET alerter = 0 WHERE id='.$in_id);
			$form['alerter']=0;
		}
	} else {
		$form['alerter']=max(0,min((int)$_POST['alerter'],1000));		
		mysqli_query($db_conn,'UPDATE forms_'.$_SESSION['user']['org'].' SET alerter = '.$form['alerter'].' WHERE id='.$in_id);
	}
	$_POST['phone']=(int)preg_replace("/[^0-9]/", '', $_POST['phone']);
	if(!$_POST['phone']){
		if($form['phone']) {
			mysqli_query($db_conn,'UPDATE forms_'.$_SESSION['user']['org'].' SET phone = NULL WHERE id='.$in_id);
			$form['phone']=null;
		}
	}else {
		if($_POST['phone']!=$form['phone']){
			mysqli_query($db_conn,'UPDATE forms_'.$_SESSION['user']['org'].' SET phone = '.$_POST['phone'].' WHERE id='.$in_id);
			$form['phone']=$_POST['phone'];
		}
	}
	
	$_POST['mail']=filter_var($_POST['mail'],FILTER_VALIDATE_EMAIL);
	if (($_POST['mail'])&& ($_POST['mail']!=$form['mail'])){
		mysqli_query($db_conn,'UPDATE forms_'.$_SESSION['user']['org'].' SET mail = '.$_POST['mail'].' WHERE id='.$in_id);
		$form['mail']=$_POST['mail'];
	}
	$success="Form settings were updated.";
}



$title=$form['name'];
include ("head.php");
?>
<script src="js/jquery/jquery.dataTables.js"></script>
<link href="css/jquery.dataTables.css" rel="stylesheet">
<?php
	echo "<h1>{$form['name']}</h1>";
	echo "<h3><small>created by:</small> {$form['realname']}&emsp;<small>created on:</small> {$form['created']}&emsp;<small>status:</small> ".(($form['active'])?'active':'inactive')."&emsp;<small>total submissions:</small> {$form['submissions']}</h3>";
?>
<br/>
<div class="tab-control" data-role="tab-control">
    <ul class="tabs">
        <li class="active"><a href="#_page_1">Statistics <i class="icon-chart-alt"></i></a></li>
        <li><a href="#_page_2">Raw data <i class="icon-database"></i></a></li>
        <li><a href="#_page_3">Market focus <i class="icon-search"></i></a></li>
        <li class="place-right"><a href="#_page_4">Admin <i class="icon-cog"></i></a></li>
    </ul>
 
    <div class="frames">
        <div class="frame" id="_page_1">
		<?php if (/*$form['submissions']>0*/true) {?>
		<script src="js/jquery.animateNumber.min.js"></script>
		<style>
			#canvas-holder {
				width: 100%;
				margin-top: 50px;
				text-align: center;
			}
			.statbox{border:1px solid gray;background:white;padding:10px}
			#runner{height:90px;margin-right:20px;margin-bottom:20px;position:relative}
			#runner b{float:left}
			#runner h1{float:right;visibility:hidden;padding-top:10px}
			#runner div{position:absolute;bottom:-40px;padding:5px;padding-left:10px;height:35px;width:100%;color:white;left:0}
			#runner div:last-child{bottom:-80px}
			
			#publicity .ribbed-teal{padding:5px!important}
			#publicity .ribbed-teal small{float:right}
			#pb1{margin-top:0;text-align:justify}
		</style>
		<?php
		if($charts=strpos($form['fieldset'],'div class="input-control select"><select name=')){?>
		<div id="chartjs-tooltip"></div>
		<script src="js/Chart.min.js"></script>
		<style>
			#chartjs-tooltip {
				opacity: 1;
				position: absolute;
				background: rgba(0, 0, 0, .7);
				color: white;
				padding: 3px;
				border-radius: 3px;
				-webkit-transition: all .1s ease;
				transition: all .1s ease;
				pointer-events: none;
				-webkit-transform: translate(-50%, 0);
				transform: translate(-50%, 0);
			}
			#chartjs-tooltip.below {
				-webkit-transform: translate(-50%, 0);
				transform: translate(-50%, 0);
			}
			#chartjs-tooltip.below:before {
				border: solid;
				border-color: #111 transparent;
				border-color: rgba(0, 0, 0, .8) transparent;
				border-width: 0 8px 8px 8px;
				bottom: 1em;
				content: "";
				display: block;
				left: 50%;
				position: absolute;
				z-index: 99;
				-webkit-transform: translate(-50%, -100%);
				transform: translate(-50%, -100%);
			}
			#chartjs-tooltip.above {
				-webkit-transform: translate(-50%, -100%);
				transform: translate(-50%, -100%);
			}
			#chartjs-tooltip.above:before {
				border: solid;
				border-color: #111 transparent;
				border-color: rgba(0, 0, 0, .8) transparent;
				border-width: 8px 8px 0 8px;
				bottom: 1em;
				content: "";
				display: block;
				left: 50%;
				top: 100%;
				position: absolute;
				z-index: 99;
				-webkit-transform: translate(-50%, 0);
				transform: translate(-50%, 0);
			}
		</style>
		<?php } ?>
		<div class="grid">
			<div class="row"style="margin-top:0">
				<div class="span8 statbox">
					<div id="div_g" style="height:148px;"></div>
					<script src="js/dygraph-combined.js"></script>
					<script>
					<?php
						$res=mysqli_query($db_conn,"SELECT * FROM stats_{$_SESSION['user']['org']} WHERE form = $in_id "/*.'AND node IN ('.implode(',',$_SESSION['selected_nodes']).')'*/);
						$graph=array();
						while ($line=mysqli_fetch_assoc($res)) $graph[$line['hour']]=$line['count'];
						if (!empty($graph)){
							$out="Hour,Submissions\\n";
							foreach ($graph as $time=>$datum) $out.=date('Y-m-d H:i:s',($time*60*60)).",$datum\\n";
							?>
							var graph_anchor=window.setInterval((function () {
								if (typeof(Dygraph)!='function') return;
								clearInterval(graph_anchor);
								new Dygraph(document.getElementById("div_g"), "<?php echo $out; ?>",{
									ylabel: 'responses per hour',
									drawPoints: true
								});
								/* It sucks that these things aren't objects, and we need to store state in window.
								window.intervalId = setInterval(function() {
									var x = new Date();  // current time
									var y = Math.random();
									data.push([x, y]);
									g.updateOptions( { 'file': data } );
								}, 1000);
								*/
							
							}),500);
							<?php
						}
					?>
					</script>
				</div>
				<div class="span4 statbox"id="runner">
					<b>Total submissions [<?php if ($form['deactivator']) echo 'max: '.$form['deactivator'];
					else echo 'no maximum';?>]</b>
					<h1 max="<?php echo $form['submissions'];?>"></h1>
					<?php if (($form['active'])&&($form['alerter'])) echo '<div class="ribbed-green"><b><i class="icon-checkmark"></i> SMS alerts activated [step: '.$form['alerter'].']</b></div>';
					else echo '<div class="ribbed-red"><b><i class="icon-warning"></i> '.(($form['active'])?'SMS alerts are not set':'Survey is inactive').'</b></div>';?>

					<?php if ($form['active']){
						if ($form['public']) echo '<div class="ribbed-green"><b><i class="icon-earth"></i> Survey unrestricted on USSD/Internet</b></div>';
						else echo '<div class="ribbed-amber"><b><i class="icon-locked-2"></i> Survey is restricted</b></div>';
					}?>					
                </div>
			</div>
			<?php
			$chart_data=array();
			$chart_count=$count=0;
			function cut_out($string,$start,$end){
				$str_two = substr(substr($string, strpos($string, $start)), strlen($start));
				return trim(substr($str_two, 0, strpos($str_two, $end)));
			}
			$tiles=array();
			foreach (explode('</li><li',$form['fieldset']) as $question){
				$count++;				
				if (strpos($question,'<select name="')){
					$arr=explode('</option><option>',cut_out($question,'<option>','</option></select>'));
					$tiles[]=array(
						'size'=>(count($arr)>3)?8:4,
						'content'=>
						"<p><b>Q:</b> ".cut_out($question,'<label>','</label>')."<hr/></p>".
						'<canvas id="canvas'.$chart_count.'"></canvas>'
					);
					$chart_data[$chart_count]=array(
						'column'=>cut_out($question,'<select name="','">'),
						'options'=>$arr
					);
					$chart_count++;
				} else if (strpos($question,'<input type="number"')){
					//Integers
					$name=cut_out($question,'name="','">');
					
					$res=mysqli_query($db_conn,"SELECT AVG($name) AS mean FROM $tbname");
					$line=mysqli_fetch_assoc($res);
					$mean=$line['mean'];
					
					$res=mysqli_query($db_conn,"SELECT CEIL(COUNT(*)/2) AS mid FROM $tbname");
					$line=mysqli_fetch_assoc($res);
					$median=$line['mid'];
					$res=mysqli_query($db_conn,"SELECT MAX($name) AS median FROM (SELECT $name FROM $tbname ORDER BY $name limit $median) x");
					$line=mysqli_fetch_assoc($res);
					$median=$line['median'];
					
					$res=mysqli_query($db_conn,"SELECT $name AS mode, COUNT(*) AS x FROM $tbname GROUP BY $name ORDER BY x DESC LIMIT 1");
					$line=mysqli_fetch_assoc($res);
					$mode=$line['mode'];
					
					array_unshift(
						$tiles,array(
							'size'=>4,
							'content'=>
							"<p><b>Q:</b> ".cut_out($question,'<label>','</label>')."<hr/><table><tr><td>Mean:</td><td><b>$mean</b></td></tr><tr><td>Median:</td><td><b>$median</b></td></tr><tr><td>Mode:</td><td><b>$mode</b></td></tr></table></p>"
						)
					);
				}
			}
			$small=array();
			$big=array();
			foreach($tiles as $key=>$tile){
				if($tile['size']==4) $small[]=$key;
				else $big[]=$key;
			}
			for($i=0;$i<min(count($big),count($small));$i++) echo '<div class="row"><div class="span8 statbox">'.$tiles[$big[$i]]['content'].'</div><div class="span4 statbox">'.$tiles[$small[$i]]['content'].'</div></div>';
			if($i<count($big)) for(;$i<count($big);$i++) echo '<div class="row"><div class="span8 statbox">'.$tiles[$big[$i]]['content'].'</div></div>';
			else{
				echo '<div class="row">';
				$c=-1;
				for(;$i<count($small);$i++) echo ((((++$c)%3)||($c==0))?'':'</div><div class="row">').'<div class="span4 statbox">'.$tiles[$small[$i]]['content'].'</div>';
				echo '</div>';
			}			
			?>
		</div>
		<script>
			var barChartData=false;
			$(document).ready(function() {
				<?php if($charts){?>
				Chart.defaults.global.customTooltips = function(tooltip) {
					// Tooltip Element
					var tooltipEl = $('#chartjs-tooltip');
					// Hide if no tooltip
					if (!tooltip) {
						tooltipEl.css({
							opacity: 0
						});
						return;
					}
					// Set caret Position
					tooltipEl.removeClass('above below');
					tooltipEl.addClass(tooltip.yAlign);
					// Set Text
					var max=parseInt(document.getElementById('runner').children[1].getAttribute('max'));
					tooltipEl.html(tooltip.text+'<br/>representing '+(parseInt((tooltip.text).substr((tooltip.text).indexOf(':')+1))*100/max)+' %');

					// Find Y Location on page
					var top;
					if (tooltip.yAlign == 'above') {
						top = tooltip.y - tooltip.caretHeight - tooltip.caretPadding;
					} else {
						top = tooltip.y + tooltip.caretHeight + tooltip.caretPadding;
					}

					// Display, position, and set styles for font
					tooltipEl.css({
						opacity: 1,
						left: tooltip.chart.canvas.offsetLeft + tooltip.x + 'px',
						top: tooltip.chart.canvas.offsetTop + top + 'px',
						fontFamily: tooltip.fontFamily,
						fontSize: tooltip.fontSize,
						fontStyle: tooltip.fontStyle,
					});
				};				
				barChartData =[
					<?php
					$colors=array(
					'fillColor : "rgba(0,220,138,0.5)",strokeColor : "rgba(0,220,138,0.8)",highlightFill: "rgba(0,220,138,0.75)",highlightStroke: "rgba(0,220,138,1)",',
					'fillColor : "rgba(27,161,226,0.5)",strokeColor : "rgba(27,161,226,0.8)",highlightFill: "rgba(27,161,226,0.75)",highlightStroke: "rgba(27,161,226,1)",',
					'fillColor : "rgba(240,163,10,0.5)",strokeColor : "rgba(240,163,10,0.8)",highlightFill: "rgba(240,163,10,0.75)",highlightStroke: "rgba(240,163,10,1)",'
					);
					for($i=0;$i<count($chart_data);$i++){
						if($i) echo',';
						echo '{labels:["'.implode('","',$chart_data[$i]['options']).'"],';
						
						$res=mysqli_query($db_conn,"SELECT {$chart_data[$i]['column']} AS label, COUNT({$chart_data[$i]['column']}) AS num FROM $tbname GROUP BY {$chart_data[$i]['column']}");
						$data=array_fill (0,count($chart_data[$i]['options']),0);
						while ($line=mysqli_fetch_assoc($res)) $data[array_search($line['label'],$chart_data[$i]['options'])]=$line['num'];
						
						echo 'datasets:[{'.$colors[$i%3].'data:['.implode(',',$data).']}]}';
					}
					?>
				]
				<?php } ?>
				setTimeout(
				//function(){$('#HW_counters').fadeIn('slow',
				function(){
					//race up the numbers
					var counter=document.getElementById('runner').children[1], comma_separator_number_step = $.animateNumber.numberStepFactories.separator(','), max;
					max=parseInt(counter.getAttribute('max'));
					counter.innerHTML='';
					counter.style.visibility='visible';
					$('#runner h1').animateNumber(
						{
							number: max,
							numberStep: comma_separator_number_step
						},
						'normal'<?php if($charts){?>,
						function(){
							for(i=0;i<barChartData.length;i++){
								var ctx = document.getElementById("canvas"+i).getContext("2d");
								window.myBar = new Chart(ctx).Bar(barChartData[i], {
									responsive : true,
									scaleShowLabels: true,
									showScale: true
								});
							}
						}
						<?php }?>
					);
				}
				//})
				,500)
				$('#example').dataTable( {
					"processing": true,
					"serverSide": true,
					"ajax": {
						"url": "server_processing_viewform.php",
						"type": "POST"
					}
				});
			});
			</script>
		<?php } else { ?>
			<h3>No submissions to show</h3>
			<script>
			$(document).ready(function() {
				$('#example').dataTable( {
					"processing": true,
					"serverSide": true,
					"ajax": {
						"url": "server_processing_viewform.php",
						"type": "POST"
					}
				});
			})
			</script>
		<?php } ?>
		</div>
        <div class="frame" id="_page_2">
			<a target="_blank"href="export?<?php echo "id={$form['tablename']}&name=".urlencode($form['name']); ?>"><button class="large inverse"><i class="icon-download-2"></i> Export to Excel</button></a>
			<a onclick="make_public()"><button class="large inverse"><i class="icon-share"></i> Share results openly</button></a>
			<script>
			function make_public(){
				$.get("share_form.php?<?php echo "id={$form['tablename']}&name=".urlencode($form['name']); ?>",function(data){
					$.Dialog({
						overlay: true,
						shadow: true,
						flat: true,
						icon: '<i class="icon-share"></i>',
						title: 'Public sharing link',
						width:'300px',
						content: (id=parseInt(data))?'<a href="export?share='+id+'"target="_blank"><h3 style="padding:10px">https://askpeople.info/export?share='+id+'</h3></a>':'An error occured'
					});
				})
			} 
			</script>
			<p></p>
			<table id="example"class="display" cellspacing="0">
				<thead>
					<tr>
						<?php
						//get the headings
						foreach ($_SESSION['formFields'] as $field) echo "<th>$field</th>"
						?>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<?php
						//get the headings
						foreach ($_SESSION['formFields'] as $field) echo "<th>$field</th>"
						?>
					</tr>
				</tfoot>
			</table>
		</div>
        <div class="frame" id="_page_3">
		<?php
			$embedded='viewform';
			include ('hierarchy.php');
		?>
		</div>
        <div class="frame" id="_page_4">
			<legend>Survey settings</legend>
			<form method="POST">
			<div class="grid">
			<div class="row">
				<div class="span4 statbox">
					<label>Status</label>
					<div class="input-control select">
						<select name="status">
							<option value="1">Active</option>
							<option value="0"<?php if (!$form['active']) echo 'selected="selected"';?>>Inactive</option>
						</select>
					</div>
					<label>Auto-deactivation</label>
					<div class="input-control select">
						<select name="deactivate">
							<option value="1">On</option>
							<option value="0"<?php if (!$form['deactivator']) echo 'selected="selected"';?>>Off</option>
						</select>
					</div>
					<label>Responses before auto-deactivation (max. 50000)</label>
					<div class="input-control number">
						<input name="deactivator"type="text" <?php if ($form['deactivator']) echo 'value="'.$form['deactivator'].'"';?> placeholder="enter number"/>
						<button class="btn-clear"></button>
					</div>
				</div>
				<div class="span4 statbox"id="publicity">	
					<label>Target market</label>
					<div class="input-control select">
						<select name="target">
							<?php
							$res=mysqli_query($db_conn,'SELECT id, name FROM nodes_'.$_SESSION['user']['org']);
							while ($line=mysqli_fetch_assoc($res)) echo '<option '.(($line['id']==$form['node'])?'selected="selected"':'').'value="'.$line['id'].'">'.$line['name'].'</option>';
							?>
						</select>
					</div>
					<div class="input-control switch">
						<label>
							Share survey publicly&emsp;
							<input id="pb0"type="checkbox"name="public"<?php if ($form['public']) echo 'checked="checked"';?>/>
							<span id="pb"class="check"style="float:right"></span>
						</label>
					</div>
					<script>
						$('#pb').click(function(){
							if ($('#pb0')[0].checked) $('#pb2').hide('slow',function(){$('#pb1').show('slow')});
							else $('#pb1').hide('slow',function(){$('#pb2').show('slow')});
						})
					</script>
					<ul <?php if ($form['public']) echo 'style="display:none"';?>id="pb1">
					<li><b>Public</b> means that anybody with the address to your survey can answer it (via USSD or Internet).</li>
					<li>Hence, by sharing the address around, you <b>get more answers, free</b>!</li>
					<li>However, in public mode, we <b>cannot ensure profiling</b> of respondents, as everyone is allowed to answer.</li>
					</ul>
					<div <?php if (!$form['public']) echo 'style="display:none"';?>id="pb2">
					<div class="ribbed-teal fg-white"style="margin-bottom:10px">
					<small><i class="icon-hash"></i> USSD survey-code</small><br/>
					<b><?php echo $_SESSION['user']['org'].'.'.$in_id;?></b>
					</div>
					<div class="ribbed-teal fg-white">
					<small><i class="icon-cloud"></i> Internet link</small><br/>
					<a style="color:white;text-decoration:none"target="_blank"href="https://askpeople.info?go=<?php echo $_SESSION['user']['org'].'.'.$in_id;?>"><b>askpeople.info?go=<?php echo $_SESSION['user']['org'].'.'.$in_id;?></b></a>
					</div>
					</div>
				</div>
				<div class="span4 statbox">
					<label>Email for alerts</label>
					<div class="input-control number">
						<input name="mail"type="email" <?php if ($form['mail']) echo 'value="'.$form['mail'].'"';?>/>
						<button class="btn-clear"></button>
					</div>
					<label>SMS alert on responses</label>
					<div class="input-control select">
						<select name="alert">
							<option value="1">Active</option>
							<option value="0"<?php if (!$form['alerter']) echo 'selected="selected"';?>>Inactive</option>
						</select>
					</div>
					<label>Responses before SMS alert (max 1000)</label>
					<div class="input-control number">
						<input name="alerter"type="text" <?php if ($form['alerter']) echo 'value="'.$form['alerter'].'"';?> placeholder="enter number"/>
						<button class="btn-clear"></button>
					</div>
					<label>Phone number to use</label>
					<div class="input-control number">
						<input name="phone"type="text" <?php if ($form['phone']) echo 'value="'.$form['phone'].'"';?> placeholder="leave blank to use organization phone"/>
						<button class="btn-clear"></button>
					</div>
				</div>
			</div>
			</div>
			<input class="large inverse"type="submit" value="Update"/>
            </form>
		</div>
    </div>
</div>		
<style>
.input-control{margin-bottom:7px}
	tbody tr{cursor:pointer}
	.metro .tab-control .frames{border-top:1px dashed black}
</style>				
<?php
include ("foot.php");
?>