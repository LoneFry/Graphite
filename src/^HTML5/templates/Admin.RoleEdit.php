<?php echo $View->render('header'); ?>
<nav>
    <ul class="breadcrumbs">
        <li><a href="/Admin">Admin</a></li>
        <li><a href="/Admin/Role">Roles</a></li>
    </ul>
</nav>
    <form action="<?php echo '/Admin/RoleEdit/'.$R->role_id;?>" method="post">

    <div>
        <h2>Edit Role</h2>

        <table class="form">
            <tr>
                <th>Label</th>
                <td><input type="text" name="label" value="<?php html($R->label);?>"></td>
            </tr>
            <tr>
                <th>Description</th>
                <td><textarea rows="4" cols="40" name="description"><?php html($R->description);?></textarea></td>
            </tr>
            <tr>
                <th>Disabled?</th>
                <td><select name="disabled">
                        <option value="0">No, Role is Enabled</option>
                        <option value="1"<?php if (1==$R->disabled) {echo ' selected';}?>>Yes, Role is Disabled</option>
                    </select>
                </td>
            </tr>

            <tr>
                <th>Creator Login</th>
                <td><a href="/Admin/LoginEdit/<?php echo $R->creator_id;?>"><?php html($creator);?></a></td>
            </tr>
            <tr>
                <th>Created</th>
                <td><?php echo $R->dateCreated ? date('r', $R->dateCreated) : 'Never'; ?></td>
            </tr>
            <tr>
                <th>Modified</th>
                <td><?php echo $R->dateModified ? date('r', $R->dateModified) : 'Never'; ?></td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="submit" value="Submit">
                    <input type="hidden" name="role_id" value="<?php echo $R->role_id;?>">
                </td>
            </tr>
        </table>
    </div>

    <div style="border-top:1px solid transparent;">
        <h2>Grant Role</h2>

        <table class="list">
            <thead>
                <tr>
                    <th>Grant<input type="hidden" name="grant[]" value="0"></th>
                    <th>To</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
<?php
if (is_array($Logins)) {
    foreach ($Logins as $k => $v) {
?>
        <tr>
            <td><input type="checkbox" name="grant[<?php echo $k;?>]" id="g<?php echo $k;?>" value="1"<?php if(isset($members[$k]))echo ' checked';?>></td>
            <td><label for="g<?php echo $k;?>"><?php echo $v->loginname;?></label></td>
            <td class="subtle"><?php
                if (isset($members[$k]) && isset($Logins[$members[$k]])) {
                    echo $Logins[$members[$k]]->loginname;
                }?></td>
        </tr>
<?php
    }
}
?>
            </tbody>
        </table>
    </div>
</form>
<?php echo $View->render('footer');
