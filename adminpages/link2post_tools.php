<?php 
	//wrap this in some error handling to detect form submission with an empty or invalid URL
	//use filter_var function to check URL http://php.net/manual/en/function.filter-var.php
	l2p_processURL(); 
?>
<div class="wrap">
	<h2>Link2Post</h2>
	
	<form>
		<label for="l2purl">URL:</label>
		<input type="hidden" name="page" value="link2post_tools" />
		<input type="text" id="l2purl" name="l2purl" />
		<input type="submit" />
	</form>
</div>