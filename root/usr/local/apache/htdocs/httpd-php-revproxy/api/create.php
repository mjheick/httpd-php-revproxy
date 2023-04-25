<?php

require "../lib/autoload.php";
$config = new RevProxy_Config(CONFIG_ROOT);
if (($message = RevProxy_Login::ensureAuthenticated($config)) !== true) {
    echo $message;
    exit;
}

$classes = array();

try {
	$api = new RevProxy_API($config);
	$redirects = $api->getRedirects();

	$uuid = uuid_create();

	if (!isset($_GET['source'])) {
		throw new Exception('No Source specified');
	}
	if (!preg_match('/\\A(?:[[:alnum:]][[:alnum:]\\-]*\\.?)*[[:alnum:]]\\z/i', $_GET['source'])) {
		throw new Exception('Source hostname is not valid!');
	}
	if ($_GET['source'] === php_uname('n') || $_GET['source'] === $_SERVER['HTTP_HOST']) {
		throw new Exception("Cannot set source hostname to this machine's hostname or you'll prevent access to this interface!");
	}

	if (!isset($_GET['destination'])) {
		throw new Exception('No Destination specified');
	}

	if (ip2long($_GET['destination']) === FALSE && !preg_match('/\\A(?:[[:alnum:]][[:alnum:]\\-]*\\.?)*[[:alnum:]]\\z/i', $_GET['destination'])) {
		throw new Exception('Destination must be an IP address or a valid hostname!');
	}
	if (ip2long($_GET['destination']) === FALSE && gethostbyname($_GET['destination']) === $_GET['destination']) {
		throw new Exception('Destination hostname cannot be resolved!');
	}
	if ($_GET['destination'] === php_uname('n') || $_GET['destination'] === $_SERVER['HOSTNAME']) {
		throw new Exception("Cannot set destination hostname to this machine's hostname!");
	}

	$redirects[$uuid] = array(
		'enabled' => TRUE,
		'source' => $_GET['source'],
		'destination' => $_GET['destination'],
		'created' => time(),
		'modified' => time(),
	);

	$api->setRedirects($redirects);
	
}
catch(Exception $e) {
    echo json_encode(array(
        'error' => "Exception: {$e->getMessage() }"
    ));
    exit;
}
echo json_encode($redirects[$uuid]);
?>
