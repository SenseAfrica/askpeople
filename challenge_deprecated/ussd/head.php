<?php
if (!function_exists('getallheaders')) { 
	function getallheaders() {
		$headers = ''; 
		foreach ($_SERVER as $name => $value) if (substr($name, 0, 5) == 'HTTP_') $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
		return $headers; 
	}
}
// Send the headers
header('Content-type: text/html');
header('Pragma: public');
header('Cache-control: private');
header('Expires: -1');

$msisdn = "";
foreach (getallheaders() as $name => $value) {
    if (strtolower($name) == "user-msisdn"){
		$msisdn = $value;
	}
}
//test
//$msisdn='123456789';
$msisdn = preg_replace("/[^0-9]/", '', $msisdn);
echo '<?xml version="1.0" encoding="utf-8"?>';
if ($msisdn) include('../../db.php');
else {
	echo '<html><body>';
	echo '<head>';
	echo '  <meta name="nav" content="end"/>';
	echo '</head>';
	echo 'We were unable to retrieve your number.<br/>';
	echo 'Try again later.';
	echo '</body></html>';
	exit;
}
?>