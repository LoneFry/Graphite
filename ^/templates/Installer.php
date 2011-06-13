<?php get_header(); ?>
<style>
form#installer {background-color:#e2e2e2;padding:50px;}
form#installer div{margin:auto;width:500px;}
form#installer h3{margin:0 -10px 20px -10px;border-bottom:3px solid #2e2e2e;}
form#installer label{display:block;font:bold 10pt Georgia}
form#installer input[type=text],form#installer input[type=password]{margin-bottom:20px;width:400px;font:bold 16pt Tahoma;}
</style>
<form action="<?php echo CONT; ?>Installer/install" method="post" id="installer"
	onsubmit="if(sha1_vm_test()){this.password1.value=hex_sha1(this.password1.value);this.password2.value=hex_sha1(this.password2.value);}">
<div>
<?php if(isset($config)){ ?>
	<h3>Config</h3>
		<textarea rows="4" cols="80"><?php html($config);?></textarea>
<?php } ?>
	<h3>Website Details</h3>
		<label for="siteName">Site Name</label>
		<input id="siteName" type="text" name="siteName" value="<?php html($siteName);?>">
		<label for="loginname">Root Loginname</label>
		<input id="loginname" type="text" name="loginname" value="<?php html($loginname);?>">
		<label for="acctP1">Root Password</label>
		<input id="acctP1" type="password" name="password1">
		<label for="acctP2">Confirm Password</label>
		<input id="acctP2" type="password" name="password2">
		<label for="siteEmail">Site Email</label>
		<input id="siteEmail" type="text" name="siteEmail" value="<?php html($siteEmail);?>">
	
	<h3>Database Details</h3>
		<label for="Host">Database Host</label>
		<input id="Host" type="text" name="Host" value="<?php html($Host);?>">
		<label for="User">Read/Write User</label>
		<input id="User" type="text" name="User" value="<?php html($User);?>">
		<label for="Pass">Read/Write Password</label>
		<input id="Pass" type="password" name="Pass">
		<label for="Passb">Confirm Password</label>
		<input id="Passb" type="password" name="Passb">
		<label for="Name">Database Name</label>
		<input id="Name" type="text" name="Name" value="<?php html($Name);?>">
		<label for="Tabl">Table Prefix</label>
		<input id="Tabl" type="text" name="Tabl" value="<?php html($Tabl);?>">
	
	<h3>Read-Only Database Details</h3>
		<label for="User2">Read-Only User</label>
		<input id="User2" type="text" name="User2" value="<?php html($User2);?>">
		<label for="Pass2">Read-Only Password</label>
		<input id="Pass2" type="password" name="Pass2">
		<label for="Pass2b">Confirm Password</label>
		<input id="Pass2b" type="password" name="Pass2b">

	<h3>Installing</h3>
		<input type="submit" value="Install">
</div>
</form>
<?php get_footer();?>
