		</section>
		<footer>
		</footer>
<?php
if (MODE == 'dev') {
	echo '<details id="debug">';
	if (isset(G::$G['startTime'])) {
		echo '<summary>load time: '.number_format(microtime(true)-G::$G['startTime'], 4).'s</summary>';
	}
	echo '<a href="http://validator.w3.org/check?uri=referer">Validate</a>';
	if (isset($_POST)) {
		G::croak($_POST, false);
	}
	G::croak(G::$M->getQueries(), false);
	echo '</details>';
}
?>
	</body>
</html>
