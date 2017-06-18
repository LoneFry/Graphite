<?php echo $View->render('header'); ?>
<?php if ($_login_id) { ?>
        <section class="Account Account_left">
            <h2>You Are Already Checked In</h2>
            <p>You are already recognized as <b><?php html($_loginname); ?></b>.</p>
            <p>If you want to switch users, you can use the form to the right.</p>
        </section>
<?php } ?>
        <section class="Account">
            <h2>Check In Below</h2>
            <p><?php echo isset($msg) ? $msg : ''; ?></p>
            <div id="bodyLogin">
                <form action="<?php echo $_loginURL; ?>" method="post">
                    <div>
                        <label for="loginU2">Username</label>
                        <input id="loginU2" type="text" name="l" value="<?php html(isset($l) ? $l : ''); ?>">
                    </div>
                    <div>
                        <label for="loginP2">Password</label>
                        <input id="loginP2" type="password" name="p">
                    </div>
                    <div>
                        <input id="loginS2" type="submit" value="Check-in">
                        <input type="hidden" name="_URI" value="<?php html($_URI); ?>">
                        <input type="hidden" name="_Lbl" value="<?php html($_Lbl); ?>">
                    </div>
                </form>
            </div>
            <p><a href="/Account/recover">Forgot Password?</a></p>
        </section>
<?php echo $View->render('footer');
