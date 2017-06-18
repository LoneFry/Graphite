<?php echo $View->render('header'); ?>
            <section class="Account">
                <h2>Password Recovery</h2>
                <p><?php echo $msg; ?></p>
                <p>Enter your username, or the email associated with
                    your username, and a new password will be sent to you.</p>
                <div id="bodyLogin">
                    <form action="/Account/recover" method="post">
                        <div>
                            <label for="loginname">Username or Email</label>
                            <input id="loginname" type="text" name="loginname">
                        </div>
                        <div class="center">
                            <input type="hidden" name="action" value="Get Password">
                            <input type="submit" value="Get Password">
                        </div>
                    </form>
                </div>
            </section>
<?php echo $View->render('footer');
