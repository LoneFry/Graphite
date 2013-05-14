<?php get_header(); ?>

    <ul class="breadcrumbs">
        <li><a href="<?php echo CONT;?>Admin">Admin</a></li>
        <li><a href="<?php echo CONT;?>Admin/Role">Roles</a></li>
    </ul>

    <h2>Add a New Role</h2>

    <form action="<?php echo CONT;?>Admin/RoleAdd" method="post">
        <table class="formTable">
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
                        <option value="1"<?php if (1==$R->disabled) { echo ' selected'; }?>>Yes, Role is Disabled</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <input type="submit" value="Submit">
                </td>
            </tr>
        </table>
    </form>
<?php get_footer();
