<?php get_header(); ?>
			<h2 class="tcenter">Checked Out</h2> 
			<p class="tcenter">It looks like you checked out successfully!
				<br>How about we redirect you <a href="<?php html($sURI);?>"><?php html($sLbl);?></a>?
				<script type="text/javascript"><!--
					window.setTimeout("location.replace('<?php html($sURI);?>')",1);//--></script>
			</p>
<?php get_footer(); ?>
