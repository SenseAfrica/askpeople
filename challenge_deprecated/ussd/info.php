<?php
include('head.php');
echo '<html>';
echo '<body>';

$res=mysql_query("SELECT credits FROM visitors WHERE phone = $msisdn");
$line=mysql_fetch_assoc($res);
echo 'Each time you complete a survey, you receive 5 credits for each question .<br/>';
echo 'You can redeem your credits for airtime or other gifts!<br/>';
echo 'You have  '.$line['credits'].' credits in your account.<br/>';
echo '<a href="redeem.php">redeem credits</a><br/>';

echo '</body>';
echo '</html>';
?>