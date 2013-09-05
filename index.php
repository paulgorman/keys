<?
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_PlusService.php';

session_start();

$client = new Google_Client();
$client->setApplicationName("LVE Keys Database");
$client->setClientId('403939207897.apps.googleusercontent.com');
$client->setClientSecret('L5PeGoLkOH_Qr9Y8XMFoilwk');
$client->setRedirectUri('http://keys.irev.net/');
$client->setDeveloperKey('AIzaSyBzq4BBN22hhn1nu5hLmVAMgbho9i2bgws');
$plus = new Google_PlusService($client);

if (isset($_REQUEST['logout'])) {
	unset($_SESSION['access_token']);
}

if (isset($_GET['code'])) {
	$client->authenticate($_GET['code']);
	$_SESSION['access_token'] = $client->getAccessToken();
	header('Location: http://keys.irev.net/');
}

if (isset($_SESSION['access_token'])) {
	$client->setAccessToken($_SESSION['access_token']);
}

if ($client->getAccessToken()) {
	$me = $plus->people->get('me');
	$url = filter_var($me['url'], FILTER_VALIDATE_URL);
	$img = filter_var($me['image']['url'], FILTER_VALIDATE_URL);
	$name = filter_var($me['displayName'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
	$personMarkup = "<a rel='me' href='$url'>$name</a><div><img src='$img'></div>";
	$plusid = filter_var($me['id'], FILTER_SANITIZE_NUMBER_INT);

	$log = fopen("userslog.txt",a);
	$date = date("g:i:sa T l, M jS");
	fwrite($log, "$date - $name - $plusid\n");
	fclose($log);

	// The access token may have been updated lazily.
	$_SESSION['access_token'] = $client->getAccessToken();
} else {
	$authUrl = $client->createAuthUrl();
}
?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<link rel='stylesheet' href='style.css' />
	</head>
	<body>
		<header><h1>LVE Keys Database</h1></header>
		<div class="box">
		<?php if(isset($personMarkup)): ?>
			<div class="me"><?= $personMarkup; ?></div>
		<?php endif ?>
	
		<?php
			if(isset($authUrl)) {
				echo "<a class='login' href='$authUrl'>Connect Me!</a>";
			} else {
				echo "<a class='logout' href='?logout'>Logout</a>";
			}
		?>
		</div>
	</body>
</html>
