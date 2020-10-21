<style>
.metro .navbar .element-menu>li:hover,.metro .navigation-bar .element-menu>li:hover{border-bottom: 5px solid white;background:black}
#main_btn:hover{
	background: white!important;
    color:black
}
.navigation-bar .place-right:hover{
	background: white!important;
    color:black!important
}
.navigation-bar .place-right:hover span{
    color:black!important
}
.dropdown-menu{
	background: black!important;
    color: white!important;
    border-color: black;
}
.dropdown-menu a{
	color: white!important;
}
.dropdown-menu a:hover{
	border:none!important;
	border-right:5px solid white!important;
	background:black!important;
	color:white!important
}
.dropdown-menu li:hover{
	border:none!important;
	background:black!important;
}
</style>
<div class="navigation-bar bg-black">
    <div class="navigation-bar-content container">
        <a id="main_btn"href="index.php" class="element"><span class="icon-home"></span> AskPeople</a>
        <span class="element-divider"></span>

        <a class="element1 pull-menu" href="#"></a>
        <ul class="element-menu">
		<?php if (isset($_SESSION['user'])){
		if ($_SESSION['user']['admin']){ ?>
			<li>
                <a class="dropdown-toggle"href="#">Agents</a>
				<ul class="dropdown-menu" data-role="dropdown">
                    <li><a href="new_agent.php">Create Agent</a></li>
                    <li><a href="search_agent.php">Search/Browse</a></li>
                </ul>
            </li>
			<li>
                <a class="dropdown-toggle"href="#">Surveys</a>
				<ul class="dropdown-menu" data-role="dropdown">
                    <li><a href="new_form.php">Create Survey</a></li>
                    <li><a href="search_form.php">Search/Browse</a></li>
                </ul>
            </li>
		<?php } else { ?>
			<li>
                <a href="search_form.php">Surveys</a>
           </li>
		<?php } ?>
			<li>
                <a class="dropdown-toggle"href="#">Recharge account</a>
				<ul class="dropdown-menu" data-role="dropdown">
                    <li><a href="#"id="bill_orange">Pay with Orange billing</a></li>
                    <li><a href="#"id="money_trans">Money Transfer</a></li>
                </ul>
            </li>
			<li>
                <a href="#"id="change_pwd">Change Password</a>
           </li>
			<script>
				$('#change_pwd').click(function(){
					txt='<form method="POST" action="index.php">'+
						'<fieldset>'+
							'<legend>Change Password</legend>'+
							'<label>New Password</label>'+
								'<div class="input-control password" data-role="input-control">'+
									'<input type="password" placeholder="new password" name="new_pwd"required="required">'+
									'<button class="btn-reveal"  type="button"></button>'+
								'</div>'+
								'<div class="input-control password" data-role="input-control">'+
									'<input type="password" placeholder="re-renter new password" name="new_pwd_2"required="required">'+
									'<button class="btn-reveal"  type="button"></button>'+
								'</div>'+
								'<label>Old Password</label>'+
								'<div class="input-control password" data-role="input-control">'+
									'<input type="password" placeholder="old password" name="old_pwd"required="required">'+
									'<button class="btn-reveal"  type="button"></button>'+
								'</div>'+
								'<br/><br/>'+
								'<input type="submit" class="inverse" value="Submit">'+
						'</fieldset>'+
					'</form>';
					$.Dialog({
						overlay: true,
						shadow: true,
						flat: true,
						padding:30,
						title: 'Account Management',
						content: txt
					});
				})
				$('#money_trans').click(function(){
					txt='<p>This app is currently under development.<br/> Use this form to add credit to your account.</p>'+
					'<form method="POST"><filedset><div class="input-control number"><input type="number"name="HWINPUTS-topup"placeholder="Amount to add (numbers only)"/>'+
					'<button class="btn-clear"></button></div><br/><br/><input type="submit"class="inverse"/></fieldset></form>';
					$.Dialog({
						overlay: true,
						shadow: true,
						flat: true,
						padding:30,
						title: 'Money Transfer',
						content: txt
					});
				})
				$('#bill_orange').click(function(){
					txt='<form method="POST" action="index.php">'+
						'<fieldset>'+
							'<legend>Pay with Orange credit</legend>'+
							'<label>Phone number</label>'+
								'<div class="input-control text">'+
									'<input type="text" placeholder="ex. +99..." name="bill_phone"required="required">'+
									'<button class="btn-clear"></button>'+
								'</div>'+
							'<label>Amount</label>'+
								'<div class="input-control text">'+
									'<input type="number" placeholder="numbers only, in XOF" name="bill_amount"required="required">'+
									'<button class="btn-clear"></button>'+
								'</div>'+
								'<br/><br/>'+
								'<input type="submit" class="inverse" value="Submit">'+
						'</fieldset>'+
					'</form>';
					$.Dialog({
						overlay: true,
						shadow: true,
						flat: true,
						padding:30,
						title: 'Account Management',
						content: txt
					});
				})
			</script>
		<?php } else {?>
		<!--<img style="position:absolute;top:-1px;right:10px;height:60px"src="images/orange_en.png"/>-->
		<?php }?>
            <li>
                <a href="#"id="about_btn">Contact</a>
            </li>
        </ul>
		<?php if (isset($_SESSION['user'])){ ?>
		<div class="">
			<a title="Logout"href="logout.php"><div class="element place-right">
				<span class="icon-exit fg-white"></span>
			</div></a>
			<span class="element-divider place-right"></span>
			<a href="index.php"style="color:white!important"><button class="element image-button image-left place-right">
				<?php
				echo $_SESSION['user']['name'];
				if ((isset($_POST['HWINPUTS-topup']))&&(is_numeric($_POST['HWINPUTS-topup']))&&($_POST['HWINPUTS-topup']>0)) mysqli_query($db_conn,'UPDATE end_users SET credits = credits+'.$_POST['HWINPUTS-topup'].' WHERE id='.$_SESSION['user']['org']);
				if($_SESSION['user']['admin']){
					$res=mysqli_query($db_conn,'SELECT credits FROM end_users WHERE id='.$_SESSION['user']['org']);
					$line=mysqli_fetch_assoc($res);
					echo ' : '.($_SESSION['user']['credits']=$line['credits']).' XOF';
				}
				echo'<img src="'.((file_exists("logos/{$_SESSION['user']['org']}.jpg"))?"logos/{$_SESSION['user']['org']}.jpg":'images/default_logo.png').'">';
				?>
			</button></a>
		</div>
		<?php } ?>
    </div>
