		</div>
		<div id="footer">
			<p>
				<a href="<?php echo CONT; ?>Account/edit">Account Settings</a>
			</p>
			<a href="http://validator.w3.org/check?uri=referer"><img
				src="<?php echo CORE; ?>/images/valid-html401" 
				alt="Valid HTML 4.01" class="webButton"></a>
			<br>
			<?php if(isset(G::$G['startTime']))echo '<span class="subtle">load time: '.number_format(microtime(true)-G::$G['startTime'],4).'s</span>'; ?>
		</div>
		
		<script type="text/javascript"><!--// add icon to external links
			var s=location.protocol+'//'+location.hostname;
			var a=document.getElementsByTagName('a');
			for(f in a){
				if(a[f].href && 
					a[f].href.substr(0,4)=='http' && 
					a[f].href.substr(0,s.length)!=s &&
					(' '+a[f].rel+' ').indexOf(' external ')==-1 &&
					a[f].getElementsByTagName('img').length==0
				){
					a[f].rel+=' external';
				}
			}
		//--></script>
<?php
if(MODE=='dev'){
	G::croak($_POST,false);
	G::croak(G::$M->getQueries(),false);
}
?>
	</body>
</html>
