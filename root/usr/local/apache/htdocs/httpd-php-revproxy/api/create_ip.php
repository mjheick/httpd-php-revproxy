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
	$ips = $api->getIps();

	$uuid = uuid_create();

	if (!isset($_GET['addresses'])) {
		throw new Exception('No address specified');
	}
	$addresses = explode(',', $_GET['addresses']);
	foreach	($addresses as $address)
	{
		if (!preg_match('{\A(?:(?:25[0-5]|2[0-4][[:digit:]]|[01]?[[:digit:]][[:digit:]]?)\.){3}(?:25[0-5]|2[0-4][[:digit:]]|[01]?[[:digit:]][[:digit:]]?)(?:/(?:[[:digit:]]|[0-3][[:digit:]]))?\z}i', $address)) {
			throw new Exception('Address must be a valid IPv4 CIDR address!');
		}
	}

	if (!isset($_GET['name'])) {
		throw new Exception('No name specified');
	}
	if (!ctype_print($_GET['name'])) {
		throw new Exception('Invalid name');
	}

	$ips[$uuid] = array(
		'enabled' => TRUE,
		'name' => $_GET['name'],
		'addresses' => $addresses,
		'created' => time(),
		'modified' => time(),
	);

	$api->setIps($ips);
	
}
catch(Exception $e) {
    echo json_encode(array(
        'error' => "Exception: {$e->getMessage() }"
    ));
    exit;
}
echo json_encode($ips[$uuid]);
?>
