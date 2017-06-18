<?php echo $View->render('header'); ?>
            <section class="Account">
                <h2>Checked Out</h2>
                <p>It looks like you checked out successfully!
                    <br>How about we redirect you <a href="<?php html($_URI);?>"><?php html($_Lbl);?></a>?
                    <script type="text/javascript"><!--
                        window.setTimeout("location.replace('<?php html($_URI);?>')",1);// --></script>
                </p>
            </section>
<?php echo $View->render('footer');
