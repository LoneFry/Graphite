<?php echo $View->render('header'); ?>
<h2>Contact Log</h2>
<table class="list">
    <thead>
        <tr>
            <th>id</th>
            <th>date</th>
            <th>from</th>
            <th>subject</th>
            <th>body</th>
            <th>IP</th>
        </tr>
    </thead>
    <tbody>
<?php
if (is_array($log) && count($log)) {
    foreach ($log as $k => $v) {
?>
        <tr>
            <td><?php html($v->id);?></td>
            <td><?php echo date("r", $v->date); ?></td>
            <td><?php html($v->from);?></td>
            <td><?php html($v->subject);?></td>
            <td><?php html($v->body);?></td>
            <td><?php html($v->IP);?></td>
        </tr>
<?php
    }
} else {
?>
        <tr><td colspan="6">No records found.</td></tr>
<?php
}
?>
    </tbody>
</table>
<?php echo $View->render('footer');
