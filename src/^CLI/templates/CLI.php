<?php echo $View->render('header'); ?>
            <div id="cli" onclick="document.getElementById('prompt').focus();">
                <pre id="buffer"><?php echo $CLI_buffer; ?></pre>
                <form action="/Gsh" method="post" onsubmit="return CLI_runCommand(this);"><input id="prompt" type="text" name="prompt"><input id="submit" type="submit" value="run"></form>
            </div>

            <script type="text/javascript">
                window.addEventListener('load', CLI_resize);
                window.addEventListener('resize', CLI_resize);
                document.getElementById('prompt').focus();
                document.getElementById('prompt').scrollIntoView();
                var refreshers = <?php echo json_encode($refreshers); ?>;
            </script>
<?php echo $View->render('footer');
