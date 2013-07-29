<?php get_header(); ?>
        <script type="text/javascript"><!--
            function contactValid() {
                if(false === validateEmail(document.getElementById('i<?php echo $from; ?>').value)) {
                    return confirm('Your email address appears to be invalid.\nIf you don\'t use a valid address, we cannot respond.\n\nPress OK to send anyway.\nPress Cancel to go back and correct it.');
                }
                return true;
            }
        // --></script>
            <div class="form">
                <h3>Contact</h3>
                <form action="" method="post" onsubmit="return contactValid();">
                    <table id="contactForm"><!-- yes, I am about to use a table for layout in this form. -->
                        <tr class="c<?php echo $from; ?>">
                            <th valign="top"><label for="i<?php echo $from; ?>">Your Email Address:</label></th>
                            <td valign="top"><input type="text" id="i<?php echo $from; ?>" name="<?php echo $from; ?>" size="40" class="js-validate-email"></td>
                        </tr>
                        <tr class="c<?php echo $subject; ?>">
                            <th valign="top"><label for="i<?php echo $subject; ?>">Subject:</label></th>
                            <td valign="top"><input type="text" id="i<?php echo $subject; ?>" name="<?php echo $subject; ?>" size="40"></td>
                        </tr>
                        <tr class="c<?php echo $honey; ?>">
                            <th valign="top"><label for="i<?php echo $honey; ?>">Leave Blank:</label></th>
                            <td valign="top"><input type="text" id="i<?php echo $honey; ?>" name="<?php echo $honey; ?>" size="40"></td>
                        </tr>
                        <tr class="c<?php echo $message; ?>">
                            <th valign="top"><label for="i<?php echo $message; ?>">Message:</label></th>
                            <td valign="top"><textarea rows="8" cols="80" id="i<?php echo $message; ?>" name="<?php echo $message; ?>"></textarea></td>
                        </tr>
                        <tr class="c<?php echo $honey2; ?>">
                            <th valign="top">
                                <input type="hidden" name="<?php echo $honey2; ?>">
                                <input type="hidden" name="apple" value="<?php echo $seed; ?>">
                            </th>
                            <td valign="top"><input type="submit" value="Send Message"></td>
                        </tr>
                    </table>
                </form>
            </div>
<?php get_footer();
