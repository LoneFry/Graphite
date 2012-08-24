		</div>
		<div id="footer">
		</div>
<?php
if (MODE == 'dev') {
	if (isset(G::$G['startTime'])) {
		echo '<span class="subtle">load time: '.number_format(microtime(true)-G::$G['startTime'], 4).'s</span>';
	}
	echo '<a href="http://validator.w3.org/check?uri=referer"><img'
		.' src="'.CORE.'/images/valid-html401"'
		.' alt="Valid HTML 4.01" class="webButton"></a>'
		;
	if (isset($_POST)) {
		G::croak($_POST, false);
	}
	G::croak(G::$M->getQueries(), false);
}
?>
	</body>
</html>
