<?php echo $View->render('header'); ?>
<h2>Administrative Options</h2>
<nav>
    <ul>
<?php if (G::$S->roleTest('Admin/Login')) { ?>
        <li><a href="/Admin/Login">Manage Logins</a> (<a href="/Admin/LoginAdd">or Add</a>)</li>
        <li><a href="/Admin/LoginLog">View Login Log</a></li>
<?php } ?>
<?php if (G::$S->roleTest('Admin/Role')) { ?>
        <li><a href="/Admin/Role">Manage Roles</a> (<a href="/Admin/RoleAdd">or Add</a>)</li>
<?php } ?>
<?php if (G::$S->roleTest('Home/ContactLog')) { ?>
        <li><a href="/Home/ContactLog">View Contact Log</a></li>
<?php } ?>
    </ul>
</nav>
<?php echo $View->render('footer');
