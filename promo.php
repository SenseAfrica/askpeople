<?php
include('mail.php');
include('db.php');
$res=mysqli_query($db_conn,'SELECT email FROM users WHERE 1');
while ($line=mysqli_fetch_assoc($res)){
HW_send($line['email'],'Making the most of AskPeople',"
<html><body>
<h2>Thanks for chosing us!</h2>
<p>With AskPeople, you get comprehensive information from real people in real-time.<br/>
Here are a few tips to help you make the most of our system:</p>
<ul>
<li><p>You can ask people to answer your surveys directly online, <b>FREE</b>. Yes, at no cost at all!<br/>All you need to do is to open the survey's page (by clicking on it in the dashboard, or through search), go to the Admin section and select \"Make public\" and save your settings. You will see a link which you can share with your contacts, allowing them to fill-in your survey, directly in their browser, at absolutely no cost!</p></li>
<li><p>We target people for you, via USSD. But you can test it out yourself!<br/>When you chose \"Make public\" as indicated above, you will also be given a survey code, which users can input in the USSD system to access your survey directly! Use this to test your survey internally or to ask just a specific set of people to answer your questions!</p></li>
</ul>
<br/><i>We are comitted to serving you! contact us aytime for help: <a href='mailto:contact@humanwireless.cm'>contact@humanwireless.cm</a></i>
</body></html>
");
HW_send($line['email'],'Domain switched to askpeople.info - All issues solved',"
<html><body>
<h2>Greetings!</h2>
<p>We are happy to announce to you that our domain <b>askpoeple.info</b> is now installed and fully secured!<br/>
Don't worry, all requests to our old address will be redirected automatically, so you do not have to change anything.<br/>
You may have recently experienced a series of dead links due to our switch, but all have been fully resolved by now. Thanks for your understanding.</p>
<br/><i>We are comitted to serving you! contact us aytime for help: <a href='mailto:contact@humanwireless.cm'>contact@humanwireless.cm</a></i>
</body></html>
");
}
?>