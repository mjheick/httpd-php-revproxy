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
	$creds = $api->getCredentials();

	$uuid = uuid_create();

	if (!isset($_GET['username'])) {
		throw new Exception('No Username specified');
	}
	if (!preg_match('/\\A(?:[[:alnum:]][[:alnum:]\\-]*\\.?)*[[:alnum:]]\\z/i', $_GET['username'])) {
		throw new Exception('Username is not valid!');
	}
	if (!isset($_GET['password'])) {
		throw new Exception('No Password specified');
	}

	if (!preg_match('/\\A\\w{8,}\\z/i', $_GET['password'])) {
		throw new Exception('Password must be at least 8 characters!');
	}

	$creds[$uuid] = array(
		'enabled' => TRUE,
		'username' => $_GET['username'],
		'password' => $_GET['password'],
		'created' => time(),
		'modified' => time(),
	);

	$api->setCredentials($creds);
	
}
catch(Exception $e) {
    echo json_encode(array(
        'error' => "Exception: {$e->getMessage() }"
    ));
    exit;
}
echo json_encode($creds[$uuid]);
?>
