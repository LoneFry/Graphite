<?php echo $View->render('header'); ?>
<nav>
    <ul class="breadcrumbs">
        <li><a href="/Admin">Admin</a></li>
        <li><a href="/Admin/Role">Roles</a></li>
    </ul>
</nav>
    <h2>Add a New Role</h2>

    <form action="/Admin/RoleAdd" method="post">
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
                <td colspan="2">
                    <input type="submit" value="Submit">
                </td>
            </tr>
        </table>
    </form>
<?php echo $View->render('footer');
