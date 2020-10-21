<?php
include_once('../db.php');
@session_start();
if (isset($_GET['share'])){
	$res=mysqli_query('SELECT * FROM share_form  WHERE id = '.(int)$_GET['share']);
	if($line=mysqli_fetch_assoc($res)){
		$tbl=$line['tbl'];
		$org=$line['org'];
		$name=$line['name'];
	}
} else if ((isset($_GET['id']))&&(isset($_SESSION['user']['org']))) {
	$tbl=(int)$_GET['id'];
	$org=$_SESSION['user']['org'];
	$name=$_GET['name'];
} else exit ("Error");

/***** EDIT BELOW LINES *****/
$DB_Server = $sql_details['host']; // MySQL Server
$DB_Username = $sql_details['user']; // MySQL Username
$DB_Password = $sql_details['pass']; // MySQL Password
$DB_DBName = $sql_details['db']; // MySQL Database Name
$DB_TBLName = "form_{$org}_$tbl"; // MySQL Table Name
$xls_filename = 'AskPeople_'.$name.'_'.date('Y-m-d').'.xls'; // Define Excel (.xls) file name
 
/***** DO NOT EDIT BELOW LINES *****/
// Create MySQL connection
/*
function cleaner ($data){
return ($data!='submit_node');
}
$sql = "SELECT nodes_{$_SESSION['user']['org']}.name AS 'sub-unit', $DB_TBLName.`".implode("`,$DB_TBLName.`",array_diff ($_SESSION['formFields'],array('sub-unit')))."` FROM $DB_TBLName LEFT JOIN nodes_{$_SESSION['user']['org']} ON $DB_TBLName.submit_node = nodes_{$_SESSION['user']['org']}.id";
*/
$sql="SELECT * FROM $DB_TBLName";
$Connect = @mysqli_connect($DB_Server, $DB_Username, $DB_Password) or die("Failed to connect to MySQL:<br />" . mysqli_error() . "<br />" . mysqli_errno());
// Select database
$Db = @mysqli_select_db($DB_DBName, $Connect) or die("Failed to select database:<br />" . mysqli_error(). "<br />" . mysqli_errno());
// Execute query
$result = @mysqli_query($sql,$Connect) or die("Failed to execute query:<br />" . mysqli_error(). "<br />" . mysqli_errno());
 
// Header info settings
header("Content-Type: application/xls");
header("Content-Disposition: attachment; filename=$xls_filename");
header("Pragma: no-cache");
header("Expires: 0");
 
/***** Start of Formatting for Excel *****/
// Define separator (defines columns in excel &amp; tabs in word)
$sep = "\t"; // tabbed character
 
// Start of printing column names as names of MySQL fields
for ($i = 0; $i<mysqli_num_fields($result); $i++) {
  echo mysqli_field_name($result, $i) . "\t";
}
print("\n");
// End of printing column names
 
// Start while loop to get data
while($row = mysqli_fetch_row($result))
{
  $schema_insert = "";
  for($j=0; $j<mysqli_num_fields($result); $j++)
  {
    if(!isset($row[$j])) {
      $schema_insert .= "NULL".$sep;
    }
    elseif ($row[$j] != "") {
      $schema_insert .= "$row[$j]".$sep;
    }
    else {
      $schema_insert .= "".$sep;
    }
  }
  $schema_insert = str_replace($sep."$", "", $schema_insert);
  $schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
  $schema_insert .= "\t";
  print(trim($schema_insert));
  print "\n";
}
?>