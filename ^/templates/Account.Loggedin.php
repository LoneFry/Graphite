<?php get_header(); ?>
			<h2 class="tcenter">Checked In</h2> 
			<p class="tcenter">It looks like you checked in successfully!
				<br>How about we redirect you <a href="<?php html($sURI);?>"><?php html($sLbl);?></a>?
				<script type="text/javascript"><!--
					window.setTimeout("location.replace('<?php html($sURI);?>')",1);//--></script>
			</p>
			<div class="loginForm" id="bodyLogin">
				Hello, <?php html($loginname); ?>.  
				(<a href="<?php html($logoutURL); ?>">Logout</a>)
			</div>
<?php get_footer(); ?>
