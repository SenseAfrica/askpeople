<?php
@session_start();
include_once ('db.php');
if(!isset($title)) $title="";
if(($title!="Home")&&($title!="Web survey")&&(!isset($_SESSION['user']))) {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	header("Location: http://$host$uri");
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="product" content="Ask People">
    <meta name="description" content="Find answers directly from the field">
    <meta name="author" content="Harry W. Kamdem, Yaounde, Cameroon">

    <link href="css/commune.css" rel="stylesheet">
    <script src="js/commune.js"></script>
    <script src="js/metro.min.bak.js"></script>
    <script src="js/load-metro.js"></script>
	
	<style>
		.statbox{border:1px solid gray!important;background:white!important;padding:10px!important}
	</style>
    <title>AskPeople | <?php echo $title; ?></title>
</head>
<body class="metro"style="background-image:url('images/symphony.png')">
    <header style="z-index:1000"><?php include ("header.php"); ?></header>
	<div class="container">
	
<?php if (($title=="Home")||($title=="Web survey")) echo '<a href="http://humanwireless.cm"target="_blank"><img style="position:fixed;bottom:0;padding:10px;margin:0;right:0;width:25%;min-width:3cm;max-width:5cm;"src="images/hw.png"/></a>';
if (false){ ?>
		<p style='position:fixed;bottom:0;padding:10px;margin:0;right:0;width:100%;text-align:right;vertical-align:bottom;'>
			<!--<b>powered by</b> --><a href="http://humanwireless.cm"target="_blank"><img style='width:25%;min-width:3cm;max-width:5cm;'src='images/hw.png'/></a>
			<b style='position:absolute;bottom:5px;left:5px;font-size:12px;text-align:left'><?php if (isset($_SESSION['user'])) echo "<a href='logout.php'>Logout</a> &middot; "; ?><a href="terms.php#privacy"style="color:black!important">Privacy</a><!-- &middot; French--></b>
		</p>
<?php } ?>