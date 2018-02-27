<?php

require 'persistance.php';

function renderHeader() {
	ob_start();
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title>Your Website Title</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
		<script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
		<link rel="stylesheet" href="/css/main.css">
	</head>
	<body>
	<?php
	return ob_get_clean();
}

function recuperePersistance() {
	return Persistance::Instance();
}

?>
