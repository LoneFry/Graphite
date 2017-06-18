<?php echo $View->render('header'); ?>
        <h3>Contact</h3>
        <form action="<?php echo $_SERVER["REQUEST_URI"];?>" method="post" onsubmit="return contactValid();" id="Home_Contact">
            <div class="c<?php echo $from; ?>">
                <div><label for="i<?php echo $from; ?>">Your Email Address:</label></div>
                <div><input type="text" id="i<?php echo $from; ?>" name="<?php echo $from; ?>" size="40" class="js-validate-email"></div>
            </div>
            <div class="c<?php echo $subject; ?>">
                <div><label for="i<?php echo $subject; ?>">Subject:</label></div>
                <div><input type="text" id="i<?php echo $subject; ?>" name="<?php echo $subject; ?>" size="40"></div>
            </div>
            <div class="c<?php echo $honey; ?>">
                <div><label for="i<?php echo $honey; ?>">Leave Blank:</label></div>
                <div><input type="text" id="i<?php echo $honey; ?>" name="<?php echo $honey; ?>" size="40"></div>
            </div>
            <div class="c<?php echo $message; ?>">
                <div><label for="i<?php echo $message; ?>">Message:</label></div>
                <div><textarea rows="8" cols="80" id="i<?php echo $message; ?>" name="<?php echo $message; ?>"></textarea></div>
            </div>
            <div class="c<?php echo $honey2; ?>">
                <div>
                    <input type="hidden" name="<?php echo $honey2; ?>">
                    <input type="hidden" name="apple" value="<?php echo $seed; ?>">
                </div>
                <div><input type="submit" value="Send Message"></div>
            </div>
        </form>
        <script type="text/javascript"><!--
            function contactValid() {
                if(false === validateEmail(document.getElementById('i<?php echo $from; ?>').value)) {
                    return confirm('Your email address appears to be invalid.\nIf you don\'t use a valid address, we cannot respond.\n\nPress OK to send anyway.\nPress Cancel to go back and correct it.');
                }
                return true;
            }
        // --></script>
<?php echo $View->render('footer');
