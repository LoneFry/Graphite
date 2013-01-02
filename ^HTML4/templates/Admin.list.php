<?php get_header(); ?>
<h2>Administrative Options</h2>
<ul>
<?php if(G::$S->roleTest('Admin/Login')){ ?>
    <li><a href="<?php echo CONT;?>Admin/Login">Manage Logins</a> (<a href="<?php echo CONT;?>Admin/LoginAdd">or Add</a>)</li>
    <li><a href="<?php echo CONT;?>Admin/LoginLog">View Login Log</a></li>
<?php } ?>
<?php if(G::$S->roleTest('Admin/Role')){ ?>
    <li><a href="<?php echo CONT;?>Admin/Role">Manage Roles</a> (<a href="<?php echo CONT;?>Admin/RoleAdd">or Add</a>)</li>
<?php } ?>
<?php if(G::$S->roleTest('Home/ContactLog')){ ?>
    <li><a href="<?php echo CONT;?>Home/ContactLog">View Contact Log</a></li>
<?php } ?>
</ul>
<?php get_footer(); ?>
