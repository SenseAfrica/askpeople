<?php
	session_start();
	if (!isset($_SESSION['captchas']))$_SESSION['captchas']=0;

	$url="http://www.EasyCaptchas.com/".session_id().".".(++$_SESSION['captchas']).
		".askpeople.info.captcha.gif?transparent=true&color=000000&lines=true&noise=true&width=135&height=60&words=false";
	set_time_limit(0);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $r = curl_exec($ch);
    curl_close($ch);
    header('Expires: 0'); // no cache
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
    header('Cache-Control: private', false);
    header('Content-Type: application/force-download');
    header('Content-Disposition: attachment; filename="' . basename($url) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . strlen($r)); // provide file size
    header('Connection: close');
    echo $r;
?>