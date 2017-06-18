<?php echo $View->render('header'); ?>
<nav>
    <ul class="breadcrumbs">
        <li><a href="/Admin">Admin</a></li>
        <li><a href="/Admin/Login">Logins</a></li>
    </ul>
</nav>
<?php include 'Admin.LoginSearch.php'; ?>

    <h2>Add a New Account</h2>

    <form action="/Admin/LoginAdd" method="post">
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
                <td><input type="text" name="email1" id="email1" value="<?php html($L->email);?>" class="js-validate-email-stricter">
                        <label class="msg" for="email1" id="email1Msg"></label>
                </td>
            </tr>
            <tr>
                <th>Confirm E-Mail Address</th>
                <td><input type="text" name="email2" id="email2" value="<?php html($L->email);?>" class="js-validate-email-stricter">
                        <label class="msg" for="email2" id="email2Msg"></label>
                </td>
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
                <td colspan="2">
                    <input type="submit" value="Submit">
                </td>
            </tr>
        </table>
    </form>
<?php echo $View->render('footer');
