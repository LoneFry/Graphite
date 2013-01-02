<?php get_header(); ?>
			<h2 class="tcenter">Checked In</h2>
			<p class="tcenter">It looks like you checked in successfully!
				<br>How about we redirect you <a href="<?php html($_URI);?>"><?php html($_Lbl);?></a>?
				<script type="text/javascript"><!--
					window.setTimeout("location.replace('<?php html($_URI);?>')",1);//--></script>
			</p>
			<div class="loginForm" id="bodyLogin">
				Hello, <?php html($_loginname); ?>.
				(<a href="<?php html($_logoutURL); ?>">Logout</a>)
			</div>
<?php get_footer(); ?>
