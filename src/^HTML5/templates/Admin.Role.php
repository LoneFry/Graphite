<?php echo $View->render('header'); ?>
<nav>
    <ul class="breadcrumbs">
        <li><a href="/Admin">Admin</a></li>
    </ul>
</nav>
    <h2>Roles</h2>
    <a href="/Admin/RoleAdd">Add Role</a>
    <table class="list">
        <thead>
            <tr>
                <th>Id</th>
                <th>Role</th>
                <th>Creator</th>
                <th>Description</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
<?php
if (isset($list) && is_array($list)) {
    foreach ($list as $k => $v) {
?>
        <tr class="<?php echo $v->disabled?'subtle':'';?>">
            <td><?php echo $v->role_id;?></td>
            <td><a href="/Admin/RoleEdit/<?php echo $k;?>"><?php html($v->label);?></a></td>
            <td><?php echo $v->creator_id;?></td>
            <td><?php echo $v->description;?></td>
            <td><?php echo $v->disabled?'Disabled':'Enabled';?></td>
        </tr>
<?php
    }
}
?>
        </tbody>
    </table>
<?php echo $View->render('footer');
