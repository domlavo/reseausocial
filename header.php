<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'persistance.php';

function renderHeader() {
	ob_start();
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title>Your Website Title</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
		<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
		<script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
		<script src="https://apis.google.com/js/platform.js?onload=onLoad" async defer></script>
		<meta name="google-signin-scope" content="profile email">
		<meta name="google-signin-client_id" content="924453145091-v7ivhe8s60llqd9e89f74cfti5o2es1i.apps.googleusercontent.com">
		<link rel="stylesheet" href="./css/main.css">
	</head>
	<body>
	<script>
    function onLoad() {
      gapi.load('auth2', function() {
        gapi.auth2.init();
      });
    }
	</script>
	<?php
	return ob_get_clean();
}

function recupererPersistance() {
	return Persistance::Instance();
}

function verifierConnection() {
	$loginID = $_SESSION['loginID'];
	return recupererPersistance()->recupererUtilisateur($loginID);
}

?>
