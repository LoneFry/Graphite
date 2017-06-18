<?php echo $View->render('header'); ?>
<nav>
    <ul class="breadcrumbs">
        <li><a href="/Admin">Admin</a></li>
        <li><a href="/Admin/Login">Logins</a></li>
    </ul>
</nav>
<?php include 'Admin.LoginSearch.php'; ?>

<form action="<?php echo '/Admin/LoginEdit/'.$L->login_id;?>" method="post" id="Admin_LoginEdit">
    <div>
        <h2>Edit Account Settings</h2>

        <table class="form">
            <tr>
                <th>LoginName</th>
                <td><input type="text" name="loginname" value="<?php html($L->loginname);?>"></td>
            </tr>
            <tr>
                <th>Real Name</th>
                <td><input type="text" name="realname" value="<?php html($L->realname);?>"></td>
            </tr>
            <tr>
                <th>Password</th>
                <td><input type="password" name="pass1"></td>
            </tr>
            <tr>
                <th>Confirm Password</th>
                <td><input type="password" name="pass2"></td>
            </tr>
            <tr>
                <th>E-Mail Address</th>
                <td><input type="email" name="email1" value="<?php html($L->email);?>"></td>
            </tr>
            <tr>
                <th>Confirm E-Mail Address</th>
                <td><input type="email" name="email2" value="<?php html($L->email);?>"></td>
            </tr>
            <tr>
                <th>Session Strength</th>
                <td><select name="sessionStrength">
                        <option value="2">Secure session to Browser and IP</option>
                        <option value="1"<?php if (1==$L->sessionStrength) {echo ' selected';}?>>Secure session to Browser only</option>
                        <option value="0"<?php if (0==$L->sessionStrength) {echo ' selected';}?>>Allow multiple concurrent sessions</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Must Change Password?</th>
                <td><select name="flagChangePass">
                        <option value="0">No</option>
                        <option value="1"<?php if (1==$L->flagChangePass) {echo ' selected';}?>>Yes</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Disabled?</th>
                <td><select name="disabled">
                        <option value="0">No, User is Enabled</option>
                        <option value="1"<?php if (1==$L->disabled) {echo ' selected';}?>>Yes, User is Disabled</option>
                    </select>
                </td>
            </tr>

            <tr>
                <th>Referring Login</th>
                <td><a href="/Admin/LoginEdit/<?php echo $L->referrer_id;?>"><?php html($referrer);?></a></td>
            </tr>
            <tr>
                <th>Created</th>
                <td><?php echo $L->dateCreated?'<time datetime="'.date('c', $L->dateCreated).'">'.date('r', $L->dateCreated).'</time>':'Never';?></td>
            </tr>
            <tr>
                <th>Modified</th>
                <td><?php echo $L->dateModified?date('r', $L->dateModified):'Never';?></td>
            </tr>
            <tr>
                <th>Checked In</th>
                <td><?php echo $L->dateLogin?date('r', $L->dateLogin):'Never';?></td>
            </tr>
            <tr>
                <th>Checked Out</th>
                <td><?php echo $L->dateLogout?date('r', $L->dateLogout):'Never';?></td>
            </tr>
            <tr>
                <th>Active</th>
                <td><?php echo $L->dateActive?date('r', $L->dateActive):'Never';?></td>
            </tr>
            <tr>
                <th>Last IP</th>
                <td><?php echo $L->lastIP;?></td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="submit" value="Submit">
                    <input type="hidden" name="login_id" value="<?php echo $L->login_id;?>">
                </td>
            </tr>
        </table>
    </div>

    <div>
        <h2>Grant Roles</h2>

        <table class="list">
            <thead>
                <tr>
                    <th>Grant<input type="hidden" name="grant[]" value="0"></th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
<?php
if (is_array($Roles)) {
    foreach ($Roles as $k => $v) {
?>
        <tr>
            <td><input type="checkbox" name="grant[<?php echo $k;?>]" id="g<?php echo $k;?>" value="1"<?php if($L->roleTest($v->label))echo ' checked';?>></td>
            <td><label for="g<?php echo $k;?>"><?php echo $v->label;?></label></td>
        </tr>
<?php
    }
}
?>
            </tbody>
        </table>
    </div>

    <div>
        <h2>Login Log</h2>
        <table class="list">
            <thead>
                <tr>
                    <th>pkey</th>
                    <th>date</th>
                    <th>IP</th>
                    <th>user agent</th>
                </tr>
            </thead>
            <tbody>
<?php
if (is_array($log) && count($log)) {
    foreach ($log as $k => $v) {
?>
                <tr>
                    <td><?php html($v->pkey);?></td>
                    <td><?php echo date("r", $v->iDate); ?></td>
                    <td><?php html($v->ip);?></td>
                    <td><?php html($v->ua);?></td>
                </tr>
<?php
    }
} else {
?>
        <tr><td colspan="4">No records found.</td></tr>
<?php
}
?>
            </tbody>
        </table>
    </div>

</form>
<?php echo $View->render('footer');
