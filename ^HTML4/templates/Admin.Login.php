<?php get_header(); ?>

    <ul class="breadcrumbs">
        <li><a href="<?php echo CONT;?>Admin">Admin</a></li>
    </ul>

<?php include 'Admin.LoginSearch.php'; ?>

<ul>
<?php if(isset($list) && is_array($list))foreach($list as $k => $v){ ?>
    <li><a href="<?php echo CONT;?>Admin/LoginEdit/<?php echo $k;?>"><?php html($v->loginname);?></a></li>
<?php } ?>
</ul>
<?php get_footer(); ?>
