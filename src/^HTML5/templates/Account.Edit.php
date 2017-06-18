<?php echo $View->render('header'); ?>
            <section class="Account">
                <h2>Account Settings</h2>
                <div id="bodyLogin">
                    <form action="/Account/edit" method="post">
                        <input type="hidden" name="path" value="<?php echo $path; ?>">
                        <div>
                            <label for="acctMail">Private eMail</label>
                            <input id="acctMail" type="email" name="email" value="<?php echo $email; ?>">
                        </div>
                        <div>
                            <label for="acctComm">Comment</label>
                            <input id="acctComm" type="text" name="comment" value="<?php echo $comment; ?>">
                        </div>
                        <div>
                            <label for="acctP1">Reset Password</label>
                            <input id="acctP1" type="password" name="password1">
                            <p id="password_policy"><?php html(Security::validate_password('')); ?></p>
                        </div>
                        <div>
                            <label for="acctP2">Confirm Password</label>
                            <input id="acctP2" type="password" name="password2">
                        </div>
                        <div>
                            <input id="acctS" type="submit" value="Update">
                        </div>
                    </form>
                </div>
            </section>
<?php echo $View->render('footer');
