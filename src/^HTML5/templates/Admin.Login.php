<?php echo $View->render('header'); ?>
<nav>
    <ul class="breadcrumbs">
        <li><a href="/Admin">Admin</a></li>
    </ul>
</nav>
<?php include 'Admin.LoginSearch.php'; ?>

<ul>
<?php
if (isset($list) && is_array($list)) {
    foreach ($list as $k => $v) {
?>
        <li><a href="/Admin/LoginEdit/<?php echo $k;?>"><?php html($v->loginname);?></a></li>
<?php
    }
}
?>
</ul>
<?php echo $View->render('footer');
