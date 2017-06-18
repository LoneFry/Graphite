<?php echo $View->render('header'); ?>
<form action="/Installer/install" method="post" id="installer">
<div>
<?php if (isset($config)) { ?>
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
        <input id="siteEmail" type="email" name="siteEmail" value="<?php html($siteEmail);?>">

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
    <label><input type="checkbox" name="HTML5" value="1"<?php if ($HTML5) {
            echo ' checked="checked"';
        } ?>> Enable HTML5 front end?</label>
    <label><input type="checkbox" name="CLI" value="1"<?php if ($CLI) {
            echo ' checked="checked"';
        } ?>> Enable CLI?</label>
    <input type="submit" value="Install">
</div>
</form>
<?php echo $View->render('footer');
