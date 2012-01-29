<?php get_header(); ?>
<?php if($_login_id){ ?>
			<div class="fleft" style="width:50%;">
				<h2 class="tcenter">You Are Already Checked In</h2>
				<p class="tcenter">&nbsp;</p>
				<p class="tcenter">You are already recognized as <b><?php html($_loginname); ?></b>.</p>
				<p class="tcenter">If you want to switch users, you can use the form to the right.</p>
			</div>
		<div class="fright" style="width:50%;">
<?php } ?>
			<h2 class="tcenter">Check In Below</h2>
			<p class="tcenter"><?php echo isset($msg)?$msg:''; ?></p>
			<div class="loginForm" id="bodyLogin">
				<form action="<?php echo $_loginURL; ?>" method="post"
					onsubmit="if(sha1_vm_test()){this.h.value=hex_sha1(this.s.value+hex_sha1(this.p.value));this.p.value='';return true;}else{return true;}">
					<div>
						<label for="loginU2">Username</label>
						<input id="loginU2" type="text" name="l" value="<?php html(isset($l)?$l:''); ?>">
					</div>
					<div>
						<label for="loginP2">Password</label>
						<input id="loginP2" type="password" name="p">
						<input id="loginH2" type="hidden" name="h">
					</div>
					<div>
						<input id="loginS2" type="submit" value="Check-in">
						<input type="hidden" name="s" value="<?php echo session_id(); ?>">
						<input type="hidden" name="_URI" value="<?php html($_URI); ?>">
						<input type="hidden" name="_Lbl" value="<?php html($_Lbl); ?>">
					</div>
				</form>
			</div>
			<p class="tcenter"><a href="<?php echo CONT; ?>Account/recover">Forgot Password?</a></p>
<?php if($_login_id){ ?>
		</div>
<?php } ?>
<?php get_footer(); ?>
