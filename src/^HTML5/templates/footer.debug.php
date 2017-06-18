<?php if (MODE != 'dev') {
    return;
} ?>
<details id="debug">
    <summary>Debug Info: <?php $loadTime = number_format(microtime(true) - NOW, 4) ?></summary>
    <a href="http://validator.w3.org/check?uri=referer">Validate</a>
    <?php
    $loadTimeClass = 'success';
    if ($loadTime > 1.5) {
        $loadTimeClass = 'danger';
    } elseif ($loadTime > 0.5) {
        $loadTimeClass = 'warning';
    }

    $memoryUsage      = round(memory_get_usage() / 1024 / 1024, 2);
    $memoryUsageClass = 'success';
    if ($memoryUsage > 25) {
        $memoryUsageClass = 'danger';
    } elseif ($memoryUsage > 10) {
        $memoryUsageClass = 'warning';
    }

    $peakUsage      = round(memory_get_peak_usage() / 1024 / 1024, 2);
    $peakUsageClass = 'success';
    if ($peakUsage > 25) {
        $peakUsageClass = 'danger';
    } elseif ($peakUsage > 10) {
        $peakUsageClass = 'warning';
    }

    $queryData      = isset(G::$M) ? G::$M->getQueries() : [[0]];
    $queryTime      = round($queryData[0][0], 4);
    $queryTimeClass = 'success';
    if ($queryTime > 0.5) {
        $queryTimeClass = 'danger';
    } elseif ($queryTime > 0.2) {
        $queryTimeClass = 'warning';
    }

    $phpTime      = $loadTime - $queryTime;
    $phpTimeClass = 'success';
    if ($phpTime > 1.5) {
        $phpTimeClass = 'danger';
    } elseif ($phpTime > 0.5) {
        $phpTimeClass = 'warning';
    }

    ?>
    <details open="open">
        <summary class="h4">Performance</summary>
        <dl class="dl-horizontal">
            <dt><span class="text-default">Request URL:</span></dt>
            <dd><span class="text-default"><strong><?php html($_SERVER['REQUEST_URI']); ?></strong></span></dd>
            <dt><span class="text-default">Request Time:</span></dt>
            <dd><span class="text-default"><strong><?php echo date('r', NOW); ?></strong></span></dd>
            <dt><span class="text-<?php echo $phpTimeClass; ?>">PHP Run Time:</span></dt>
            <dd><span class="text-<?php echo $phpTimeClass; ?>"><strong><?php echo $phpTime; ?></strong> s</span></dd>
            <dt><span class="text-<?php echo $queryTimeClass; ?>">Query Run Time:</span></dt>
            <dd><span class="text-<?php echo $queryTimeClass; ?>"><strong><?php echo $queryTime; ?></strong> s</span></dd>
            <dt><span class="text-<?php echo $loadTimeClass; ?>">Total Load Time:</span></dt>
            <dd><span class="text-<?php echo $loadTimeClass; ?>"><strong><?php echo $loadTime; ?></strong> s</span></dd>
            <dt><span class="text-<?php echo $memoryUsageClass; ?>">Memory Usage:</span></dt>
            <dd><span class="text-<?php echo $memoryUsageClass; ?>"><strong><?php echo $memoryUsage; ?></strong> MB</span></dd>
            <dt><span class="text-<?php echo $peakUsageClass; ?>">Peak Usage:</span></dt>
            <dd><span class="text-<?php echo $peakUsageClass; ?>"><strong><?php echo $peakUsage; ?></strong> MB</span></dd>
        </dl>
    </details>
    <?php
    if (isset($GLOBALS['_Profiler'])) {
        $GLOBALS['_Profiler']->mark('View');
        $profileData = $GLOBALS['_Profiler']->get();
        ?>
        <details>
            <summary class="h4">Profile Times</summary>
            <table class="table table-striped table-bordered table-hover table-condensed js-sort-table" id="Profiler_Times">
                <thead>
                <tr>
                    <th>Label</th>
                    <th>Duration</th>
                    <th>Memory</th>
                    <th>Time</th>
                    <th>Location</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($profileData as $label => $marks) {
                    $mark = end($marks);
                    if (0 == $marks[0]['timer']) {
                        $marks[0]['timer'] = $mark['time'] - $marks[1]['time'];
                    }
                    ?>
                    <tr>
                        <td><?php html($label); ?></td>
                        <td<?php if ($marks[0]['timer'] > 1) {
                            echo ' class="text-danger"';
                        } ?>><?php if ($marks[0]['timer'] > 0) { ?><strong><?php printf('%.5f',
                                $marks[0]['timer']) ?></strong> s<?php } else {
                                echo '<br>';
                            } ?></td>
                        <td><strong><?php echo round($mark['memory'] / 1048576, 2); ?></strong> MB</td>
                        <td><?php printf('%.8f', $mark['time']); ?> s</td>
                        <td><?php html($mark['location']); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <script type="text/javascript"><!--
                $(document).ready(function() {
                    sortTable(document.getElementById('Profiler_Times'), 1, -1);
                });
                //--></script>
        </details>
        <details>
            <summary class="h4">Profile Marks</summary>
            <table class="table table-striped table-bordered table-hover table-condensed js-sort-table" id="Profiler_Marks">
                <thead>
                <tr>
                    <th>Label</th>
                    <th>Duration</th>
                    <th>Memory</th>
                    <th>Time</th>
                    <th>Location</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($profileData as $label => $marks) {
                    for ($i = 1; $i < count($marks); $i++) {
                        $mark = $marks[$i];
                        ?>
                        <tr>
                            <td><?php html($label); ?></td>
                            <td<?php if ($mark['duration'] > 1) {
                                echo ' class="text-danger"';
                            } ?>><?php if ($mark['duration'] > 0) { ?><strong><?php printf('+%.5f',
                                    $mark['duration']); ?></strong> s<?php } else {
                                    echo '<br>';
                                } ?></td>
                            <td><strong><?php echo round($mark['memory'] / 1048576, 2); ?></strong> MB</td>
                            <td><?php printf('%.8f', $mark['time']); ?> s</td>
                            <td><?php html($mark['location']); ?></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
                </tbody>
            </table>
            <script type="text/javascript"><!--
                $(document).ready(function() {
                    sortTable(document.getElementById('Profiler_Marks'), 3, 1);
                });
                //--></script>
        </details>
        <?php
    }
    foreach (array_unique(['_GET', '_'.$_SERVER['REQUEST_METHOD'], '_FILES']) as $item) {
        if (isset($GLOBALS[$item])) { ?>
            <details>
                <summary class="h4"><?php echo $item; ?> Array</summary>
                <?php G::croak($GLOBALS[$item], false); ?>
            </details>
        <?php }
    } ?>
    <details open="open">
        <summary class="h4">Query Log</summary>
        <?php G::croak($queryData, false); ?>
    </details>
</details>
