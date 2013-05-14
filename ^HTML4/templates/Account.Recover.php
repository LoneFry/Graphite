<?php get_header(); ?>
			<h2 class="tcenter">Password Recovery</h2>
			<p class="tcenter"><?php echo $msg; ?></p>
			<p class="tcenter">Enter your username, or the email associated with
				your username, and a new password will be sent to you.</p>
			<div class="loginForm" id="bodyLogin">
				<form action="<?php echo CONT; ?>Account/recover" method="post">
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
<?php get_footer();
