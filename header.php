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
		<meta name="google-signin-scope" content="profile email">
		<meta name="google-signin-client_id" content="YOUR_CLIENT_ID.apps.googleusercontent.com">
		<script src="https://apis.google.com/js/platform.js" async defer></script>
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
