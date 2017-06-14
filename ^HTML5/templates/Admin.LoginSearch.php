    <h2>Search By Letter</h2>
    <p>
<?php foreach ($letters as $k => $v) {
    echo '<a class="'.($v>0?'bold':'subtle').'" href="'.'/Admin/Login/'.$k.'" title="'.$v.'">'.$k.'</a> ';
} ?>
    <a class="subtle" href="/Admin/LoginAdd">Add New</a>
    </p>
