<?php get_header(); ?>

    <ul class="breadcrumbs">
        <li><a href="<?php echo CONT;?>Admin">Admin</a></li>
        <li><a href="<?php echo CONT;?>Admin/Role">Roles</a></li>
    </ul>

    <form action="<?php echo CONT.'Admin/RoleEdit/'.$R->role_id;?>" method="post" class="clear">

    <div class="fleft">
        <h2>Edit Role</h2>

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
                        <option value="1"<?php if(1==$R->disabled){echo ' selected';}?>>Yes, Role is Disabled</option>
                    </select>
                </td>
            </tr>
          
            <tr>
                <th>Creator Login</th>
                <td><a href="<?php echo CONT;?>Admin/LoginEdit/<?php echo $R->creator_id;?>"><?php html($creator);?></a></td>
            </tr>
            <tr>
                <th>Created</th>
                <td><?php echo $R->dateCreated?date('r',$R->dateCreated):'Never';?></td>
            </tr>
            <tr>
                <th>Modified</th>
                <td><?php echo $R->dateModified?date('r',$R->dateModified):'Never';?></td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <input type="submit" value="Submit">
                </td>
            </tr>
        </table>
    </div>

    <div style="border-top:1px solid transparent;"> 
        <h2>Grant Role</h2>

        <table class="listTable">
            <thead>
                <tr>
                    <th>Grant<input type="hidden" name="grant[]" value="0"></th>
                    <th>To</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
<?php if(is_array($Logins))foreach($Logins as $k => $v){ ?>
                <tr>
                    <td><input type="checkbox" name="grant[<?php echo $k;?>]" id="g<?php echo $k;?>" value="1"<?php if(isset($members[$k]))echo ' checked';?>></td>
                    <td><label for="g<?php echo $k;?>"><?php echo $v->loginname;?></label></td>
                    <td class="subtle"><?php
                        if(isset($members[$k]) && isset($Logins[$members[$k]])){
                            echo $Logins[$members[$k]]->loginname;
                        }?></td>
                </tr>
<?php } ?>
            </tbody>
        </table>
    </div>
</form>
<?php get_footer(); ?>
