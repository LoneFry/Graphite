<?php get_header(); ?>

    <ul class="breadcrumbs">
        <li><a href="<?php echo CONT;?>Admin">Admin</a></li>
        <li><a href="<?php echo CONT;?>Admin/Login">Logins</a></li>
    </ul>

<h2>Login Log</h2>
<table class="listTable">
    <thead>
        <tr>
            <th>pkey</th>
            <th>date</th>
            <th>login_id</th>
            <th>IP</th>
            <th>user agent</th>
        </tr>
    </thead>
    <tbody>
<?php
if (is_array($log) && count($log)) {
    foreach ($log as $k => $v) {
?>
        <tr>
            <td><?php html($v->pkey);?></td>
            <td><?php echo date("r", $v->iDate);?></td>
            <td><?php echo '<a href="'.CONT.'Admin/LoginEdit/'.$v->login_id.'">'.$v->login_id.'</a>';?></td>
            <td><?php html($v->ip);?></td>
            <td><?php html($v->ua);?></td>
        </tr>
<?php
    }
} else {
?>
        <tr><td colspan="4">No records found.</td></tr>
<?php
}
?>
    </tbody>
</table>
<?php get_footer();
