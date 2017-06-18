<?php echo $View->render('header'); ?>
            <section class="Account">
                <h2>Checked In</h2>
                <p>It looks like you checked in successfully!
                    <br>How about we redirect you <a href="<?php echo str_replace('"', '&quot;', $_URI);?>"><?php html($_Lbl);?></a>?
                    <script type="text/javascript"><!--
                        window.setTimeout("location.replace('<?php echo str_replace("'", "\\'", $_URI);?>')", 1);// --></script>
                </p>
                <div id="bodyLogin">
                    Hello, <?php html($_loginname); ?>.
                    (<a href="<?php html($_logoutURL); ?>">Logout</a>)
                </div>
            </section>
<?php echo $View->render('footer');
