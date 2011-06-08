<?php get_header(); ?>
			<h2 class="tcenter">Account Settings</h2>
			<div id="bodyLogin">
				<form action="<?php echo CONT; ?>Account/edit" method="post"
					onsubmit="if(sha1_vm_test()){this.password1.value=hex_sha1(this.password1.value);this.password2.value=hex_sha1(this.password2.value);return true;}else{return true;}">
					<div>
						<label for="acctMail">Private eMail</label>
						<input id="acctMail" type="text" name="email" value="<?php echo $email; ?>">
					</div>
					<div>
						<label for="acctComm">Comment</label>
						<input id="acctComm" type="text" name="comment" value="<?php echo $comment; ?>">
					</div>
					<div>
						<label for="acctP1">Reset Password</label>
						<input id="acctP1" type="password" name="password1">
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
<?php get_footer();?>
