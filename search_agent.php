<?php
$title="Search Agent";
include ("head.php");
?>
<script src="js/jquery/jquery.dataTables.js"></script>
<link href="css/jquery.dataTables.css" rel="stylesheet">
		<h1>Browse Agents</h1>
		<br/>
						<table id="example"onclick="table_click(event)" class="display" cellspacing="0" width="100%">
							<thead>
								<tr>
									<th>ID</th>
									<th>Admin</th>
									<th>Real name</th>
									<th>Email</th>
									<th>Market unit</th>
									<th>Submits</th>
									<th>Last seen</th>
								</tr>
							</thead>
					 
							<tfoot>
								<tr>
									<th>ID</th>
									<th>Admin</th>
									<th>Real name</th>
									<th>Email</th>
									<th>Market-unit</th>
									<th>Submits</th>
									<th>Last seen</th>
								</tr>
							</tfoot>
						</table>
						<script>
						$(document).ready(function() {
							$('#example').dataTable( {
								"processing": true,
								"serverSide": true,
								"ajax": "server_processing_agent.php"
							} );
						} );
						function table_click (e){
							if (!e) e=window.event;
							var target = (e.target)?e.target:e.srcElement, line;
							if (target.tagName.toUpperCase()=='TD') line=target.parentNode;
							else if (target.tagName.toUpperCase()=='TR') line=target;
							else return;
							var id=parseInt(line.children[0].innerHTML);
							if (id>0) window.location='view_agent.php?id='+id;
						}
						</script>
						<div>
	<style>
	tbody tr{cursor:pointer}
	th:first-child {display:none}
	td:first-child {display:none}
	td {text-align:right}
	</style>				
<?php
include ("foot.php");
?>