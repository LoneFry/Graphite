    <h2>Search By Letter</h2>
    <p>
<?php foreach ($letters as $k => $v) {
    echo '<a class="'.($v>0?'bold':'subtle').'" href="'.CONT.'Admin/Login/'.$k.'" title="'.$v.'">'.$k.'</a> ';
} ?>
    <a class="subtle" href="<?php echo CONT;?>Admin/LoginAdd">Add New</a>
    </p>
