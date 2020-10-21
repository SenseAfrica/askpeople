<?php
function build_path($org,$node){
	$org =(int) $org;
	$node =(int) $node;
	//returns path array if org is correct, or false otherwise
	if (isset($_SESSION['temp_path'])){
		foreach ($_SESSION['temp_path'] as $key => $val) if ($val['id']==$node) return
		$_SESSION['temp_path']=array_slice($_SESSION['temp_path'],0,$key+1);
	} else $_SESSION['temp_path']=array();
	
	//$root=$_SESSION['user']['root'];
	$root=1;

	$out=array();
	do {
		$res=mysqli_query("SELECT * FROM nodes_{$_SESSION['user']['org']} WHERE id = $node LIMIT 0,1");
		$line=mysqli_fetch_assoc($res);
		if(is_array($line)){
			$node=$line['parent'];
			unset($line['parent']);
			if ($line['id']==1) $line['name']='Country Market';
			array_unshift($out,$line);
			if ((!empty($_SESSION['temp_path']))&&($node==$_SESSION['temp_path'][count($_SESSION['temp_path'])-1])) {
				return $_SESSION['temp_path']=array_merge($_SESSION['temp_path'],$out);
			}
			if (is_null($node)) {
				if ($line['id']!=$root) return false;
				$line=false;		
			}
		}
	} while ($line);
	if (empty($out)) return false;
	return $_SESSION['temp_path']=$out;
}
?>