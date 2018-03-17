<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'persistance.php';
require_once 'helper.php';

function renderHeader( $hasNav = false ) {
	$bodyClass = $hasNav ? ' class="hasNav"' : '';
	ob_start();
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title>Your Website Title</title>
		<link href="https://fonts.googleapis.com/css?family=Arimo:700|Lato:400,700i" rel="stylesheet">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.0.1/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="./bootstrap-markdown/css/bootstrap-markdown.min.css">
		<link rel="stylesheet" href="./css/bootstrap-modal.css">
		<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=UA-113554913-4"></script>
		<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
		ga('create', 'UA-113554913-4', 'auto');
		var url = document.location.pathname;
		var urlSplit = "/" + url.split("/").slice(-1)[0];
		ga('send', {
			hitType: 'pageview',
			page: urlSplit
		});
</script>
		<script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
		<script src="https://apis.google.com/js/platform.js?onload=onLoad" async defer></script>
		<script src="./bootstrap-markdown/js/markdown.js"></script>
		<script src="./bootstrap-markdown/js/to-markdown.js"></script>
		<script src="./bootstrap-markdown/js/bootstrap-markdown.js"></script>
		<script src="./bootstrap-markdown/locale/bootstrap-markdown.fr.js"></script>
		<script type="text/javascript" src="./js/script.js"></script>
		<meta name="google-signin-scope" content="profile email">
		<meta name="google-signin-client_id" content="924453145091-v7ivhe8s60llqd9e89f74cfti5o2es1i.apps.googleusercontent.com">
		<link rel="stylesheet" href="./css/main.css">
	</head>
	<body<?= $bodyClass ?>>
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

?>
