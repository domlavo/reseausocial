<?php

function renderHeader() {
	ob_start();
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title>Your Website Title</title>
	</head>
	<body>
	<?php
	return ob_get_clean();
}

?>