</div>
<script>
$("#about_btn").click(function(){
	//$.post("flush.php",{}, function(data,status){ alert(/*"Data: " + data + */"Sending-out test messages...\nStatus: " + status); });
	$.Dialog({
        overlay: true,
        shadow: true,
        flat: true,
        icon: '<i class="icon-info"></i>',
        title: 'About us',
		width:330,
        content: '',
        onShow: function(_dialog){
            var content = _dialog.children('.content');
            content.html('<img src="images/about_head.png"style="position:absolute"/><h2 style="position:relative;padding: 5px;padding-left: 10px;line-height: 33px;">Built by<br/>Human Wireless</h2><div style="padding:50px;padding-top:5px;padding-bottom:5px;margin-bottom:15px;background:rgb(221,249,254)"><p><i class="icon-at"></i>&emsp;<b>contact@humanwireless.cm</b><br/></p><p><i class="icon-phone"></i>&emsp;<b>(+237) 242027748</b><br/>&emsp;&emsp;<small>Weekdays 8am-7pm GMT+1</small></p><p><i class="icon-mail"></i>&emsp;<b>P.O. Box 30627 Yaounde, CM</b></p></div><a href="http://humanwireless.cm"target="_blank"><img style="width:70%;left:15%;position:relative"src="images/hw.png"/></a>');
        }
    });
});
$("#createFlatWindow").on('click', function(){
    
});
</script>